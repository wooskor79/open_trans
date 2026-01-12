<?php

function parse_file($text, $ext) {
    if ($ext === 'srt') return parse_srt($text);
    if ($ext === 'smi') return parse_smi($text);
    if ($ext === 'ass') return parse_ass($text);
    return [];
}

/* ---------- SRT ---------- */
function parse_srt($t) {
    $blocks = preg_split("/\n\s*\n/", trim($t));
    $rows = [];

    foreach ($blocks as $b) {
        $l = explode("\n", trim($b));
        if (count($l) < 3) continue;

        [$start, $end] = array_map('trim', explode('-->', $l[1]));
        $rows[] = [
            'start' => $start,
            'end'   => $end,
            'text'  => implode("\n", array_slice($l, 2))
        ];
    }
    return $rows;
}

/* ---------- SMI ---------- */
function parse_smi($t) {
    preg_match_all('/<sync\s+start\s*=\s*(\d+)[^>]*>(.*?)(?=<sync|\z)/is', $t, $m);
    $rows = [];

    for ($i = 0; $i < count($m[1]); $i++) {
        $rows[] = [
            'start' => ms_to_time($m[1][$i]),
            'end'   => '',
            'text'  => trim(strip_tags($m[2][$i]))
        ];
    }
    return $rows;
}

/* ---------- ASS (완전 보존) ---------- */
function parse_ass($t) {
    $lines = explode("\n", $t);
    $rows = [];
    foreach ($lines as $l) {
        if (str_starts_with(trim($l), 'Dialogue:')) {
            $p = explode(',', $l, 10);
            $rows[] = [
                'start' => $p[1],
                'end'   => $p[2],
                'text'  => $p[9],
                'raw'   => $l
            ];
        }
    }
    return $rows;
}

/* ---------- util ---------- */
function ms_to_time($ms) {
    $s = floor($ms / 1000);
    return gmdate("H:i:s", $s) . ',' . str_pad($ms % 1000, 3, '0', STR_PAD_LEFT);
}
