#!/usr/bin/php
<?php

$targetPath = __DIR__ . '/../VERSION';

$output = shell_exec('git describe --tags');

if (strpos($output,'fatal') >= 0) {
    file_put_contents($targetPath, '0.0.1');
} else {
    file_put_contents($targetPath, $output);
}