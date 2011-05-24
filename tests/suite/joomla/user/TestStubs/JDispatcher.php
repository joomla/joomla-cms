<?php

class JDispatcher
{
	static public $instance;

	public static function getInstance()
	{
		if(is_null(JDispatcher::$instance))
		{
			JDispatcher::$instance = new JDispatcher;
		}
		return JDispatcher::$instance;
	}
		

	public function trigger($event, $params)
	{
		require_once dirname(__FILE__).'/FakeAuthenticationPlugin.php';
		$plugin = new plgAuthenticationFake();
		return array(call_user_func_array(array($plugin,$event), $params));
	}
}
