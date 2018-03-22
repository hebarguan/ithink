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
            'username|用户名#number'
            // 'password|密码#min:10|max:20'
        ];
        $this->checkInputParameters($validate, 'get', true);
        $result = $this->model->userSignUp(
            $this->data->username
        );
        // var_dump($result);
        $this->resSuccess($result);
    }

    /**
     * 测试2
     * 
     * @return response
     */
    public function mc()
    {
        // 同一端口和IP地址请求频次为：1秒2次
        $time = 1;
        $connectLimit = 2;
        $lockExpire = 20;  // 锁时间（秒）
        $memcache = memcache_connect('localhost', 11211);
        // $memcache->flush();exit;
        $server = $this->request->server();
        $userip = $server['REMOTE_ADDR'];
        $port = $server['REMOTE_PORT'];
        $mcKey = $userip.'_'.$port;  // 用户信息键
        $connectLockName = $mcKey.'_lock'; // 请求锁名
        if ($memcache->get($connectLockName)) {
            return $this->resError(1006);
        }
        // 时间戳
        $record = $memcache->get($mcKey);
        if ($record) {
            $times = explode('_', $record);
            $connectTimes = $times[1];  // 连接次数
            $lastTime = (float)$times[0]; // 最后一次连接时间
            $countTime = microtime(true) - $lastTime;
            // 计算比值是否超过限制
            $diffValue = (float)($time/$connectLimit) - (float)($countTime/$connectTimes);
            if ($diffValue > 0) {  
                // 已超过访问限制,开启请求连接锁
                if ($memcache->get($connectLockName)) {
                    $memcache->set($connectLockName, 1, false, $lockExpire);
                } else {
                    $memcache->add($connectLockName, 1, false, $lockExpire);
                }
                // 重新设置
                $memcache->delete($mcKey);
                // 返回错误
                return json();//$this->resError(1005);

            } else {
                // 未超限制，增加访问次数
                $times = explode('_', $record);
                $nowTime = microtime(true);
                $inc = $time[1]+1;
                $memcache->set($mcKey, $nowTime.'_'.$inc);
            }
        } else {
            $mcValue = microtime(true).'_1';
            $memcache->add($mcKey, $mcValue, false, 300);
            $memcache->close();
        }
        // 执行成功
        return $this->resResult(0);
    }

    
}
