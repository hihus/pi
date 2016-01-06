<?php

final class Csv{

	public function outputCSV($file, $dbResultAry) {
		//var_dump($dbResultAry);die;
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=" . $file);
		header("Pragma: no-cahce");
		header("Expires: 0");
		$outstream = fopen("php://output", "w");
		if (count($dbResultAry) > 0) {
			$cols = array();
			$row = $dbResultAry[0];
			foreach ($row as $key => $value) {
				array_push($cols, iconv("utf-8", "gbk", $key));
			}
			fputcsv($outstream, $cols);
		}
		foreach ($dbResultAry as $row) {
			$new_row = array();
			foreach ($row as $key => $value) {
				//当为空字符时
				/* if(strlen($value)==0 && $value==false) {
				$value = '-';
				} */
				$new_row[$key] = iconv('utf-8', 'gbk', $value);
			}
			fputcsv($outstream, $new_row);
		}
		fclose($outstream);
	}
}