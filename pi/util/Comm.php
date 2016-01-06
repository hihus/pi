<?php
/**
 * @file Common.php
 * @author wanghe (hihu@qq.com)
 **/

Class Comm {
	
	static function getClientIp(){
		if (isset($_SERVER['HTTP_CLIENT_IP']) and !empty($_SERVER['HTTP_CLIENT_IP'])){
			return _IPFilter($_SERVER['HTTP_CLIENT_IP']);
		}
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ',');
			do{
				$ip = ip2long($ip);
				//-------------------
				// skip private ip ranges
				//-------------------
				// 10.0.0.0 - 10.255.255.255
				// 172.16.0.0 - 172.31.255.255
				// 192.168.0.0 - 192.168.255.255
				// 127.0.0.1, 255.255.255.255, 0.0.0.0
				//-------------------
				if (!(($ip == 0) or ($ip == 0xFFFFFFFF) or ($ip == 0x7F000001) or
				(($ip >= 0x0A000000) and ($ip <= 0x0AFFFFFF)) or
				(($ip >= 0xC0A8FFFF) and ($ip <= 0xC0A80000)) or
				(($ip >= 0xAC1FFFFF) and ($ip <= 0xAC100000)))){
					return long2ip($ip);
				}
			} while ($ip = strtok(','));
		}
		if (isset($_SERVER['HTTP_PROXY_USER']) and !empty($_SERVER['HTTP_PROXY_USER'])){
			return _IPFilter($_SERVER['HTTP_PROXY_USER']);
		}
		if (isset($_SERVER['REMOTE_ADDR']) and !empty($_SERVER['REMOTE_ADDR'])){
			return _IPFilter($_SERVER['REMOTE_ADDR']);
		}else{
			return "0.0.0.0";
		}
	}

	static function contentFilter($v){
		if(is_numeric($v) || is_object($v)){
			return $v;
		}

		return $v;
	}

	//输入安全过滤
	static function filter($v){
		if(is_array($v)){
			while(list($key,$val) = each($v)){
				$v[$key] = self::contentFilter($val);
			}
		}else{
			$v = self::contentFilter($v);
		}
		return $v;
	}
	//输出安全过滤
	static function filterOutput($v){
		return self::removeXss($v);
	}

	static function get($name = '',$default = ''){
		if($name == ''){
			return $_GET;
		}
		if(isset($_GET[$name])){
			return self::filter($_GET[$name]);
		}
		return $default;
	}
	
	static function post($name = '',$default = ''){
		if($name == ''){
			return $_POST;
		}
		if(isset($_POST[$name])){
			return self::filter($_POST[$name]);
		}
		return $default;
	}
	
	static function req($name = '',$default = ''){
		if($name == ''){
			return $_REQUEST;
		}
		if(isset($_REQUEST[$name])){
			return self::filter($_REQUEST[$name]);
		}
		return $default;
	}
	static function setReq($name,$value){
		$_REQUEST[$name] = self::filter($value);
	}

	static function getFile($name = ''){
		if($name == ''){
			return $_FILES;
		}
		if (isset($_FILES[$name])) {
			return $_FILES[$name];
		}
		return null;
	}
	
	static function getCookie($name = '',$default = ''){
		if($name == ''){
			return $_COOKIE;
		}
		if(isset($_COOKIE[$name])){
			return $_COOKIE[$name];
		}
		return $default;
	}
	
	static function setCookie($name,$value,$expire = '',$path = '/'){
		$expire  = ($expire === '') ? (time() + 86400) : intval($expire);
		return setcookie($name,$value,$expire,$path);
	}
	
	static function delCookie($name){
		return self::setCookie($name,'',time() - 8640000);
	}

	static function getSession($name = '',$default = ''){
		if(session_status() !== PHP_SESSION_ACTIVE) {session_start();}
		if($name == ''){
			return $_SESSION;
		}
		if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		}
		return $default;
	}

	static function setSession($name,$value){
		if(session_status() !== PHP_SESSION_ACTIVE) {session_start();}
		$_SESSION[$name] = $value;
		return $value;
	}

	static function getHeader($name,$default = ''){
		$name = 'HTTP_'.strtoupper($name);
		return self::getServer($name,$default);
	}

	static function getServer($name = '',$default = ''){
		if($name == ''){
			return $_SERVER;
		}
		if (isset($_SERVER[$name])) {
			return $_SERVER[$name];
		}
		return $default;
	}

	static function getHost(){
		return self::getHeader('HOST');
	}

	static function getRefer(){
		return self::getHeader('REFERER');
	}

	static function getUA($format = false){
		$userAgent = self::getHeader('USER_AGENT');
		if($format !== false){
			$userAgent = self::formatUA($userAgent);
		}
		return $userAgent;
	}

	static function formatUA($ua){
		//规则自定义，比如把 user_agent 简化
		return $ua;
	}

	static function getServers($name,$default = ''){
		if(isset($_SERVER[$name])){
			return $SERVER[$name];
		}
		return $default;
	}

	//预留，预处理HTTP的一些参数
	static function reqFilter($params = array()){
		return false;
	}

	static function jump($url){
		header('Location:'.$url);
		exit;
	}

	static function removeXss($val) {
		// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
		// this prevents some character re-spacing such as <java\0script>
		// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
		$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

		// straight replacements, the user should never need these since they're normal characters
		// this prevents like <IMG SRC=@avascript:alert('XSS')>
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i++) {
		  // ;? matches the ;, which is optional
		  // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

		  // @ @ search for the hex values
		  $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
		  // @ @ 0{0,7} matches '0' zero to seven times
		  $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
		}

		// now the only remaining whitespace attacks are \t, \n, and \r
		$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
		$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		$ra = array_merge($ra1, $ra2);

		$found = true; // keep replacing as long as the previous round replaced something
		while ($found == true) {
		  $val_before = $val;
		  for ($i = 0; $i < sizeof($ra); $i++) {
		     $pattern = '/';
		     for ($j = 0; $j < strlen($ra[$i]); $j++) {
		        if ($j > 0) {
		           $pattern .= '(';
		           $pattern .= '(&#[xX]0{0,8}([9ab]);)';
		           $pattern .= '|';
		           $pattern .= '|(&#0{0,8}([9|10|13]);)';
		           $pattern .= ')*';
		        }
		        $pattern .= $ra[$i][$j];
		     }
		     $pattern .= '/i';
		     $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
		     $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
		     if ($val_before == $val) {
		        // no replacements were made, so exit the loop
		        $found = false;
		     }
		  }
		}
		return $val;
	}

	/**
     * 判断是否是合法的手机号(单个)
     * 目前只支持中国大陆手机号验证
     * @param string $mobile_number 手机号码
     * @param string $nation 国别，目前仅支持中国大陆
     * @return boolean
     */
    static function validMobile($mobile_number, $nation = 'China/Mainland') {
        // 电话号码判断，包括虚拟运营商号段
        /**
         * 移动号码段: 134、135、136、137、138、139、147、150、151、152、157、158、159、178、182、183、184、187、188
         * 联通号码段: 130、131、132、145、155、156、176、185、186
         * 电信号码段: 133、153、177、180、181、189
         * 虚拟运营商: 170、176、178
         */
        if ($nation == 'China/Mainland') {
            $reg = "/^13[0-9]{9}$|147[0-9]{8}|15[012356789]{1}[0-9]{8}$|17[0678]{1}[0-9]{8}$|18[0-9]{9}$/";
        } else {
            $reg = "*";
        }
        if (preg_match($reg, $mobile_number)) {
            return TRUE;
        }
        return FALSE;
    }

//end of class
}