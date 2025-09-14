<?php
class Controller_Api_Users extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    // GET /api/users
    public function get_index()
    {
        $items = \Model_Users::find('all');  // ORM
        return $this->response(['ok'=>true, 'items'=>$items], 200);
    }
}