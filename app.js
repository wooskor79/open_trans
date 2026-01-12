let engine = 'gemini';

function log(msg){
  document.getElementById('log').textContent += msg + "\n";
}

function switchTab(e, ev){
  engine = e;
  document.querySelectorAll('.tabs button').forEach(b=>b.classList.remove('active'));
  ev.target.classList.add('active');
  startBtn.disabled = true;
  quota.textContent = '';
  log(`엔진 변경: ${e}`);
}

async function checkKey(){
  const key = apiKey.value;
  log("API Key 확인 중...");
  const res = await fetch(`api/check_${engine}.php`, {
    method:'POST',
    body:new URLSearchParams({api_key:key})
  });
  const data = await res.json();
  if(!data.ok){
    log("❌ API Key 오류");
    startBtn.disabled = true;
  } else {
    log("✅ API Key 정상");
    startBtn.disabled = false;
    if(data.quota) quota.textContent = data.quota;
  }
}

async function startTranslate(){
  const fd = new FormData();
  fd.append('api_key', apiKey.value);
  fd.append('file', subFile.files[0]);
  fd.append('outfmt', document.querySelector('input[name="outfmt"]:checked').value);

  log("번역 시작...");
  const res = await fetch(`api/translate_${engine}.php`, {
    method:'POST',
    body:fd
  });
  const blob = await res.blob();
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = "translated." + (document.querySelector('input[name="outfmt"]:checked').value === 'srt'
    ? 'srt'
    : subFile.files[0].name.split('.').pop());
  a.click();
  log("완료");
}
