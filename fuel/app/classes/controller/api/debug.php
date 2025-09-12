<?php
// fuel/app/classes/controller/api/debug.php

class Controller_Api_Debug extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    public function get_index()
    {
        try {
            $db = \Fuel\Core\DB::query("SELECT DATABASE() as db")->execute()->current();
            return $this->response([
                'env'  => \Fuel::$env,
                'db'   => $db ? $db['db'] : null,
                'time' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            return $this->response([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}