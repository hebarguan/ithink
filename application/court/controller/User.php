<?php
namespace app\court\controller;
use \think\Request;
use app\common\controller\MyController;

class User extends MyController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\court\model\UserModel($this->data);
    }

    /**
     * 用户注册
     * 
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $repassword 确认密码
     * @return response
     */
    public function signUp()
    {
        var_dump($this->request->post());
        $this->checkInputParameters('username,password,repassword', 'get', true, true);
        $this->model->userSignUp(
            $this->data->username,
            $this->data->repassword
        );
    }

    
}
