<?php
/**
 * @file EXArray.php
 * @date 2010/07/12 19:48:58
 * @version 1.0 
 * @brief 
 *  
 **/

final class EXArray
{
	var $cacheable = true;

	/**
	 * 根据某个字段索引一个顺序数组成为一个关联数组
	 */ 
	function indexArray($arr,$keyname)
	{
		$ret = array();
		foreach($arr as $row) {
			$ret[$row[$keyname]] = $row;
		}
		return $ret;
	}
	
	/**
	 * 根据某个字段进行索引，索引后此字段具有相同值的行组成一个数组，此字段的值成为数组的Key 
	 * @param unknown_type $arr
	 * @param unknown_type $keyname
	 */
	function indexArrayEx($arr,$keyname)
	{
		$ret = array();
		foreach($arr as $row) {
			$keyvalue = $row[$keyname];
			$ret[$keyvalue][] = $row;
		}
		return $ret;
	}

	/**
	 * 提取一个数组中的某个key的值，组成一个单独的列表
	 */ 
	function extractList($arr,$keyname)
	{
		$ret = array();
		foreach($arr as $row) {
			$ret[] = $row[$keyname];
		}
		return $ret;
	}
	
	private function _randomCompare($a,$b)
	{
		return mt_rand(1,1000)%2?1:-1;	
	}
	
	function randomSort($arr)
	{
		usort($arr,array($this,'_randomCompare'));
		return $arr;
	}
	
	/**
	 * 提取一个数组中的某个key的值，组成一个单独的列表
	 */ 
	function extractListEx($arr,$keyname)
	{
		$ret = array();
		foreach($arr as $row) {
			if (isset($row[$keyname])) { 
				$ret[] = $row[$keyname];
			}
		}
		return $ret;
	}
}






/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */
