<?php

$fp = fopen(__DIR__.'/cases_hourly.txt', 'r');

$rounds = [];


while ($line = fgets($fp)) {
    $data = json_decode($line, true);
    if (!$data) {
        throw new Exception(json_last_error_msg());
    }

    $r = ($data['escaping']['value'] * 60 * 9) + ($data['round']['value'] * 60) + $data['sexagenaryHour']['value'];
    if (isset($rounds[$r])) {
        continue;
    }
    $rounds[$r] = $line;
}

ksort($rounds);

for($i = 0; $i < 1080; $i++) {
    echo trim($rounds[$i]).PHP_EOL;
}
