<?php

final class Xcrypt
{
	var $cacheable = true;
	
    const ID_LENGTH = 12;
    private $strbase = "5z1GydOFFAU2is7JQIk0BV9EuhWbwZXNjSo3cRgDqCtvfrK4xelanMpH8L6YYY";
    private $key,$length,$codelen,$codenums,$codeext;

    function __construct($key = 993.141592653589){
        $this->key = $key;
        $this->length = self::ID_LENGTH;
        $this->codelen = substr($this->strbase,0,$this->length);
        $this->codenums = substr($this->strbase,$this->length,10);
        $this->codeext = substr($this->strbase,$this->length + 10);
    }

    function encode($nums){
        if(!is_numeric($nums) || $nums < 0 || !preg_match('~^[0-9]+$~is',$nums)){
            return $nums;
        }
        $rtn = "";
        $numslen = strlen($nums);
        $begin = substr($this->codelen,$numslen - 1,1);

        $extlen = $this->length - $numslen - 1;
        $temp = str_replace('.', '', $nums / $this->key);
        $temp = substr($temp,-$extlen);

        $arrnumsTemp = str_split($this->codenums);
        $arrnums = str_split($nums);
        foreach ($arrnums as $v) {
            $rtn .= $arrnumsTemp[$v];
        }

        $arrextTemp = str_split($this->codeext);
        $arrext = str_split($temp);
        foreach ($arrext as $v) {
            $rtn .= $arrextTemp[$v];
        }
        return $begin.$rtn;
    }

    function decode($code){
        if(strlen($code)!=self::ID_LENGTH){
            return '';
        }
        $begin = substr($code,0,1);
        $rtn = '';
        $len = strpos($this->codelen,$begin);
        if($len!== false){
            $len++;
            $arrnums = str_split(substr($code,1,$len));
            foreach ($arrnums as $v) {
                $rtn .= strpos($this->codenums,$v);
            }
        }
        return $rtn;
    }

    /**
     * 返回是否有效密文
     */
    public function isValidCode($code)
    {
        return self::ID_LENGTH == strlen($code) && !is_numeric($code);
    }
}
