<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

jimport('joomla.html.grid');


/**
 * General inspector class for JHrid.
 *
 * @package Joomla.UnitTest
 * @subpackage HTML
 * @since 11.3
 */
class JGridInspector extends JGrid
{
	/**
	* Method for inspecting protected variables.
	*
	* @return mixed The value of the class variable.
	*/
	public function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		} else {
			trigger_error('Undefined or private property: ' . __CLASS__.'::'.$name, E_USER_ERROR);
			return null;
		}
	}

	/**
	* Sets any property from the class.
	*
	* @param string $property The name of the class property.
	* @param string $value The value of the class property.
	*
	* @return void
	*/
	public function __set($property, $value)
	{
		$this->$property = $value;
	}
	
	/**
	 * Calls any inaccessible method from the class.
	 * 
	 * @param string 	$name Name of the method to invoke 
	 * @param array 	$parameters Parameters to be handed over to the original method
	 * 
	 * @return mixed The return value of the method 
	 */
	public function __call($name, $parameters = false)
	{
		return call_user_func_array(array($this,$name), $parameters);
	}
}

/**
 * Test class for JGrid.
 * 
 * @since 11.3
 */
class JGridTest extends PHPUnit_Framework_TestCase
{	
	/**
	 * Test for JGrid::__construct method.
	 */
	public function test__construct()
	{
		$table = new JGrid();
		$this->assertThat(
			($table instanceof JGrid),
			$this->isTrue()
		);
		
		$options = array('class' => 'center', 'width' => '50%');
		$table = new JGrid($options);
		$this->assertThat(
			$table->getTableOptions(),
			$this->equalTo($options)
		);
	}

	/**
	 * Test for JGrid::__toString method.
	 */
	public function test__toString()
	{
		$table = new JGrid();
		$table->addColumn('testCol1');
		$table->addRow(array('class' => 'test1'));
		$table->addRowCell('testCol1', 'testcontent1', array('class' => '1'));
		
		$this->assertThat(
			(string) $table,
			$this->equalTo($table->toString())
		);
	}
	
	/**
	 * Test for JGrid::setTableOptions method.
	 */
	public function testSetTableOptions()
	{
		$options = array('class' => 'center', 'width' => '50%');
		$table = new JGrid();
		$table->setTableOptions($options);
		$this->assertThat(
			$table->getTableOptions(),
			$this->equalTo($options)
		);
	}

	/**
	 * Test for JGrid::getTableOptions method.
	 */
	public function testGetTableOptions()
	{
		$options = array('class' => 'center', 'width' => '50%');
		$table = new JGrid();
		$table->setTableOptions($options);
		$this->assertThat(
			$table->getTableOptions(),
			$this->equalTo($options)
		);
	}

	/**
	 * Test for JGrid::addColumn method.
	 */
	public function testAddColumn()
	{
		$table = new JGrid();
		$table->addColumn('test1');
		$this->assertThat(
			$table->getColumns(),
			$this->equalTo(array('test1'))
		);
	}

	/**
	 * Test for JGrid::getColumns method.
	 */
	public function testGetColumns()
	{
		$table = new JGrid();
		$table->addColumn('test1');
		$this->assertThat(
			$table->getColumns(),
			$this->equalTo(array('test1'))
		);
	}

	/**
	 * Test for JGrid::deleteColumn method.
	 */
	public function testDeleteColumn()
	{
		$table = new JGrid();
		$table->addColumn('test1');
		$table->addColumn('test2');
		$table->addColumn('test3');
		$this->assertThat(
			$table->getColumns(),
			$this->equalTo(array('test1', 'test2', 'test3'))
		);
		$table->deleteColumn('test2');
		$this->assertThat(
			$table->getColumns(),
			$this->equalTo(array('test1', 'test3'))
		);
	}

	/**
	 * Test for JGrid::setColumns method.
	 */
	public function testSetColumns()
	{
		$table = new JGrid();
		$table->addColumn('test1');
		$table->addColumn('test2');
		$table->addColumn('test3');
		$this->assertThat(
			$table->getColumns(),
			$this->equalTo(array('test1', 'test2', 'test3'))
		);
		$array = array('test1', 'test3');
		$table->setColumns($array);
		$this->assertThat(
			$table->getColumns(),
			$this->equalTo(array('test1', 'test3'))
		);
		
		$array = array('test3', 'test1');
		$table->setColumns($array);
		$this->assertThat(
			$table->getColumns(),
			$this->equalTo(array('test3', 'test1'))
		);
	}

	/**
	 * Test for JGrid::addRow method.
	 */
	public function testAddRow()
	{
		$table = new JGridInspector();
		$table->addRow();
		$this->assertThat(
			$table->rows,
			$this->equalTo(array(0 => array('_row' => array())))
		);
		$this->assertThat(
			$table->getActiveRow(),
			$this->equalTo(0)
		);
		$table->addRow();
		$this->assertThat(
			$table->rows,
			$this->equalTo(array(0 => array('_row' => array()), 1 => array('_row' => array())))
		);
		$this->assertThat(
			$table->getActiveRow(),
			$this->equalTo(1)
		);
	}

	/**
	 * Test for JGrid::setActiveRow method.
	 */
	public function testSetActiveRow()
	{
		$table = new JGridInspector();
		$table->addRow(array('class' => 'test1'));
		$table->addRow(array('class' => 'test2'));
		$table->addRow(array('class' => 'test3'));
		$this->assertThat(
			$table->getActiveRow(),
			$this->equalTo(2)
		);
		$table->setActiveRow(1);
		$this->assertThat(
			$table->getActiveRow(),
			$this->equalTo(1)
		);
	}
	
	/**
	 * Test for JGrid::getActiveRow method.
	 */
	public function testGetActiveRow()
	{
		$table = new JGridInspector();
		$table->addRow(array('class' => 'test1'));
		$table->addRow(array('class' => 'test2'));
		$table->addRow(array('class' => 'test3'));
		$this->assertThat(
			$table->getActiveRow(),
			$this->equalTo(2)
		);
		$table->setActiveRow(1);
		$this->assertThat(
			$table->getActiveRow(),
			$this->equalTo(1)
		);
	}

	/**
	 * Test for JGrid::addRowCell method.
	 */
	public function testAddRowCell()
	{
		$table = new JGridInspector();
		$table->addColumn('testCol1');
		$table->addColumn('testCol2');
		$table->addColumn('testCol3');
		$table->addRow(array('class' => 'test1'));
		$table->addRowCell('testCol1', 'testcontent1', array('class' => '1'));
		$table->addRow(array('class' => 'test2'));
		$table->addRowCell('testCol2', 'testcontent2');
		$table->addRow(array('class' => 'test3'));
		$table->addRowCell('testCol3', 'testcontent3');
		$assertion = new stdClass();
		$assertion->options = array();
		$assertion->content = 'testcontent3';
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test3'), 'testCol3' => $assertion))
		);
		$table->setActiveRow(1);
		$assertion->content = 'testcontent2';
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test2'), 'testCol2' => $assertion))
		);
		$table->setActiveRow(0);
		$assertion->content = 'testcontent1';
		$assertion->options = array('class' => '1');
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion))
		);
		
		//Test replacing existing content
		$table->addRowCell('testCol1', 'testcontent4', array('test' => 'difcontent'));
		$assertion->content = 'testcontent4';
		$assertion->options = array('test' => 'difcontent');
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion))
		);
		
		//Test appending content
		$table->addRowCell('testCol1', ' appendedcontent', array('class' => '1'), false);
		$assertion->content = 'testcontent4 appendedcontent';
		$assertion->options = array('test' => 'difcontent', 'class' => '1');
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion))
		);
		
		//Test adding another cell
		$table->addRowCell('testCol2', 'Col2content');
		$assertion2 = new stdClass();
		$assertion2->content = 'Col2content';
		$assertion2->options = array();
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion, 'testCol2' => $assertion2))
		);
	}

	/**
	 * Test for JGrid::getRow method.
	 */
	public function testGetRow()
	{
		$table = new JGrid();
		$table->addColumn('testCol1');
		$table->addRow(array('class' => 'test1'));
		
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test1')))
		);
	}

	/**
	 * Test for JGrid::getRows method.
	 */
	public function testGetRows()
	{
		$table = new JGridInspector();
		$table->addColumn('testCol1');
		$table->addRow(array('class' => 'test1'));
		$table->addRowCell('testCol1', 'testcontent1', array('class' => '1'));
		$assertion = new stdClass();
		$assertion->options = array('class' => '1');
		$assertion->content = 'testcontent1';
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion))
		);
	}

	/**
	 * Test for JGrid::deleteRow method.
	 */
	public function testDeleteRow()
	{
		$table = new JGrid();
		$table->addColumn('testCol1');
		$table->addRow(array('class' => 'test1'));
		$table->addRowCell('testCol1', 'testcontent1', array('class' => '1'));
		$table->addRow();
		$assertion = new stdClass();
		$assertion->options = array('class' => '1');
		$assertion->content = 'testcontent1';
		$this->assertThat(
			$table->getRow(0),
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion))
		);
		$table->deleteRow(0);
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array()))
		);
	}

	/**
	 * Test for JGrid::toString method.
	 */
	public function testToString()
	{
		$table = new JGrid();
		$table->addColumn('testCol1');
		$table->addRow(array('class' => 'test1'));
		$table->addRowCell('testCol1', 'testcontent1', array('class' => '1'));
		$this->assertThat(
			$table->toString(),
			$this->equalTo("<table><tbody>\n\t<tr>\n\t\t<td class=\"1\">testcontent1</td>\n\t</tr>\n</tbody></table>")
		);
	}
	
	/**
	 * Test for JGrid::renderHeader method.
	 */
	public function testRenderHeader()
	{
		$table = new JGridInspector();
		$table->columns = array('testCol1');
		$content = new stdClass();
		$content->options = array('class' => 'test1');
		$content->content = 'testcontent';
		$table->rows = array(0 => array('_row' => array(), 'testCol1' => $content));
		$table->specialRows = array('header' => array(0), 'footer' => array());
		$this->assertThat(
			$table->renderHeader(),
			$this->equalTo("<thead>\n\t<tr>\n\t\t<th class=\"test1\">testcontent</th>\n\t</tr>\n</thead>")
		);
	}

	/**
	 * Test for JGrid::renderFooter method.
	 */
	public function testRenderFooter()
	{
		$table = new JGridInspector();
		$table->columns = array('testCol1');
		$content = new stdClass();
		$content->options = array('class' => 'test1');
		$content->content = 'testcontent';
		$table->rows = array(0 => array('_row' => array(), 'testCol1' => $content));
		$table->specialRows = array('header' => array(), 'footer' => array(0));
		$this->assertThat(
			$table->renderFooter(),
			$this->equalTo("<tfooter>\n\t<tr>\n\t\t<th class=\"test1\">testcontent</th>\n\t</tr>\n</tfooter>")
		);
	}
	
	/**
	 * Test for JGrid::renderBody method.
	 */
	public function testRenderBody()
	{
		$table = new JGridInspector();
		$table->columns = array('testCol1');
		$content = new stdClass();
		$content->options = array('class' => 'test1');
		$content->content = 'testcontent';
		$table->rows = array(0 => array('_row' => array(), 'testCol1' => $content));
		$this->assertThat(
			$table->renderBody(),
			$this->equalTo("<tbody>\n\t<tr>\n\t\t<td class=\"test1\">testcontent</td>\n\t</tr>\n</tbody>")
		);
	}
	
	/**
	 * Test for JGrid::renderAttributes method.
	 */
	public function testRenderAttributes()
	{
		$table = new JGridInspector();
		$this->assertThat(
			$table->renderAttributes(array('class' => 'test1')),
			$this->equalTo(' class="test1"')
		);
	}
}