<?php
namespace app\common\model;

use think\Model;
use think\Db;

class MyModel extends Model
{
    protected function initialize()
    {
        parent::initialize();
    }

    /**
	 * 计算总页数
	 *
	 * @param int  $itemsNum 查询总行数
	 * @param int  $pageLength 每页行数
	 * @return int
	 */
    protected function countSumPage($itemsNum, $pageLength)
    {
		if ( ! $itemsNum OR !$pageLength) {
			return 1;
		}
        $countRes = $itemsNum%$pageLength;

        $pageLengthNum = intval($itemsNum/$pageLength);

        $pageLengthNum = $countRes > 0 ? $pageLengthNum+1 : $pageLengthNum;

        if ($pageLengthNum === 0) $pageLengthNum = 1;

        return $pageLengthNum;
    }


    /**
     * 数据库选择表
     * 
     * @param string $table 数据表
     * @return source
     */
    public function from($table)
    {
        return Db::table($table);
    }

    /**
     * 数据库选择表
     * 
     * @param string $table 数据表
     * @return source
     */
    public function __call($method, $args)
    {
        return Db::$method($args[0]);
    }


}



