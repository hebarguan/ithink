<?php
namespace app\common\model;

use think\Model;
use think\Db;

class MyModel extends Model
{
    protected function initialize()
    {
        parent::initialize();
        $this->db = new \think\db\Query;
    }

    /**
     * 手动连接数据库
     *
     * @param array  $config 手动设置配置项
     * @return void
     */
    protected function dbConnect($config = [])
    {
        // 查询结果类型
        config('database.result_type', \PDO::FETCH_CLASS);
        $dbConfig = config('database');
        if ( ! empty($config)) {
            $dbConfig = array_merge($dbConfig, $config);
        }
        $this->db->connect($dbConfig);
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
        if ($pageLengthNum === 0) {
            $pageLengthNum = 1;
        }
        return $pageLengthNum;
    }

   /**
     * 数据库动态对象查询
     * 
     * @param string $method 请求方法
     * @param string $args 参数
     * @return source
     */
    public function __call($method, $args)
    {
        return $this->db->$method($args[0]);
    }


}



