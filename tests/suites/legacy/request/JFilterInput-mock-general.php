<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Mock of JFilterInput for JRequest testing
 *
 * @package     Joomla.UnitTest
 * @subpackage  Request
 *
 * @since       12.3
 */
class JFilterInputJRequest
{
	/**
	 * Information on the calls expected to the mock object.
	 *
	 * This array is indexed by a hash of the source and type; each element is
	 * an array containing the source, type, expected response and number of
	 * expected calls.
	 */
	static private $_expectations = array();

	/**
	 * Stub for the clean method.
	 *
	 * @param   mixed   $source  Input string/array-of-string to be 'cleaned'
	 * @param   string  $type    Return type for the variable (INT, FLOAT, BOOLEAN, WORD, ALNUM, CMD, BASE64, STRING, ARRAY, PATH, NONE)
	 *
	 * @return  mixed   Canned response based on table lookup.
	 */
	public function clean($source, $type = 'string')
	{
		$hash = md5($source . '|' . strtoupper($type));

		if (!isset($this->_expectations[$hash]))
		{
			$this->_expectations[$hash] = array('source' => $source, 'type' => $type, 'result' => null, 'count' => 0);
		}
		--$this->_expectations[$hash]['count'];

		return $this->_expectations[$hash]['result'];
	}

	/**
	 * Mock for the reset method.
	 *
	 * @return  void
	 */
	public function mockReset()
	{
		$this->_expectations = array();
	}

	/**
	 * Mock for the setUp method.
	 *
	 * @param   mixed   $source  Input string/array-of-string to be 'cleaned'
	 * @param   string  $type    Return type for the variable (INT, FLOAT, BOOLEAN, WORD, ALNUM, CMD, BASE64, STRING, ARRAY, PATH, NONE)
	 * @param   string  $result  Cleaned result to return
	 * @param   int     $count   Return count
	 *
	 * @return  mixed   Canned response based on table lookup.
	 */
	public function mockSetUp($source, $type, $result, $count = 1)
	{
		$hash = md5($source . '|' . strtoupper($type));
		$this->_expectations[$hash] = array('source' => $source, 'type' => $type, 'result' => $result, 'count' => $count);
	}

	/**
	 * Mock for the tearDown method.
	 *
	 * @return  mixed  Canned response based on table lookup.
	 */
	public function mockTearDown()
	{
		foreach ($this->_expectations as $hash => $info)
		{
			if (!$info['count'])
			{
				unset($this->_expectations[$hash]);
			}
		}

		if (count($this->_expectations))
		{
			return $this->_expectations;
		}

		return true;
	}
}
