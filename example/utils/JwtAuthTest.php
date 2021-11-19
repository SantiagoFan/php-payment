<?php
namespace JoinPhpCommon\example\utils;
use think\facade\Request;
use think\facade\Response;

class JwtAuthTest{
    public function index() {
        $jwt = new JwtAuth('test',
            'dsadadsads',
            't.cn',
            't.cn'
        );
        $data = [
            'uid'=>299632,
            'name'=>'张三'
        ];
        // 生成token
        $token = $jwt->CreateToken($data);
        vdump('TOKEN: '.$token);
        // 验证
        $is_success = $jwt->validateToken($token);
        vdump("is_success".$is_success);
        // 解析数据
        $data_2 = $jwt->parseToken($token);
        vdump($data_2);
        // 获取用户信息 验证错误则返回
        $user = $this->GetUserData('',$token);
        vdump($user);
    }
    /**
     * 获取用户信息 不存在则返回错误码 或者跳转登录
     */
    public function GetUserData($login_url,$token){
        $jwt = new JwtAuth('test',
            'dsadadsads',
            't.cn',
            't.cn'
        );
        if (empty($token)) {
            //ajax
            if (Request::isAjax()) {
                $data = ['code' => 50014, 'message' => '未登录请登录后重试'];
                $response = Response::create($data, 'json', 200, [], []);
                throw new HttpResponseException($response);// 参考tp 框架内部处理redirect 和error的思路直接输出结果
            } else {
                $response = Response::create($login_url, 'redirect', 302)->params([]);
                throw new HttpResponseException($response);
            }
        } else {
            $is_success = $jwt->validateToken($token);
            if (!$is_success) { //校验不成功
                if (Request::isAjax()) {
                    $data = ['code' => 50012, 'message' => '登录超时请重新登录'];
                    $response = Response::create($data, 'json', 200, [], []);
                    throw new HttpResponseException($response);// 参考tp 框架内部处理redirect 和error的思路直接输出结果
                } else {
                    $response = Response::create($login_url, 'redirect', 302)->params([]);
                    throw new HttpResponseException($response);
                }
            } else {//添加用戶状态信息
                return $jwt->parseToken($token);
            }
        }
    }
}
