<?php
/** 
 * @date 2012-8-14
 * @version 1.0 
 * @brief 
 *  
 **/
 final class UrlSign
 {
 	/**
	 * 生成签名
	 *
	 * @param string 	$method 请求方法 "GET" or "POST"
	 * @param string 	$uri    网址部分
	 * @param array 	$params 表单参数
	 * @param string 	$secret 密钥
	 */
    static function makeSign($method, $uri, $params, $secret) 
    {
        $mk = $this->makeSource($method, $uri, $params);
        $my_sign = hash_hmac('sha1', $mk, strtr($secret, '-_', '+/'), true);
        $my_sign = base64_encode($my_sign);

        return $my_sign;
    }
    
	static function makeSource($method, $uri, $params) 
    {
        $strs = strtoupper($method) . '&' . rawurlencode($uri) . '&';

        ksort($params);
        $query_string = array();
        foreach ($params as $key => $val ) 
        { 
            array_push($query_string, $key . '=' . $val);
        }   
        $query_string = join('&', $query_string);

        return $strs . rawurlencode($query_string);
    }
 }