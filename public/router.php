<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && file_exists($file)) {
    return false; // 既存ファイルはそのまま配信
}
require __DIR__ . '/index.php'; // それ以外はFuelに渡す
