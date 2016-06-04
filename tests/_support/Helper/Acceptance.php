<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
	/**
	 * Function to getConfiguration from the acceptance.suite.yml to be used by the tests
	 *
	 * @return array
	 */
	public function getConfiguration($element = null)
	{
		if (!$element)
		{
			throw new \InvalidArgumentException('empty value or non existing element was requested from configuration');
		}

		return $this->config[$element];
	}
}
