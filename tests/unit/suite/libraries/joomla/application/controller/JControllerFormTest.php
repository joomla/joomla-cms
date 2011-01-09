<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

class JControllerFormTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		jimport('joomla.application.component.controllerform');
		require_once 'controller_classes.php';
	}

	/**
	 * Returns an array of the structure of the object
	 *
	 * @return mixed	Boolean false on parsing error, array if successful.
	 */
	private function getStructure($object)
	{
		$m = array();
		preg_match('#(.*)__set_state\((.*)\)$#s', var_export($object, true), $m);
		if (empty($m[2])) {
			return false;
		} else {
			$structure = null;
			eval('$structure = '.$m[2].';');
			return $structure;
		}
	}

	public function testConstructor()
	{
		//
		// Test the auto-naming of the _option, _context, _view_item and _view_list
		//

		$object = new MincesControllerMince(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path'	=> JPATH_BASE.'/component/com_foobar'
			)
		);

		$this->assertThat(
			$structure = $this->getStructure($object),
			$this->logicalNot($this->isFalse()),
			'Line: '.__LINE__.' must be able to parse the '.get_class($object).' class'
		);

		// Check the _option variable was created properly.
		$this->assertThat(
			$structure['option'],
			$this->equalTo('com_minces')
		);

		// Check the _context variable was created properly.
		$this->assertThat(
			$structure['context'],
			$this->equalTo('mince')
		);

		// Check the _view_item variable was created properly.
		$this->assertThat(
			$structure['view_item'],
			$this->equalTo('mince')
		);

		// Check the _view_list variable was created properly.
		$this->assertThat(
			$structure['view_list'],
			$this->equalTo('minces')
		);

		//
		// Test for correct pluralisation.
		//

		$object = new MiniesControllerMiny(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path'	=> JPATH_BASE.'/component/com_foobar'
			)
		);

		$this->assertThat(
			$structure = $this->getStructure($object),
			$this->logicalNot($this->isFalse()),
			'Line: '.__LINE__.' must be able to parse the '.get_class($object).' class'
		);

		// Check the _view_list variable was created properly.
		$this->assertThat(
			$structure['view_list'],
			$this->equalTo('minies')
		);

		$object = new MintsControllerMint(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path'	=> JPATH_BASE.'/component/com_foobar'
			)
		);

		$this->assertThat(
			$structure = $this->getStructure($object),
			$this->logicalNot($this->isFalse()),
			'Line: '.__LINE__.' must be able to parse the '.get_class($object).' class'
		);

		// Check the _view_list variable was created properly.
		$this->assertThat(
			$structure['view_list'],
			$this->equalTo('mints')
		);
	}

	public function testDisplay()
	{
		$object = new MintsControllerMint(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path'	=> JPATH_BASE.'/component/com_foobar'
			)
		);
		/*
		Need to mock JRoute!!!
		$object->display();
		if ($structure = $this->getStructure($object))
		{
			print_r($structure);
		}
		else {
			$this->fail('Could not parse '.get_class($object));
		}
		*/
	}
}
