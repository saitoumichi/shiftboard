<?php
class Controller_Api_Debug extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    public function get_db_test()
    {
        $out = [
            'ok' => true,
            'env' => \Fuel::$env,
            'config_loaded' => null,
            'config_snapshot' => null,
            'fuel_db' => null,
            'fuel_db_error' => null,
            'mysqli' => null,
            'mysqli_error' => null,
            'orm' => null,
            'orm_error' => null,
        ];

        try {
            // 1) db設定を読み込む（存在すれば配列が返る）
            //   development 環境なら fuel/app/config/development/db.php が自動マージされます
            $cfg = \Config::load('db', true); // ← ファイル名 'db' をロード
            $out['config_loaded'] = is_array($cfg) ? 'ok' : 'not_array';
            // default.connection の抜粋
            $conn = \Arr::get($cfg, 'default.connection', []);
            $out['config_snapshot'] = $conn;

            // 2) Fuel\DB で SELECT 1
            try {
                $row = \DB::query('SELECT 1 AS ok')->execute()->current();
                $out['fuel_db'] = isset($row['ok']) ? (int)$row['ok'] : null;
            } catch (\Throwable $e) {
                $out['fuel_db_error'] = $e->getMessage();
            }

            // 3) 素の mysqli で疎通（hostname/port/username/password をそのまま使う）
            try {
                $mysqli = @new \mysqli(
                    $conn['hostname'] ?? '127.0.0.1',
                    $conn['username'] ?? '',
                    $conn['password'] ?? '',
                    $conn['database'] ?? '',
                    (int)($conn['port'] ?? 3306)
                );
                if ($mysqli->connect_errno) {
                    $out['mysqli_error'] = $mysqli->connect_error;
                } else {
                    $res = $mysqli->query('SELECT 1 AS ok');
                    $row = $res ? $res->fetch_assoc() : null;
                    $out['mysqli'] = $row ? (int)$row['ok'] : null;
                    $mysqli->close();
                }
            } catch (\Throwable $e) {
                $out['mysqli_error'] = $e->getMessage();
            }

            // 4) ORM が使えるか（Model_User があれば件数）
            try {
                if (class_exists('\\Model_User')) {
                    $cnt = \Model_User::query()->count();
                    $out['orm'] = ['users_count' => $cnt];
                } else {
                    $out['orm'] = 'Model_User not found';
                }
            } catch (\Throwable $e) {
                $out['orm_error'] = $e->getMessage();
            }

            // ① 接続インスタンスを明示的に作って ping
try {
  $conn = \Database_Connection::instance('default');
  $conn->connect();
  $out['db_connected'] = $conn->connected() ? 'yes' : 'no';
} catch (\Throwable $e) {
  $out['db_connect_error'] = $e->getMessage();
}

// ② SELECT 1 を “明示的に default 接続” で実行
try {
  $row = \DB::query('SELECT 1 AS ok')->execute('default')->current();
  $out['fuel_select1'] = $row ?: null;
} catch (\Throwable $e) {
  $out['fuel_select1_error'] = $e->getMessage();
}

// ③ ビルダーでも試す
try {
  $row = \DB::select(\DB::expr('1 AS ok'))->execute('default')->current();
  $out['fuel_builder_select1'] = $row ?: null;
} catch (\Throwable $e) {
  $out['fuel_builder_error'] = $e->getMessage();
}

try {
  $out['orm_users_count'] = \Model_User::query()->count();
} catch (\Throwable $e) {
  $out['orm_error'] = $e->getMessage();
}

            return $this->response($out, 200);
        } catch (\Throwable $e) {
            return $this->response(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}