<?php
namespace app\common\controller;

use think\Log;
use think\Request;
use think\Controller;

class MyController extends Controller
{
    public function _initialize()
    {
        parent::_initialize();
        $this->gets = Request::instance()->get();
        $this->posts = Request::instance()->post();
        $this->data = (object)array_merge($this->gets, $this->posts);
        $this->__log(true);
    }

    /**
     * 打印请求日志
     *
     * @param bool $log true则打印
     * @return void
     */
    private function __log($log = true)
    {
        if ($log) {
            $result = explode("?", $_SERVER['REQUEST_URI']);
            if ($_SERVER['REQUEST_URI'] !== '/') {
                // 打印请求路由
                Log::record('The Request Uri Is => '.$_SERVER['REQUEST_URI']);
                // 打印post参数
                if ( ! empty($this->posts)) {
                    Log::record('The Request Post Params Is => '.json_encode($this->posts, JSON_UNESCAPED_UNICODE));
                }
                // 打印get参数
                if ( ! empty($this->gets)) {
                    Log::record('The Request Get Params Is => '.json_encode($this->gets, JSON_UNESCAPED_UNICODE));
                }
            }

        }
    }

    /**
    * 条件筛选
    * 
    * @param string $fields 原始字段
    * @param array  $data  原始数据
    * @return array
    */
    protected function _screenCondition($fields, $data) 
    {
        $screenCdi = [];
        foreach ($fields as $field => $inputField)
        {
            $fieldVal = $this->checkIfSet($data, $inputField);
            if (is_numeric($fieldVal) OR $fieldVal != '') {
                $screenCdi[$field] = $fieldVal;
            }
        }
        return $screenCdi;
    }

    /**
     * 检测数组中键是否存在
     * 若有则返回对应键=》值的数据
     *
     * @param array $checkOriData 原始数据
     * @param array $fieldStr 要检测的字段
     * @return void
     */
    protected function checkIfSet($checkOriData, $fieldStr)
    {
        $fields = explode(',', $fieldStr);
        $fieldsLength = count($fields);
        foreach ($fields as $attr) {
            $valType = gettype($checkOriData);
            if ($valType == 'array') {
                $check = (object)$checkOriData;
            } elseif ($valType == 'object') {
                $check = $checkOriData;
            }
            $attr = trim($attr);
            if (isset($check->$attr)) {
                $attrVal = $check->$attr;
                // 若数据长度只有1则返回该值
                if ($fieldsLength === 1) {
                    return $attrVal;
                }
                $returnData[$attr] = $attrVal;
                continue;
            }
    
            if ($fieldsLength === 1) {
                return;
            }
            $returnData[$attr] = '';
        }
        return $returnData;
    }

   // 检测字段是否存在
   protected function checkInputParameters($fieldStr, $method = 'get', $returnObject = false, $checkEmpty = false)
   {
       $fields = explode(',', $fieldStr);
       if (empty($fields)) {
           return false;
       }
   
       $input = Request::instance()->$method();
       foreach ($fields as $key => $fieldName) {
           $fieldName = trim($fieldName);
           if ( ! isset($input[$fieldName])) {
               $this->resError(1002);
           }
   
           if ($checkEmpty === true && $input[$fieldName] === '') {
               $this->resError(1003);
           }
           $inputData[$fieldName] = $input[$fieldName];
       }
   
       if ($returnObject !== false) {
           return (object)$inputData;
       }
   
       return $inputData;
   }

   /**
     * 系统请求错误
     *
     * @param integer $code 错误码
     * @return response
     */
    protected function resError($code = 10000)
    {
        $errResult = require_once(APP_PATH.'error/Sys.php');
        $resResult['resCode']  = 10000;
        $resResult['resMsg']  = $errResult[$code];
        Log::record("System Error Message >>>".json_encode($resResult, JSON_UNESCAPED_UNICODE));
        echo json_encode($resResult, JSON_UNESCAPED_UNICODE);
        exit;
    }


}