#!/usr/bin/env php
<?php

echo file_get_contents(__DIR__ . '/humbuglog.txt');

$log = json_decode(file_get_contents(__DIR__ . '/humbuglog.json'), true);
if (!array_key_exists('escapes', $log['summary'])) {
    echo "Could not find 'escapes' in summary\n";
    exit(1);
}

if ($log['summary']['escapes']) {
    echo "Humbug reported escaped mutants\n";
    exit(1);
}

exit(0);
