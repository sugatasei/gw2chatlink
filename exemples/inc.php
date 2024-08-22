<?php

// Error reporting
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('html_errors', 0);

// UTF-8 support
ini_set('output_buffering', 0);
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');

// locales
setlocale(LC_ALL, 'fr_FR.utf8');
setlocale(LC_NUMERIC, 'C');
ini_set('date.timezone', 'Europe/Paris');

include __DIR__ . '/../vendor/autoload.php';
