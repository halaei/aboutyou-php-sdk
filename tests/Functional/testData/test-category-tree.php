<?php
date_default_timezone_set('GMT');

function formatMem($mem) {
    return number_format($mem / 1024, 2, ',', '.') . ' kb';

}

function formatTime($time) {
    if ($time > 120) {
        return date('H:i:s', $time);
    } else if ($time >= 10) {
        return number_format($time, 3, ',', '.') . ' s';
    }

    return number_format($time * 1000, 2, ',', '.') . ' ms';
}

$jsonString = file_get_contents('app-8-category-tree.json');

$mem_limit1 = memory_get_peak_usage();
$start = $end = time() + microtime(true);

echo 'mem: ', formatMem($mem_limit1), ' - time: ', formatTime($end - $start), PHP_EOL;

$jsonArray = json_decode($jsonString, true);
$end = time() + microtime(true);
$mem_limit1 = memory_get_peak_usage();
echo 'mem: ', formatMem($mem_limit1), ' - time: ', formatTime($end - $start), PHP_EOL;
unset($jsonArray);
$start = time() + microtime(true);

$jsonObject = json_decode($jsonString);
$end = time() + microtime(true);
$mem_limit1 = memory_get_peak_usage();
unset($jsonObject);
echo 'mem: ', formatMem($mem_limit1), ' - time: ', formatTime($end - $start), PHP_EOL;
$start = time() + microtime(true);
