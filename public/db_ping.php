<?php
$host = '127.0.0.1';
$port = 13306;
$db   = 'shiftboard';
$user = 'app';
$pass = 'app_pass';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
  $mysqli = new mysqli($host, $user, $pass, $db, $port);
  $res = $mysqli->query('SELECT DATABASE() AS db, (SELECT COUNT(*) FROM shifts) AS shifts_cnt');
  $row = $res->fetch_assoc();
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true, 'db' => $row['db'], 'shifts_cnt' => (int)$row['shifts_cnt']]);
} catch (Throwable $e) {
  header('Content-Type: application/json; charset=utf-8', true, 500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}