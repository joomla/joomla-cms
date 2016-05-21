<?php
namespace Codeception\Module;
// here you can define custom actions
// all public methods declared in helper class will be available in $I
class AcceptanceHelper extends \Codeception\Module
{
	/**
	 * Function to getConfiguration from the YML and return in the test
	 *
	 * @param null $element
	 *
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function getConfiguration($element = null)
	{
		if (is_null($element)) {
			throw new InvalidArgumentException('empty value or non existing element was requested from configuration');
		}
		return $this->config[$element];
	}
}