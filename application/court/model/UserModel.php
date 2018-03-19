<?php
namespace app\court\model;

use app\common\model\MyModel;
use think\Db;

class UserModel extends MyModel
{
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 用户注册
     * 
     * @param string $usename 用户名
     * @param string $password 密码
     * @return int|boo
     */
    public function useSignUp($username, $password)
    {
        $this->query('LOCK TABLE `user` WRITE');
        $query = $this->from('user')->where('username', $username)->find();
        var_dump($query);
    }

}