<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2011 Open Source Matters, Inc.
 * @version $Id: JFilterInput-mock-general.php 20196 2011-01-09 02:40:25Z ian $
 *
 */

/**
 * Mock of JFilterInput for JRequest testing
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
	 * @param   mixed   Input string/array-of-string to be 'cleaned'
	 * @param   string  Return type for the variable (INT, FLOAT, BOOLEAN, WORD, ALNUM, CMD, BASE64, STRING, ARRAY, PATH, NONE)
	 * @return  mixed   Canned response based on table lookup.
	 */
	function clean($source, $type='string')
	{
		$hash = md5($source . '|' . strtoupper($type));
		if (! isset($this -> _expectations[$hash])) {
			$this -> _expectations[$hash] = array(
				'source' => $source,
				'type' => $type,
				'result' => null,
				'count' => 0
			);
		}
		--$this->_expectations[$hash]['count'];
		return $this->_expectations[$hash]['result'];
	}

	function mockReset() {
		$this->_expectations = array();
	}

	function mockSetUp($source, $type, $result, $count = 1) {
		$hash = md5($source . '|' . strtoupper($type));
		$this->_expectations[$hash] = array(
			'source' => $source,
			'type' => $type,
			'result' => $result,
			'count' => $count
		);
	}

	function mockTearDown() {
		foreach ($this -> _expectations as $hash => $info) {
			if (! $info['count']) {
				unset($this -> _expectations[$hash]);
			}
		}
		if (count($this -> _expectations)) {
			return $this -> _expectations;
		}
		return true;
	}

}
