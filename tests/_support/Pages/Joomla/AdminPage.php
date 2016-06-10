<?php
namespace Pages\Joomla;

use Facebook\WebDriver\WebDriverBy as By;

abstract class AdminPage extends Page
{
	protected $url = 'undefined';

	/**
	 * @return string
	 */
	public function message()
	{
		return $this->driver->findElement(By::id('system-message-container'))->getText();
	}

	/**
	 * @return Toolbar
	 */
	abstract public function toolbar();

	public function set($property, $value)
	{
		$this->driver->fillField($property, $value);
	}

	/**
	 * @return boolean
	 */
	public function isCurrent()
	{
	}
}
