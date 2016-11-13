#!/usr/bin/env php
<?php

call_user_func(function () {
    echo file_get_contents(__DIR__ . '/humbuglog.txt');

    $log = json_decode(file_get_contents(__DIR__ . '/humbuglog.json'), true);
    if (!array_key_exists('escapes', $log['summary'])) {
        throw new \UnexpectedValueException('Could not find "escapes" in summary');
    }

    if ($log['summary']['escapes']) {
        throw new \UnexpectedValueException('Humbug reported escaped mutants');
    }
});
