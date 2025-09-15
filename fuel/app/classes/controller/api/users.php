<?php
class Controller_Api_Users extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    // GET /api/users
    public function get_index()
    {
        $users = Model_User::find('all');
return $this->response([
    'ok'    => true,
    'users' => $users,
]);
    }
}