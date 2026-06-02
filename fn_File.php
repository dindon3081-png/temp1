<?php
require_once "debug.php";	// @@debug
define("PATH_JSON",	'../data/');	// json檔路徑


function MakeDir($to_dir) {
	if (!is_dir($to_dir)) {
		/* 轉中文 */
		@mkdir(mb_convert_encoding($to_dir, 'big5', 'UTF-8'), 0755, true);
	}
}


/* 目錄刪除(含檔案 */
function dir_del($dir) {
	if (is_dir($dir)) {
		$items = scandir($dir);
		foreach ($items as $item) {
			if ($item != "." && $item != "..") {
				if (filetype($dir."/".$item) == "dir")

					dir_del($dir."/".$item);
				else
					unlink($dir."/".$item);
			}
		}
		reset($items);
		rmdir($dir);
	}
}

/* 檔案刪除 */
function file_del($fullName) {
	if (file_exists($fullName)) {
		unlink($fullName); //將檔案刪除
		return true;
	} else {
		return false;
	}
}

/* 儲存檔案 */
function file_save($file, $value = null, $overwrite = null) {
	$overwrite = $overwrite ? null : FILE_APPEND;	// 覆寫否
	/* 避免中文檔名 */
	file_put_contents(iconv("UTF-8", "big5", $file), $value, $overwrite);
}

/* 讀取json格式檔案 */
function get_json($fname, $path = null) {
	$fileName = utf8_big5($fname); // 避免中文檔名
	txt('get_json_path: ', $path);	// @@
	txt('get_json_fname: ', $fileName);	// @@

	$file = (!$path ? PATH_JSON : $path."/").$fileName.".json";
	txt('filefilefile: ', $file);	// @@

	$hasFile = file_exists($file);
	txt('hasFile: ', $hasFile);	// @@

	// return $hasFile ? json_decode(file_get_contents($file)) : '';
	/* @!!: 前端需傳純字串JSON.stringify,取回時再自轉JSON.parse */
	return $hasFile ? file_get_contents($file) : '';
}

/* 儲存json格式檔案 */
function save_json($data, $path = null) {
	// txt('save_json: ', $data);	// @@
	$fname = utf8_big5($data['fname']); // 避免中文檔名
	$file = (!$path ? PATH_JSON : $path."/").$fname.".json";
	// txt('save_json-file: ', $file); // @@

	// 取得該檔案路徑的目錄
	$dir = dirname($file);
	// txt('save_json-dir: ', $dir); // @@
	if (!is_dir($dir)) { // 目錄不存在則自動遞迴建立多層目錄
		if (!mkdir($dir, 0755, true)) {
			return false;
		}
	}

	// $isUTF8 = (bool)GetPostKey('utf8');
	/* 產生 JSON 格式,註: 加 unicode 參數(保留中文(需 php版本更新5.4以上) */
	// $json = json_encode($data['cont'], ($isUTF8 ? JSON_UNESCAPED_UNICODE : null));

	/* @!!: 前端需傳純字串JSON.stringify */
	// 去除反斜線,連續的兩個會成為一個	// ==> /
	$json = stripslashes($data['cont']);
	// txt('儲存json格式cont: ', $json);	// @@
	file_put_contents($file, $json);	// 產生檔案
}

/* 
function save_top_jos($key_kind, $sn, $bOn) {
	$file = PATH_JSON."top_".$key_kind.".json";

	$arr1 = file_exists($file) ? json_decode(file_get_contents($file)) : '';
	if (!$arr1) { return; }
	$hasFind = in_array($sn, $arr1);

	if ($bOn) {	// add
		if (!$hasFind) {	// 沒找到
			array_push($arr1, $sn);
		}
	} else {	// del
		if ($hasFind) {		// 有找到
			$arr1 = array_values(array_delete($sn, $arr1));
			// txt('contcont: ', $arr1);	// @@
		}
	}

	$json = json_encode($arr1);
	// txt('json: ', $json);	// @@

	file_put_contents($file, $json);	// 產生檔案
}
 */

/* 取得該路徑中的所有目錄(不含子目錄 */
function get_dirs($path) {
	$path = utf8_big5($path);
	if (!is_dir($path)) { return false; }
	// txt('get_dirs-path: ', $path);	// @@

	$dirs = glob($path.'\*', GLOB_ONLYDIR | GLOB_MARK);

	/* 拿掉前目錄+轉中文輸出 */
	for($i = 0, $len = count($dirs); $i < $len; $i++) {
		// $dir1 = basename($dirs[$i]);	// NG: 中文出問題
		// txt('get_dirs-basename: ', $dir1);	// @@

		$dir1 = str_replace($path, "", $dirs[$i]);
		$dirs[$i] = big5_utf8(str_replace('\\', '', $dir1));
	}
	return $dirs;
}

/* 以遞迴的方式，取得深層資料夾的所有路徑 */
// function list_dirs($dir) {
    // static $alldirs = array();
    // $dirs = glob($dir . '/*', GLOB_ONLYDIR);
    // if (count($dirs) > 0) {
        // foreach ($dirs as $d) $alldirs[] = $d;
    // }
    // foreach ($dirs as $dir) list_dirs($dir);
    // return $alldirs;
// }

/* 取得該路徑中的所有檔案 */ // 不含子目錄
function get_file_dir($path, $bFull=true) {	// $bFull含附檔名

	/** @PS: 1.去空:如有目錄造成跳號問題
		2.中文目錄,不轉中文big5會抓不到
		3.因有中文,無法用pathinfo來做,否則抓不到
	*/

	$result = array_values(array_filter(glob(utf8_big5($path).'/*'), 'is_file')); // 過濾空值+陣列重排
	// txt('陣列重排: ', $result);	// @@

	/* 針對中文 */
	// foreach($result as $file1) {	// NG!
	foreach($result as $i => $file1) {
		$file1 = big5_utf8(StrCutB($file1, '/'));
		if ($bFull) {
			$result[$i] = $file1;

		} else {
			$ext = substr(strrchr($file1, '.'), 0);	// .xxx
			$result[$i] = str_replace($ext, '', $file1);
		}
	}
	// txt('Get_result: ', $result);	// @@
	return $result;
}




/* BIG5 轉 UTF-8 */
function big5_utf8($string) {
	// return @iconv('big5', "UTF-8//TRANSLIT//IGNORE", $string);	// NG:跳過許功蓋!
	// return @iconv("big5","UTF-8", $string);	// NG:許功蓋就不見!
	return mb_convert_encoding($string, "utf-8", "big5");	// 參2: 目標編碼, 參3: 原始編碼
}

/* UTF-8 轉 BIG5 */
function utf8_big5($string) {
	//============ UTF-8 轉 BIG5 (UTF-8轉繁體中文):
	// return iconv("UTF-8", "BIG5", $string);	// NG: Detected an illegal character
	// 參2: 目標編碼, 參3: 原始編碼
	return @mb_convert_encoding($string, "BIG5", "auto");	// OK
	// return mb_convert_encoding($string, "BIG5", "utf-8");	// NG
}

// ----------------------------------------------------------------
/* 以下是網頁類 */

/* 抓網頁內容 */
function catch_html($url1) {
	$curl = curl_init();

	/* Set SSL if required */
	// if (substr($url1, 0, 5) == 'https') {
	if (Left($url1, 5) == 'https') {
		curl_setopt($curl, CURLOPT_PORT, 443);
	}

	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLINFO_HEADER_OUT, true);
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_URL, $url1);

	$json = curl_exec($curl);
	curl_close($curl);

	return $json;
	// return curl_exec($curl);	// NG: 關線了!
}

/* 網頁內容to文字 */
function html2Txt($str1) {
	/* 去除HTML 標籤 */
	$str1 = strip_tags($str1);
	// $str1 = strip_tags($str1, "<a>");	// 保留a
	$str1 = str_replace('&gt;','>',$str1);	// &gt; --> >
	$str1 = str_replace('&lt;','<',$str1);	// &gt; --> <
	$str1 = str_replace('&nbsp;','',$str1);
// txt('去除HTML標籤: ', $str1);	// @@

	$arrCont = explode("\n", $str1);	// 分割字串
	$arrCont = array_filter($arrCont);	// 過濾空值
// txt('arrCont: ', $arrCont);	// @@

	$tmp1 = '';	$val = '';
	foreach($arrCont as $key=>$value){
		$val = rtrim(trim($value));	// 去空白
		if ($val) {
			$tmp1 .= $val."\n";
		}
	}
	return $tmp1;
}

/* 移除 JS+Css */
function removeJsCss($cont) {
	/* 移除 JavaScript */
	$cont = preg_replace('/<.*script.*>/', '', $cont);
	// $cont = preg_replace('/.*.function.*/', '', $cont);

	/* 移除 css */
	$cont = preg_replace('/<.*link.*>/', '', $cont);
	$cont = preg_replace('/<.*style.*>/', '', $cont);

	$cont = preg_replace('/.*.css.*|.*.js.*/', '', $cont);
	return $cont;
}



