<?php

class SeleniumClientAutoLoader {

	public function __construct()
	{
		spl_autoload_register(array($this, 'seleniumClientLoader'));
	}

	private function seleniumClientLoader($className)
	{
		$fileName = "../" . str_replace("\\", "/", $className) . '.php';
		if (file_exists($fileName))
		{
			include "../" . str_replace("\\", "/", $className) . '.php';
		}
		elseif (file_exists('../' . $fileName))
		{
			include "../" . $fileName;
		}
		elseif (file_exists('../Pages/' . $className . '.php'))
		{
			include '../Pages/' . $className . '.php';
		}

	}

}

$autoloader = new SeleniumClientAutoLoader();