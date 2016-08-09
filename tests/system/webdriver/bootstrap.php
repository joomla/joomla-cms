<?php

class SeleniumClientAutoLoader {

	// Array of page class files
	private $pageClassFiles = array();

	public function __construct()
	{
		spl_autoload_register(array($this, 'seleniumClientLoader'));
		$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator('../Pages/'),
				RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $file)
		{
			if ($file->isFile())
			{
				$this->pageClassFiles[substr($file->getFileName(), 0, (strlen($file->getFileName()) - 4))] = (string) $file;
			}
		}
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
		elseif (isset($this->pageClassFiles[$className]) && file_exists($this->pageClassFiles[$className]))
		{
			include $this->pageClassFiles[$className];
		}

	}

}

$autoloader = new SeleniumClientAutoLoader();