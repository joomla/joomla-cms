<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Grid
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * General inspector class for JGrid.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Grid
 * @since       11.3
 */
class JGridInspector extends JGrid
{
	/**
	 * Method for inspecting protected variables.
	 *
	 * @param   string  $name  Variable name
	 *
	 * @return mixed The value of the class variable.
	 */
	public function __get($name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			trigger_error('Undefined or private property: ' . __CLASS__ . '::' . $name, E_USER_ERROR);

			return null;
		}
	}

	/**
	 * Sets any property from the class.
	 *
	 * @param   string  $property  The name of the class property.
	 * @param   string  $value     The value of the class property.
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
	 * @param   string      $name        Name of the method to invoke
	 * @param   array|bool  $parameters  Parameters to be handed over to the original method
	 *
	 * @return mixed The return value of the method
	 */
	public function __call($name, $parameters = false)
	{
		return call_user_func_array(array($this, $name), $parameters);
	}
}

/**
 * Test class for JGrid.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       11.3
 */
class JGridTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test for JGrid::__construct method.
	 *
	 * @return void
	 */
	public function test__construct()
	{
		$table = new JGrid;
		$this->assertThat(
			($table instanceof JGrid),
			$this->isTrue()
		);

		$options = array('class' => 'center', 'width' => '50%');
		$table = new JGridInspector($options);
		$this->assertThat(
			$table->options,
			$this->equalTo($options)
		);
	}

	/**
	 * Test for JGrid::__toString method.
	 *
	 * @return void
	 */
	public function test__toString()
	{
		$table = new JGridInspector;
		$table->addColumn('testCol1');
		$table->addRow(array('class' => 'test1'));
		$table->setRowCell('testCol1', 'testcontent1', array('class' => '1'));

		$this->assertThat(
			(string) $table,
			$this->equalTo($table->toString())
		);
	}

	/**
	 * Test for JGrid::setTableOptions method.
	 *
	 * @return void
	 */
	public function testSetTableOptions()
	{
		$options = array('class' => 'center', 'width' => '50%');
		$table = new JGridInspector;
		$table->setTableOptions($options);
		$this->assertThat(
			$table->options,
			$this->equalTo($options)
		);
	}

	/**
	 * Test for JGrid::getTableOptions method.
	 *
	 * @return void
	 */
	public function testGetTableOptions()
	{
		$options = array('class' => 'center', 'width' => '50%');
		$table = new JGridInspector;
		$table->options = $options;
		$this->assertThat(
			$table->getTableOptions(),
			$this->equalTo($options)
		);
	}

	/**
	 * Test for JGrid::addColumn method.
	 *
	 * @return void
	 */
	public function testAddColumn()
	{
		$table = new JGridInspector;
		$table->addColumn('test1');
		$this->assertThat(
			$table->columns,
			$this->equalTo(array('test1'))
		);
	}

	/**
	 * Test for JGrid::getColumns method.
	 *
	 * @return void
	 */
	public function testGetColumns()
	{
		$table = new JGridInspector;
		$table->columns = array('test1');
		$this->assertThat(
			$table->getColumns(),
			$this->equalTo(array('test1'))
		);
	}

	/**
	 * Test for JGrid::deleteColumn method.
	 *
	 * @return void
	 */
	public function testDeleteColumn()
	{
		$table = new JGridInspector;
		$table->columns = array('test1', 'test2', 'test3');
		$table->deleteColumn('test2');
		$this->assertThat(
			$table->columns,
			$this->equalTo(array('test1', 'test3'))
		);
	}

	/**
	 * Test for JGrid::setColumns method.
	 *
	 * @return void
	 */
	public function testSetColumns()
	{
		$table = new JGridInspector;
		$table->columns = array('test1', 'test2', 'test3');
		$array = array('test4', 'test5');
		$table->setColumns($array);
		$this->assertThat(
			$table->columns,
			$this->equalTo(array('test4', 'test5'))
		);
	}

	/**
	 * Test for JGrid::addRow method.
	 *
	 * @return void
	 */
	public function testAddRow()
	{
		$table = new JGridInspector;
		$table->addRow();
		$this->assertThat(
			$table->rows,
			$this->equalTo(array(0 => array('_row' => array())))
		);
		$this->assertThat(
			$table->activeRow,
			$this->equalTo(0)
		);
		$table->addRow();
		$this->assertThat(
			$table->rows,
			$this->equalTo(array(0 => array('_row' => array()), 1 => array('_row' => array())))
		);
		$this->assertThat(
			$table->activeRow,
			$this->equalTo(1)
		);
		$table->addRow(array('class' => 'test'));
		$this->assertThat(
			$table->rows,
			$this->equalTo(array(0 => array('_row' => array()), 1 => array('_row' => array()), 2 => array('_row' => array('class' => 'test'))))
		);
		$this->assertThat(
			$table->activeRow,
			$this->equalTo(2)
		);
		$table->addRow(array(), 1);
		$this->assertThat(
			$table->specialRows,
			$this->equalTo(array('header' => array(3), 'footer' => array()))
		);
		$table->addRow(array(), 2);
		$this->assertThat(
			$table->specialRows,
			$this->equalTo(array('header' => array(3), 'footer' => array(4)))
		);
	}

	/**
	 * Test for JGrid::getRowOptions method.
	 *
	 * @return void
	 */
	public function testGetRowOptions()
	{
		$table = new JGridInspector;

		$table->rows = array(0 => array('_row' => array()));
		$table->activeRow = 0;

		$this->assertThat(
			$table->getRowOptions(),
			$this->equalTo(array())
		);

		$new = array('test' => 'test1');

		$table->rows = array(0 => array('_row' => $new));

		$this->assertThat(
			$table->getRowOptions(),
			$this->equalTo($new)
		);
	}

	/**
	 * Test for JGrid::setRowOptions method.
	 *
	 * @return void
	 */
	public function testSetRowOptions()
	{
		$table = new JGridInspector;

		$table->rows = array(0 => array('_row' => array()));
		$table->activeRow = 0;

		$new = array('test' => 'test1');

		$table->setRowOptions($new);

		$this->assertThat(
			$table->rows[0]['_row'],
			$this->equalTo($new)
		);
	}

	/**
	 * Test for JGrid::setActiveRow method.
	 *
	 * @return void
	 */
	public function testSetActiveRow()
	{
		$table = new JGridInspector;
		$table->rows = array(array('_row' => array('class' => 'test1')),
			array('_row' => array('class' => 'test2')),
			array('_row' => array('class' => 'test3')));
		$table->activeRow = 2;
		$table->setActiveRow(1);
		$this->assertThat(
			$table->activeRow,
			$this->equalTo(1)
		);
	}

	/**
	 * Test for JGrid::getActiveRow method.
	 *
	 * @return void
	 */
	public function testGetActiveRow()
	{
		$table = new JGridInspector;
		$table->rows = array(array('_row' => array('class' => 'test1')),
			array('_row' => array('class' => 'test2')),
			array('_row' => array('class' => 'test3')));
		$table->activeRow = 2;
		$this->assertThat(
			$table->getActiveRow(),
			$this->equalTo(2)
		);
		$table->activeRow = 1;
		$this->assertThat(
			$table->getActiveRow(),
			$this->equalTo(1)
		);
	}

	/**
	 * Test for JGrid::addRowCell method.
	 *
	 * @return void
	 */
	public function testSetRowCell()
	{
		$table = new JGridInspector;
		$table->columns = array('testCol1', 'testCol2', 'testCol3');
		$table->rows = array(array('_row' => array('class' => 'test1')),
			array('_row' => array('class' => 'test2')),
			array('_row' => array('class' => 'test3')));
		$assertion = new stdClass;
		$assertion->options = array();
		$assertion->content = 'testcontent3';

		$table->activeRow = 0;
		$table->setRowCell('testCol1', 'testcontent1', array('class' => '1'));
		$table->activeRow = 1;
		$table->setRowCell('testCol2', 'testcontent2');
		$table->activeRow = 2;
		$table->setRowCell('testCol3', 'testcontent3');
		$this->assertThat(
			$table->rows[2],
			$this->equalTo(array('_row' => array('class' => 'test3'), 'testCol3' => $assertion))
		);
		$assertion->content = 'testcontent2';
		$this->assertThat(
			$table->rows[1],
			$this->equalTo(array('_row' => array('class' => 'test2'), 'testCol2' => $assertion))
		);
		$assertion->content = 'testcontent1';
		$assertion->options = array('class' => '1');
		$this->assertThat(
			$table->rows[0],
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion))
		);

		// Test replacing existing content
		$table->activeRow = 0;
		$table->setRowCell('testCol1', 'testcontent4', array('test' => 'difcontent'));
		$assertion->content = 'testcontent4';
		$assertion->options = array('test' => 'difcontent');
		$this->assertThat(
			$table->rows[0],
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion))
		);

		// Test appending content
		$table->setRowCell('testCol1', ' appendedcontent', array('class' => '1'), false);
		$assertion->content = 'testcontent4 appendedcontent';
		$assertion->options = array('class' => '1');
		$this->assertThat(
			$table->rows[0],
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion))
		);

		// Test adding another cell
		$table->setRowCell('testCol2', 'Col2content');
		$assertion2 = new stdClass;
		$assertion2->content = 'Col2content';
		$assertion2->options = array();
		$this->assertThat(
			$table->rows[0],
			$this->equalTo(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion, 'testCol2' => $assertion2))
		);
	}

	/**
	 * Test for JGrid::getRow method.
	 *
	 * @return void
	 */
	public function testGetRow()
	{
		$table = new JGridInspector;
		$table->columns = array('testCol1');
		$table->rows = array(0 => array('_row' => array('ref' => 'idtest')), 1 => array('_row' => array('class' => 'test1')));
		$table->activeRow = 1;

		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test1')))
		);

		$this->assertThat(
			$table->getRow(0),
			$this->equalTo(array('_row' => array('ref' => 'idtest')))
		);
	}

	/**
	 * Test for JGrid::getRows method.
	 *
	 * @return void
	 */
	public function testGetRows()
	{
		$table = new JGridInspector;
		$table->columns = array('testCol1');
		$assertion = new stdClass;
		$assertion->options = array('class' => '1');
		$assertion->content = 'testcontent1';

		$table->rows = array(
			0 => array('_row' => array('class' => 'test1'), 'testCol1' => $assertion),
			1 => array('_row' => array('class' => 'test2'), 'testCol1' => $assertion),
			2 => array('_row' => array('class' => 'test3'), 'testCol1' => $assertion)
		);
		$table->specialRows = array('header' => array(1), 'footer' => array(2));
		$this->assertThat(
			$table->getRows(),
			$this->equalTo(array(0))
		);
		$this->assertThat(
			$table->getRows(1),
			$this->equalTo(array(1))
		);
		$this->assertThat(
			$table->getRows(2),
			$this->equalTo(array(2))
		);
	}

	/**
	 * Test for JGrid::deleteRow method.
	 *
	 * @return void
	 */
	public function testDeleteRow()
	{
		$table = new JGridInspector;
		$table->columns = array('testCol1');
		$assertion = new stdClass;
		$assertion->options = array('class' => '1');
		$assertion->content = 'testcontent1';

		$table->rows = array(
			0 => array('_row' => array('class' => 'test1'), 'testCol1' => $assertion),
			1 => array('_row' => array('class' => 'test2'), 'testCol1' => $assertion),
			2 => array('_row' => array('class' => 'test3'), 'testCol1' => $assertion)
		);
		$table->specialRows = array('header' => array(1), 'footer' => array(2));

		$table->deleteRow(0);
		$this->assertThat(
			$table->rows,
			$this->equalTo(
				array(
				1 => array('_row' => array('class' => 'test2'), 'testCol1' => $assertion),
				2 => array('_row' => array('class' => 'test3'), 'testCol1' => $assertion)
			)
			)
		);
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test3'), 'testCol1' => $assertion))
		);
		$this->assertThat(
			$table->specialRows,
			$this->equalTo(array('header' => array(1), 'footer' => array(2)))
		);
		$table->deleteRow(1);
		$this->assertThat(
			$table->rows,
			$this->equalTo(
				array(
				2 => array('_row' => array('class' => 'test3'), 'testCol1' => $assertion)
			)
			)
		);
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(array('_row' => array('class' => 'test3'), 'testCol1' => $assertion))
		);
		$this->assertThat(
			$table->specialRows,
			$this->equalTo(array('header' => array(), 'footer' => array(2)))
		);
		$table->deleteRow(2);
		$this->assertThat(
			$table->rows,
			$this->equalTo(array())
		);
		$this->assertThat(
			$table->getRow(),
			$this->equalTo(false)
		);
		$this->assertThat(
			$table->specialRows,
			$this->equalTo(array('header' => array(), 'footer' => array()))
		);
	}

	/**
	 * Test for JGrid::toString method.
	 *
	 * @return void
	 */
	public function testToString()
	{
		$table = new JGridInspector;
		$table->columns = array('testCol1');
		$assertion = new stdClass;
		$assertion->options = array('class' => '1');
		$assertion->content = 'testcontent1';
		$table->rows = array(array('_row' => array('class' => 'test1'), 'testCol1' => $assertion));

		// Make sure the body is rendered correctly
		$this->assertThat(
			$table->toString(),
			$this->equalTo("<table><tbody>\n\t<tr class=\"test1\">\n\t\t<td class=\"1\">testcontent1</td>\n\t</tr>\n</tbody></table>")
		);

		// Make sure the header is rendered correctly
		$table->specialRows = array('header' => array(0), 'footer' => array());

		$this->assertThat(
			$table->toString(),
			$this->equalTo("<table><thead>\n\t<tr class=\"test1\">\n\t\t<th class=\"1\">testcontent1</th>\n\t</tr>\n</thead></table>")
		);

		// Make sure the footer is rendered correctly
		$table->specialRows = array('header' => array(), 'footer' => array(0));

		$this->assertThat(
			$table->toString(),
			$this->equalTo("<table><tfoot>\n\t<tr class=\"test1\">\n\t\t<td class=\"1\">testcontent1</td>\n\t</tr>\n</tfoot></table>")
		);
	}

	/**
	 * Test for JGrid::renderArea method.
	 *
	 * @return void
	 */
	public function testRenderArea()
	{
		$table = new JGridInspector;
		$table->columns = array('testCol1');
		$content = new stdClass;
		$content->options = array('class' => 'test1');
		$content->content = 'testcontent';
		$table->rows = array(0 => array('_row' => array(), 'testCol1' => $content));
		$table->specialRows = array('header' => array(0), 'footer' => array());
		$this->assertThat(
			$table->renderArea(array(0), 'thead', 'th'),
			$this->equalTo("<thead>\n\t<tr>\n\t\t<th class=\"test1\">testcontent</th>\n\t</tr>\n</thead>")
		);
	}

	/**
	 * Test for JGrid::renderAttributes method.
	 *
	 * @return void
	 */
	public function testRenderAttributes()
	{
		$table = new JGridInspector;
		$this->assertThat(
			$table->renderAttributes(array('class' => 'test1')),
			$this->equalTo(' class="test1"')
		);

		$this->assertThat(
			$table->renderAttributes(array('class' => 'test1', 'ref' => 'test5')),
			$this->equalTo(' class="test1" ref="test5"')
		);
	}
}
