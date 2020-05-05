<?php

abstract class Controller {

	/**
	 * Data generated from model is stored in array and will be passed to view
	 * @var array
	 */
	public $data = array();

	/**
	 * the tpl file used for this module
	* @var string
	*/
	public $tpl;

	/**
	* Global INFO
	* @var array
	*/
	public $info = array();


	/**
	* method that should not be called as an event
	* @var string
	*/
	private $bad_method_names;

	/**
	 * if the page need login check;
	 * @var boolean
	 */
	public $need_login = true;

	/**
	 * the name for event from $_POST/$_GET, this should be fixed
	 * @var string
	 */
	private $event_var = 'event';

	/**
	 * Helper to forge url in the framework to call a controller
	 * @param $controller: the controller to call.
	 * @param $event: the event to call on the controller.
	 * @param $params: other parameters in the query string.
	 */
	public static function forge_url($controller = '', $event = '', $params = array()) {
		// Get the url of the application index.php script.
		global $INFO;
		$index_url = $INFO['indexurl'];

		// Build the query of the url.
		$params['contr'] = $controller;
		$params['event'] = $event;
		$query = http_build_query($params);

		// Forge the URL.
		require_once 'lib/http_build_url.php';
		return http_build_url($INFO['indexurl'], array('query' => $query));
	}

	/**
	 * Constructor of the controller.
	 */
	public function __construct() {
		global $INFO;
		$this->info = $INFO;
		$this->bad_method_names = array('dispatch', 'set_data', 'display', 'is_valid_method', 'isLoggedIn', 'get_member_type');
		$this->Reflection = new \ReflectionClass(get_class($this));
	}

	/**
	 * This is the default event when no event is matched
	 */
	abstract public function main();

	/**
	 * Invoke the appropriate method in concrete module class
	 * @access public
	 * @return void
	*/
	public function dispatch() {

		// Check the login of the user.
		if($this->need_login) {
		//	$this->check_login();
		}
		// Get the method from the GET or POST, or set it to main by default..
		$req = array();
		if(!isset($_GET) && !isset($_POST) ){
			global $_GET;
		}
		if(isset($_GET[$this->event_var])){
			$req = $_GET;
		}elseif(isset($_POST[$this->event_var])){
			$req = $_POST;
		}
		
		if(array_key_exists($this->event_var, $req)
			&& $req[$this->event_var] != ''
			&& $this->is_valid_method($req[$this->event_var])) {
			$event = $req[$this->event_var];
		} else {
			$event = 'main';
		}

		// Invoke the method.
		$this->info['log']->logDebug("Invoking method $event on controller ". get_class($this));
		$this->$event();

		// Extract the model.
		$this->info['log']->logDebug('with data:');
		$this->info['log']->logDebug($this->data);
		$this->data['INFO']=$this->info;
		extract($this->data);

		// Get the view.
		if(!empty($this->tpl)) {
			echo $this->load_view($this->tpl, $this->data);
		}
	}

	/**
	 * Check if this is a valid method to invoke
	 * @param $method: the method name
	 */
	private function is_valid_method($method) {
		if(array_key_exists($method, $this->bad_method_names)) {
			return false;
		}
		// magic function __get(), __call should not be called directly.
		elseif (substr($method, 0, 2) == '__') {
			return false;
		} elseif (is_subclass_of($this, 'Controller') && method_exists($this, $method)) {
			$rf = $this->Reflection->getMethod($method);
			if($rf->isConstructor() || $rf->isPrivate() || $rf->isProtected() || $rf->isAbstract() || $rf->isStatic()) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Check if the user is logged in.
	 */
	protected function check_login() {
		$this->info['log']->logDebug("check login on controller " . get_class($this));
		if(!isset($_SESSION['user'])) {
			$redirect = Controller::forge_url('login', '', array('return_page' => $_SERVER['REQUEST_URI']));
			$this->info['log']->logDebug("User is not logged in, redirecting $redirect");
			header("Location: $redirect");
			exit();
		} else {
			$this->info['log']->logDebug("User is logged in.");
		}
	}

	/**
	 * Load the view (template) with the data.
	 * @param $template: the name of the view to load.
	 * @param $data: the data of the view.
	 */
	protected function load_view($template, $data){
		extract($data);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();
		include($template); // include() vs include_once() allows for multiple views with the same name

		$buffer = ob_get_contents();
		@ob_end_clean();
		return $buffer;
	}
}

