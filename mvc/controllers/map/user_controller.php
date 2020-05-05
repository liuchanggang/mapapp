<?php
require_once 'models/simple_model.php';
class user extends Controller {
    public function __construct() {
        parent::__construct();
        $this->tpl_dir = "views/map/";
        $this->Model = new Simple('users');
        session_start();
        if(!isset($_SESSION['user_id'])){
            header('Locaition:/mvc/index.php?pkg=map&contr=map&event=map');
        }
    }

    public function main(){

    }
    public function login(){
        $result = $this->Model->findbyCon(array('email'=>trim($_REQUEST['email'])));
        if (count($result) ==1 && password_verify($_REQUEST['password'], $result[0]['password'])) {
            $_SESSION['user_id'] = $result[0]['id'];
            $output = array('success' => true);
        } else {
            $output = array('success' => false);
        }
        echo json_encode($output);
        exit;
    }
    
    public function register() {
        $check = $this->Model->findbyCon(array('email'=>trim($_REQUEST['email'])));
        if (count($check) > 0) {
            $output = array('success' => false, 'msg' => 'Your email is already registered');
        }else{
            $toSave['email'] = trim($_REQUEST['email']);
            $toSave['password'] = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
            $this->Model->save($toSave);
            $_SESSION['user_id'] = $this->Model->dbCon->insert_id;
            $output = array('success' => true);
        }
        echo json_encode($output);
        exit;
    }
}