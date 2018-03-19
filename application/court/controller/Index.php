<?php
namespace app\court\controller;
use app\common\controller\MyController;

class Index extends MyController
{
    public function test()
    {
        echo "oo";
    }

    public function ha()
    {
        $this->my();
        // echo "fine";
    }
}
