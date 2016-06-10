<?php
namespace Pages\Joomla;

class AdminLoginPage extends Page
{
	/**
	 * @return string
	 */
	public function url()
	{
		return 'administrator';
	}

	/**
	 * @param $username
	 * @param $password
	 * 
	 * @return ControlPanelPage
	 */
	public function login($username, $password)
	{
		return new ControlPanelPage($this->driver);
	}

	/**
	 * @return boolean
	 */
	public function isCurrent()
	{
		// TODO: Implement isCurrent() method.
		return true;
	}
}
