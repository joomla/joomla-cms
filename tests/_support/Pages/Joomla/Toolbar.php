<?php
namespace Pages\Joomla;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverBy as By;

class Toolbar
{
	/** @var WebDriver  */
	protected $driver;

	/** @var array  */
	protected $definition = [];

	public function __construct(\AcceptanceTester $driver, $definition)
	{
		$this->driver = $driver;
		$this->definition = $definition;
	}

	public function click($label)
	{
		$key = strtolower($label);
		$this->driver->findElement(By::id($this->definition[$key]['id']))->findElement(By::cssSelector('button'))->click();

		$pageClass = $this->definition[$key]['page'];

		return new $pageClass($this->driver);
	}
}
