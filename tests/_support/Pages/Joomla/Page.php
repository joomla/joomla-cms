<?php
namespace Pages\Joomla;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverBy as By;

abstract class Page
{
	/** @var WebDriver  */
	protected $driver;

	protected $url = 'undefined';

	public function __construct(\AcceptanceTester $driver)
	{
		$this->driver = $driver;
	}

	/**
	 * @return string
	 */
	public function url()
	{
		return $this->url;
	}

	/**
	 * @return boolean
	 */
	abstract public function isCurrent();
}
