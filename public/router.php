<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// 静的ファイルはサーバーに任せる
if ($path !== '/' && file_exists($file) && is_file($file)) {
    return false;
}

// それ以外は Fuel の index.php へ
require __DIR__ . '/index.php';