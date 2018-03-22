<?php
namespace app\home\model;

use app\common\model\MyModel;
use think\Db;

class UserModel extends MyModel
{
    // 模型默认表user仅模型查询有效，$this->db Query 查询必须指定表名
    protected $table = 'user';

    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 用户注册
     * 
     * @param string $usename 用户名
     * @param string $password 密码
     * @return int|bool
     */
    public function userSignUp($username, $password = 'haha')
    {
        // 手动连接数据库
        $this->dbConnect();
        $this->db->name('user');
        $this->db->field('count(username) as rows');
        $pdoQuery = $this->db->where('username', 'jaychou')->find();
        return $pdoQuery;
    }

}