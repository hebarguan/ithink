<?php
namespace app\home\controller;
use \think\Request;
use app\common\controller\MyController;

class User extends MyController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\home\model\UserModel;
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
        $validate = [
            'username|用户名#length:10,100'
            // 'password|密码#min:10|max:20'
        ];
        $validResult = $this->checkInputParameters($validate, 'get', true);
        if (is_string($validResult)) {
            return $validResult;
        }
        $result = $this->model->userSignUp(
            $this->data->username
        );
        // var_dump($result);
        return $this->resResult(0, $result);
    }

    
}
