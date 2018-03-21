<?php
namespace app\home\controller;
use app\common\controller\MyController;

class Index extends MyController
{
    public function test()
    {
        $this->resError(1001);
    }

    public function ha()
    {
        echo '<pre>';
        var_dump($this->request->param(false));
        foreach ($this->request->param(false) as $key => $val)
        {
            echo "$key   =>  $val <br/>";
        }
        echo "</pre>";
    }
}
