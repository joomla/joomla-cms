<?php

class mockParams
{
	public $params;
	
	public function get($param, $default=null)
	{
		return isset($this->params[$param]) ? $this->params[$param] : $default;
	}
}