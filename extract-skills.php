<?php

$path = 'C:/Users/Administrator/.cursor/projects/c-Users-Administrator-Documents-MEET-AJ-PORTFOLIO/agent-transcripts/aae45c9e-702d-4241-8ac3-1ec09d15c020/aae45c9e-702d-4241-8ac3-1ec09d15c020.jsonl';
$h = fopen($path, 'r');
$n = 0;
$hits = 0;
while (($line = fgets($h)) !== false) {
    $n++;
    if (! str_contains($line, 'aria-valuenow')) {
        continue;
    }
    $hits++;
    echo "line $n len=".strlen($line).PHP_EOL;
    if ($hits >= 8) {
        break;
    }
}
fclose($h);
echo "done hits=$hits".PHP_EOL;
