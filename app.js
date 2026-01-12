let engine = 'gemini';
let timerInterval = null;
let startTime = 0;

function log(msg) {
    console.log(msg); // 이제 내부 로그는 콘솔에만 남깁니다.
}

function switchTab(e, ev) {
    engine = e;
    document.querySelectorAll('.tabs button').forEach(b => b.classList.remove('active'));
    ev.target.classList.add('active');
    document.getElementById('startBtn').disabled = true;
    document.getElementById('quota').textContent = '';
    document.getElementById('dashboard').style.display = 'none';
}

async function checkKey() {
    const key = document.getElementById('apiKey').value;
    const res = await fetch(`api/check_${engine}.php`, {
        method: 'POST',
        body: new URLSearchParams({ api_key: key })
    });
    const data = await res.json();
    if (!data.ok) {
        alert("❌ API Key 오류");
        document.getElementById('startBtn').disabled = true;
    } else {
        alert("✅ API Key 정상");
        document.getElementById('startBtn').disabled = false;
        if (data.quota) document.getElementById('quota').textContent = data.quota;
    }
}

// SRT 간단 파서 (실시간 프리뷰용)
function parseSRTRows(text) {
    return text.trim().split(/\n\s*\n/).map(block => {
        const lines = block.split('\n');
        return lines.length >= 3 ? lines.slice(2).join(' ') : "";
    }).filter(t => t !== "");
}

async function startTranslate() {
    const fileInput = document.getElementById('subFile');
    const files = fileInput.files;
    if (files.length === 0) return;

    const outfmt = document.querySelector('input[name="outfmt"]:checked').value;
    const apiKey = document.getElementById('apiKey').value;

    // UI 설정
    document.getElementById('dashboard').style.display = 'block';
    document.getElementById('final-msg').textContent = '';
    startTime = performance.now();
    runTimer();

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        document.getElementById('stat-progress-file').textContent = `${i + 1}/${files.length} 파일`;
        
        // 프리뷰용 텍스트 추출
        const rawText = await file.text();
        const rows = parseSRTRows(rawText);
        
        // 실제 번역 요청 (백엔드 통신)
        // 백엔드에서 실시간 줄 단위 정보를 주지 않으므로, 
        // 여기서는 파일 단위 시작 시 현재 줄/전체 줄을 초기화하여 보여줍니다.
        document.getElementById('stat-progress-line').textContent = `분석 중... / ${rows.length} 줄`;
        document.getElementById('curr-orig').textContent = rows[0] || "파일 처리 중...";

        const fd = new FormData();
        fd.append('api_key', apiKey);
        fd.append('file', file);
        fd.append('outfmt', outfmt);

        try {
            const res = await fetch(`api/translate_${engine}.php`, { method: 'POST', body: fd });
            const blob = await res.blob();
            
            // 파일 다운로드
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `translated_${file.name}`;
            a.click();

            // 진행 상태 업데이트 (가상 업데이트 - 실제 백엔드 연동 전까지 완료 표시)
            document.getElementById('stat-progress-line').textContent = `${rows.length}/${rows.length} 줄`;
            document.getElementById('curr-trans').textContent = "번역 완료 및 다운로드됨";
            
            // 다음에 번역할 5줄 미리보기 (마지막 파일이면 비움)
            const nextPreview = document.getElementById('next-preview');
            nextPreview.innerHTML = rows.slice(1, 6).map(line => `<li>- ${line.substring(0, 50)}...</li>`).join('');

        } catch (e) {
            console.error("번역 실패:", e);
        }
    }

    clearInterval(timerInterval);
    const totalTime = ((performance.now() - startTime) / 1000).toFixed(2);
    document.getElementById('final-msg').textContent = `🎉 모든 번역 완료! (총 소요시간: ${totalTime}초)`;
}

function runTimer() {
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        const diff = Math.floor((performance.now() - startTime) / 1000);
        const m = String(Math.floor(diff / 60)).padStart(2, '0');
        const s = String(diff % 60).padStart(2, '0');
        document.getElementById('stat-time').textContent = `${m}:${s}`;
    }, 1000);
}