<?php
namespace app\common\controller;

use think\Log;
use think\Request;
use think\Controller;

class MyController extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
        $this->request = Request::instance();
        $this->activeUrlClassicModel(); // 激活URL经典模式
        $this->gets = $this->request->get();
        $this->posts = $this->request->post();
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
        if ($log == false) {
            return false;
        }
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
            return true;
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
            $fieldVal = $this->checkInputHaving($data, $inputField);
            if (is_numeric($fieldVal) OR $fieldVal != '') {
                // 查询类型：1、 ['age >' => 23]
                $queryType = explode($fieldVal, ' ');
                if (count($queryType) > 1) {
                    $screenCdi[$queryType[0]] = [$queryType[1], $fieldVal];
                } else {
                    // 查询类型：2、['name' => 'hebar']
                    $screenCdi[$field] = $fieldVal;
                }
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
    protected function checkInputHaving($checkOriData, $fieldStr)
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

    /**
     * 检测输入参数是否存在或空值
     *
     * @param string|array $ruleFields 验证规则参数
     * @param string $method 检测类型：默认get, 可选post
     * @param boolean $cknull 是否检测为空字符串
     * @return array|object|string
     */
    protected function checkInputParameters($ruleFields, $method = 'get', $cknull = false)
    {
        $validRules = [];
        if (is_array($ruleFields)) {
            $paramKeys = $ruleFields;
        } else {
            $paramKeys = explode('&', $ruleFields);
            if (empty($paramKeys)) {
                return false;
            }
        }
        // 读取请求参数
        $input = $this->request->$method();
        foreach ($paramKeys as $inputKey)
        {
            $rulesBlock = explode('#', trim($inputKey));
            switch (count($rulesBlock))
            {
                case 1 :
                    if ($cknull === true) {
                        $validRules[] = [$rulesBlock[0], 'require', $this->errmsg(1003)];
                    }
                    break;
                case 2 :
                    if (true === $cknull) {
                        $validRules[] = [$rulesBlock[0], 'require|'.$rulesBlock[1], $this->errmsg(1003)];
                    } else {
                        $validRules[] = [$rulesBlock[0], $rulesBlock[1], $this->errmsg(1004)];
                    }
                    break;
                case 3 :
                    if (true === $cknull) {
                        $validRules[] = [$rulesBlock[0], 'require|'.$rulesBlock[1], $this->errmsg('1003|'.$rulesBlock[2])];
                    } else {
                        $validRules[] = [$rulesBlock[0], $rulesBlock[1], $rulesBlock];
                    }
                    break;
            }
            // 检测参数是否被定义
            $paramName = explode('|', $rulesBlock[0])[0];
            if ( ! isset($input[$paramName])) {
                $this->resError(1002);
            } else {
                $returnResult[$paramName] = $input[$paramName];
            }
        }
        // 使用验证规则
        if ( ! empty($validRules)) {
            $validate = new \think\Validate($validRules);
            if (! $validate->check($input)) {
                $this->resError($validate->getError());
            }
        }
        return $returnResult;
    }
   
   /**
     * 系统请求错误
     *
     * @param integer $code 错误码
     * @param string  $errmsg 手动错误提示
     * @param boolean $noecho 是否显示提示
     * @return response
     */
    protected function resError($errmsg = 1001, $httpCode = 400, $noecho = false)
    {
        if (true !== $noecho) {
            $errResult = require(APP_PATH.'error/Sys.php');
            $resError['code']  = -1;
            // 错误提示
            if (! is_numeric($errmsg)) {
                $resError['errmsg']  = $errmsg;
            } else {
                $resError['errmsg']  = $errResult[$errmsg];
            }
            // 调试模式下开启错误日志写入
            if (config('app_debug')) {
                Log::record("System Error Message >>> ".json_encode($resError, JSON_UNESCAPED_UNICODE));
            }
            header($this->httpCode($httpCode));
            header('Content-Type:application/json;charset=utf8');
            echo json_encode($resError, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

     /**
     * 请求正确，返回结果
     *
     * @param integer $code 响应码，0表示成功
     * @param string $result 相应结果
     * @return response
     */
    protected function resSuccess($result = '', $httpCode = 200, $resmsg = 'success')
    {
        $response['code'] = 0;
        $response['resmsg'] = $resmsg;
        if ($result !== '') {
            $response['data'] = $result;
        }
        if (config('app_debug')) {
            // 错误日志
            Log::record("System Error Message >>> ".json_encode($response, JSON_UNESCAPED_UNICODE));
        }
        header($this->httpCode($httpCode));
        header('Content-Type:application/json;charset=utf8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

     /**
     * 读取错误提示语
     *
     * @param string $codestr 错误码 可以：1001 或者 1001|1002 返回对应错误:系统错误 或者 系统错误|缺少参数
     * @return string
     */
    protected function errmsg($codestr)
    {
        $errmsg = [];
        $errors = require(APP_PATH.'error/Sys.php');
        $expCode = explode('|', $codestr);
        foreach ($expCode as $code)
        {
            if (is_numeric($code)) {
                if (isset($errors[$code])) {
                    $errmsg[] = $errors[$code];
                } else {
                    $errmsg[] = 'UNKOWN ERROR CODE';
                }
            } else {
                $errmsg[] = $code;
            }
        }
        return implode('|', $errmsg);
    }

    /**
     * 读取头部状态
     *
     * @param string $code 状态码
     * @return void
     */
    protected function httpCode($code)
    {
        switch ($code)
        {
            case '200' :
                //200 正常状态
                $httpStatus = 'HTTP/1.1 200 OK';
                break;
            case '301' :
                // 301 永久重定向，记得在后面要加重定向地址 Location:$url
                $httpStatus = 'HTTP/1.1 301 Moved Permanently';
                break;
            case '304' :
                // 设置页面304 没有修改
                $httpStatus = 'HTTP/1.1 304 Not Modified';
                break;
            case '400' :
                // 请求错误
                $httpStatus = 'HTTP/1.1 400 Bad Request';
                break;
            case '401' :
                // 未验证
                $httpStatus = 'HTTP/1.1 401 Unauthorized';
                break;
            case '403' :
                // 403 禁止访问
                $httpStatus = 'HTTP/1.1 403 Forbidden';
                break;
            case '500' :
                // 500 服务器错误
                $httpStatus = 'HTTP/1.1 500 Internal Server Error';
                break;
        }
        return $httpStatus;
    }

    /**
     * 添加URL经典模式:  /home/controller/method?name=hebar&age=24 有效
     * 
     * @return void
     */
    protected function activeUrlClassicModel()
    {
        $uriQuery = parse_url($_SERVER['REQUEST_URI']);
        if (isset($uriQuery['query'])) {
            // 解析?后的query字段参数
            parse_str($uriQuery, $getParams);
            $this->request->get($getParams);
        }
    }


}