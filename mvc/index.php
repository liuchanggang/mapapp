<?php
require_once("mvc_config.php");
require_once "framework/Controller.php";
require_once "framework/Model.php";

// Save the url of the index page.
$INFO['indexurl'] = $_SERVER['SCRIPT_NAME'];

if(isset($_REQUEST['token'])){
	require_once 'models/tokens.php';
	$tokenModel = new Tokens();
	$query = "select op.*,tk.id as tk_id from d_operators op 
		inner join d_token tk on op.id=tk.account_id
		where tk.token='".$tokenModel->escaping($_REQUEST['token'])."'";
	$rs = $tokenModel->findOne($query);
	unset($rs['password']);
	session_start();
	$_SESSION['op'] = $rs;
	if($rs['tk_id']){
		$arToken = array('id'=>$rs['tk_id'],'last_login'=>date('Y-m-d H:i:s'),'last_ip'=>$_SERVER['REMOTE_ADDR']);
		$tokenModel->save($arToken);
	}
}

// Initializing modules from user input
if (array_key_exists("contr", $_REQUEST)) {
	$contr = $_REQUEST["contr"];
} else if (array_key_exists("contr", $_POST)) {
	$contr = $_POST["contr"];
}

// Load home page as default if no module is specified!
if (!isset($contr) || $contr == '') {
	$_REQUEST["contr"] = $contr = "home";
}

$pkg = empty($_REQUEST['pkg'])? '':$_REQUEST['pkg'].'/';
//Ensure controller directory path
//If controller is is a sub directory the $contr should be passed as admin_customerservice
//which results in the path admin\customerservice
//$contr = str_replace('_', '\\', $contr);
if (!file_exists("controllers/" . $pkg. $contr."_controller.php")) {
	header("Location: error.php");
	die();
}else{
	require_once("controllers/". $pkg . $contr."_controller.php");
}
// Strip slashes if magic quotes is turned on
if (get_magic_quotes_gpc()) {
	$input = array(&$_GET, &$_POST, &$_COOKIE, &$_ENV, &$_SERVER);
	while(list($k, $v) = each($input)) {
		foreach($v AS $key => $val) {
			if(!is_array($val)) {
				$input[$k][$key] = stripslashes($val);
				continue;
			}
			$input[] =& $input[$k][$key];
		}
	}
	unset($input);
}

try {

	// This code is setting the cookie, it must be
	// executed before any output.
	$contrClass = $contr;
	$contr_instance = new $contrClass();
	$contr_instance->dispatch();

} catch (Exception $error) {
	//trigger_error($error->getMessage());
	$INFO['log']->logError($error);
   	echo $error->getMessage();
}
