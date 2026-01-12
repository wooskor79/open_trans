<?php

function build_srt($rows) {
    $out = [];
    $i = 1;
    foreach ($rows as $r) {
        if (!$r['end']) continue;
        $out[] = $i++;
        $out[] = "{$r['start']} --> {$r['end']}";
        $out[] = $r['text'];
        $out[] = "";
    }
    return implode("\n", $out);
}

function output_result($rows, $outfmt, $ext) {
    if ($outfmt === 'srt') {
        header("Content-Disposition: attachment; filename=translated.srt");
        echo build_srt($rows);
        return;
    }

    if ($ext === 'ass') {
        header("Content-Disposition: attachment; filename=translated.ass");
        echo implode("\n", array_column($rows, 'raw'));
        return;
    }

    if ($ext === 'smi') {
        header("Content-Disposition: attachment; filename=translated.smi");
        foreach ($rows as $r) {
            echo "<SYNC Start=0><P>{$r['text']}\n";
        }
        return;
    }

    header("Content-Disposition: attachment; filename=translated.srt");
    echo build_srt($rows);
}
