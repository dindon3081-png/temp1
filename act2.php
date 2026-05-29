<?php
require "config.php";	// 組態設定
require "fun1.php";		// 共用Fun(處理GET/POST)
require "debug.php";	// @@debug

define("FN_SQL",			'fn_sql.php');
define("FN_FILE",			'fn_File.php');
define("FN_SESS",			'fn_sess.php');
define("FN_UPLOAD",		'fn_upload.php');
define("FN_LIBS",			'fn_libs.php');

define("PATH_SET",		'setting/');							// 設定檔路徑
define("PATH_IMG",		'img/');								// 圖路徑


$action = $_GET['act'];
// $_POST = json_decode($HTTP_RAW_POST_DATA, true);	// NG: 無帶值時會死
// txt('data來源: ', $_POST);	/* @QQ: Test開關 */

// $hasPost = empty($_POST) ? false : true
// if ($hasPost) {	// NONO: 會變成跨域(CORS)的錯
if (empty($_POST)) {	// content-type == json
	$_POST = json_decode(file_get_contents('php://input'), true);
}

$params = !empty($_POST) ? CleanInput($_POST) : null;
	// txt('inputs來源: ', $_POST);	/* @QQ: Test開關 */

/* !!!PS: 定要排錯,前端會無故發2次,1次不帶值 */
if (!$params) { OutPut([]);	return; }

// txt('action: '.$action.', params來源: ', $params);	/* @QQ: Test開關 */
if (!preg_match("/LoginSer|Logout|SendMailCode/", $action)) {
	// log1('preg_match: ', $params);	// @@

	$account = GetPostKey('account');
	$token = GetPostKey('token');
	$isTrue = $account && $token && checktoken($account, $token);
	if (!$isTrue) {
		txt('NG_Act-params: ', $params);	// @@

		echo "NG!";
		return false;
	}
}

OutPut(call_user_func($action, $params));

/*
function Testser($data) {
	$result = [
			'dir'      => 111,
			'files'    => '234',
		];

	$result = json_decode(file_get_contents('php://input'), true);
	txt('Testser-result: ', $result);	// @@
	return $result;
}
 */


/* 會員資訊 - User user */
function IsRegister($data) {
	// txt('IsRegister-data: ', $data);	// @@
	include FN_SQL;		return chk_user_exist($data);
}
function Add_User($data) {
	include FN_SQL;		return Add_User_sql($data);
}
function Add_User_auto($data) {	// 針對第3方登入
	include FN_SQL;		return Add_User_auto_sql($data);
}

function Get_User() {
	include FN_SQL;		return Get_User_sql();
}

function Upd_User($data) {
	include FN_SQL;		return Upd_User_sql($data);
}


function Del_User($data) {
	include FN_SQL;		Del_User_sql((int)$data['sn']);
}
function Del_User_Betch($data) {
	// txt('Del_User_Betch: ', $data);	// @@
	include FN_SQL;
	foreach($data["arrSn"] as $key => $value) {
		if (is_numeric($value))		Del_User_sql($value);
	}
	return true;
}

/* 抓所有員工(等級11~20) */
function Get_Employ() {
	include FN_SQL;		return Get_Employ_sql();
}

function export_type($ftype = '', $bDay = 0) {	// bDay: 使用表備份,不使用則供下載用
	$now_date = $bDay ? '-'.date('YmdHis') : '';

	include FN_SQL;
	$rows = Get_Users20_sql();
	// txt('Pro_Export: ', $rows);	// @@

	$fname = 'export-'.$ftype.$now_date;
	// $file 	= PATH_SET.$fname.".json";
	$file 	= PATH_SET.$fname.".json";

	// txt('export_type: ', $file);	// @@

	/* 產生 JSON 格式,註: 加 unicode 參數(保留中文(需 php版本更新5.4以上) */
	$json = json_encode($rows, JSON_UNESCAPED_UNICODE);
	// txt('保留中文: ', $json);	// @@
	file_put_contents($file, $json);	// 產生檔案
}


/* 確認有無會員帳號 */
function Chk_Account_Exist($data) {
	include FN_SQL;		return Chk_Account_Exist_sql($data['account']);
}

function CheckSysVer() {
	/* @Fix: 版本緩存問題 */
	$obj1 = read_json("sys", "../data");
	$nVer = $obj1->sys;
	// txt('版本緩存問題: ', $nVer);	// @@

	return $nVer;
}

/* 讀取json檔案-後端專用 */
function read_json($fname, $path = null) {
	$file = (!$path ? PATH_JSON : $path."/").$fname.".json";
	// txt('filefilefile: ', $file);	// @@
	$hasFile = file_exists($file);
	// txt('hasFile: ', $hasFile);	// @@

	return $hasFile ? json_decode(file_get_contents($file)) : '';
}

function checktoken($account, $p_tok) {
	$isOK	= false;

	include FN_SESS;
	$num = GetSessKey('utype');
	// txt('checktoken-num: '.$num.', p_tok: ', $p_tok);	// @@
	if ($num) {
		$s_tok = GetSessKey('token'.$num);
		// txt('checktoken-s_tok: '.$s_tok);	// @@
		$isOK	= $p_tok && $s_tok && ($p_tok == $s_tok);
	// } else {
		// return false;
	}
	return $isOK;

	// $path = PATH_SET.$account;
	// $fullFile = $path."/token.key";
	// if (file_exists($fullFile)) {
		// $s_tok = (String)@file_get_contents($fullFile);
		// /** @Fix: 手機遺失SESSION問題-自動登入 */
		// // if (!$s_tok && $p_tok) {
			// // txt('no_sess!!');	// @@
			// // return 'no_sess';
		// // }
		// $isOK	= $p_tok && $s_tok && ($p_tok == $s_tok);
		// // txt('pos_token: '.$p_tok.', sess_token: ', $s_tok);	// @@

	// } else {
		// return false;
	// }
	// return $isOK;
}

function LoginSer($data) {
	// txt('LoginSer: ', $data);	// @@
	include FN_SQL;		return LoginSer_sql($data);
}

function Get_login_order() {
	include FN_SQL;		return Get_login_order_sql();
}

/* 取得-目前登入者帳號,姓名,等級,權限 */
function Get_User_Info($data) {
	// $resp = [];
	include FN_SQL;
	$resp = Get_User_Info_sql($data);

	$resp["roles"] = ["admin"];	// @@test temp
	// txt('after-目前登入者帳號: ', $resp);	// @@
	return $resp;
}


function Logout($data) {
	// Del_ZipFile();
	// $fullFile = PATH_SET.$data['account']."/token.key";
	// include_once FN_FILE;	file_del($fullFile);
	// $data['arrFile'] = ["token.key"];
	// // txt('Logout-arrFile: ', $data);	// @@
	// Del_Files($data);

	include_once FN_SESS;
	Del_SESSION();
}

/* 刪除檔案-多(單){移除圖檔 */
function Del_Files($data) {
	// txt('Del_ImgFile: ', $data);	// @@
	$path = PATH_SET.$data['account']."/";

	$isPic = GetPostKey('isPic');
	if ($isPic) {
		$kind = GetPostKey('kind');
		$path .= PATH_IMG.$kind."/";
	}

	/* 追加路徑 */
	$fpath = GetPostKey('fpath');
	if ($fpath) {$path .= $fpath."/";}

	include FN_FILE;
	foreach($data["arrFile"] as $key => $fname) {
		// txt('Del_ImgFile_Betch: ', $fname);	// @@
		$fullFile = $path.$fname;
		// txt('Del_Files-fullFile: ', $fullFile);	// @@
		file_del($fullFile);
	}
	// return true;
}

/* 圖片區 - Images images */
/* 上傳圖檔(Only */
function Upload_Img($data) {
	// txt('上傳圖檔案_data: ', $data);	// @@
	// txt('上傳圖檔案_FILE: ', $_FILES);	// @@
	$path = PATH_SET.$data['account']."/".PATH_IMG.$data['kind']."/";
	$fpath = GetPostKey('fpath');
	// txt('Upload_Image-fpath: ', $fpath);	// @@
	if ($fpath) {
		$path .= $fpath;
	}
	// txt('Upload_Image-path: ', $path);	// @@

	include FN_UPLOAD;
	/** @PS: 帶檔名的做法有快取問題  */
	// $fsize = GetPostKey('fsize');
	// $resault = doUpload($path, $data['fname'], $data['fsize']);		// 開始上傳
	$resault = doUpload($path);		// 開始上傳
	// txt('Upload_Image_res: ', $resault);	// @@
	/* 前端判斷 */
	return $resault;
}


/* 忘記密碼|重設密碼 */
function ResetPS($data) {
	include FN_SQL;		return ResetPS_sql($data);
}

/* 儲存json格式檔案 */
function SaveJson($data) {
	// if (!$data) { return; }

	$path = PATH_SET.$data['account'];
	$fpath = GetPostKey('fpath');		// 追加指定目錄
	// txt('Upload_Image-fpath: ', $fpath);	// @@
	if ($fpath) {$path .= $fpath;}

	// if (!is_dir($path)) { mkdir($path); }	// 登入有建
	include FN_FILE;		save_json($data, $path);
}
/* 讀取json格式檔案 */
function Get_JosFile($data) {
	// if (!$data) { return; }

	$path = PATH_SET.$data['account']."/";
	include FN_FILE;
	$filename = $path.$data['fname'];

	// txt('Get_JosFile: ', $data);	/* @QQ: Test開關 */
	$obj1 = get_json($data['fname'], $path);
	return $obj1;
}

/* 參數檔案讀寫 */
function Get_SetFile($data) {
	$filename = PATH_SET.$data['file1'];
// txt('filename: ', $filename);	// @@

	$obj1 = []; $arr1 = [];
	$i = 0;

	if (file_exists($filename)) {	//判斷是否有該檔案
		$file = fopen($filename, "r");
		//當檔案未執行到最後一筆，迴圈繼續執行(fgets一次抓一行)
		while (!feof($file)) {
			$arr1 = explode(' : ', fgets($file));
			if (count($arr1) > 1) {
				$obj1[$i]['nid'] = $arr1[0];
				$obj1[$i]['name'] = preg_replace('/\s+/', '', $arr1[1]);	// 移除換行符號
			}
			$i++;
		}
		fclose($file);
		// txt('obj1: ', $obj1);	// @@
	}
	return $obj1;
}

function Upd_SetFile($data) {
// txt('Upd_SetFile: ', $data);	// @@
	$save_file = PATH_SET.$data['file1'];

	$result = '';
	foreach($data['cont'] as $item) {
		$result .= sprintf("%s : %s\n",
					$item['nid'],
					$item['name']
				);
	}
	// txt('Upd_SetFile: ', "\n".$result);	// @@

	include FN_FILE;
	file_save($save_file, $result."\n", true);

	return true;
}

function Get_Files($data) {
	// txt('Get_Files: ', $data);	// @@
	$path = IsAdmin() ? '../admin/' : '../';
	$path .= $data['path'];
	// txt('path: ', $path);	// @@

	include FN_FILE;
	$result = get_file_dir($path);
	// txt('result: ', $result);	// @@
	return $result;
}

/* ======================================================== */
/* 以下暫時不用 */
/* ======================================================== */

/* email取得驗證碼 */
/* 
function SendMailCode($data) {
	$code  = $data['code'];
	$msg = '<p style="font-size:18px;color: blue;">歡迎您登入CRYPTO！您的OTP驗證碼為：<br><span style="color: red;">'.$code.'</span></p><br><p>該驗證碼將在30分鐘後失效，請盡快在驗證頁完成驗證。</p>';

	include FN_LIBS;

	// 收件者信箱, 信件標題, 信件內容,
	return SendMail($data['email'], '取得驗證碼-裕泰數位資產服務', $msg);
}
 */

