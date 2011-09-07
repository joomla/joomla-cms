<?php
/**
 * JGrid class to dynamically generate HTML tables
 * 
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE 
 */

defined('JPATH_PLATFORM') or die;

/**
 * JGrid class to dynamically generate HTML tables
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.3
 */
class JGrid
{
	/**
	 * Array of columns
	 * @var array
	 * @since 11.3
	 */
	protected $columns = array();

	/**
	 * Current active row
	 * @var int
	 * @since 11.3
	 */
	protected $activeRow = 0;

	/**
	 * Rows of the table (including header and footer rows)
	 * @var array
	 * @since 11.3
	 */
	protected $rows = array();

	/**
	 * Header and Footer row-IDs
	 * @var array
	 * @since 11.3
	 */
	protected $specialRows = array('header' => array(), 'footer' => array());

	/**
	 * Associative array of attributes for the table-tag
	 * @var array
	 * @since 11.3
	 */
	protected $options;

	/**
	 * Constructor for a JGrid object
	 * 
	 * @param   array  $options  Associative array of attributes for the table-tag
	 * 
	 * @since 11.3
	 */
	function __construct($options = array())
	{
		$this->setTableOptions($options, true);
	}

	/**
	 * Magic function to render this object as a table.
	 *
	 * @return  string
	 * 
	 * @since 11.3
	 */
	function __toString()
	{
		return $this->toString();
	}

	/**
	 * Method to set the attributes for a table-tag
	 * 
	 * @param   array  $options  Associative array of attributes for the table-tag
	 * @param   bool   $replace  Replace possibly existing attributes
	 * 
	 * @return  JGrid This object for chaining
	 * 
	 * @since 11.3
	 */
	function setTableOptions($options = array(), $replace = false)
	{
		if ($replace)
		{
			$this->options = $options;
		}
		else
		{
			$this->options = array_merge($this->options, $options);
		}
		return $this;
	}

	/**
	 * Get the Attributes of the current table
	 * 
	 * @return  array Associative array of attributes
	 * 
	 * @since 11.3
	 */
	function getTableOptions()
	{
		return $this->options;
	}

	/**
	 * Add new column name to process
	 * 
	 * @param   string  $name  Internal column name
	 * 
	 * @return  JGrid This object for chaining
	 * 
	 * @since 11.3
	 */
	function addColumn($name)
	{
		$this->columns[] = $name;

		return $this;
	}

	/**
	 * Returns the list of internal columns
	 * 
	 * @return  array List of internal columns
	 * 
	 * @since 11.3
	 */
	function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Delete column by name
	 * 
	 * @param   string  $name  Name of the column to be deleted
	 * 
	 * @return  JGrid This object for chaining
	 * 
	 * @since 11.3
	 */
	function deleteColumn($name)
	{
		$index = array_search($name, $this->columns);
		if ($index !== false)
		{
			unset($this->columns[$index]);
			$this->columns = array_values($this->columns);
		}

		return $this;
	}

	/**
	 * Method to set a whole range of columns at once
	 * This can be used to re-order the columns, too
	 * 
	 * @param   array  $columns  List of internal column names
	 * 
	 * @return  JGrid This object for chaining
	 * 
	 * @since 11.3
	 */
	function setColumns($columns)
	{
		$this->columns = array_values($columns);

		return $this;
	}

	/**
	 * Adds a row to the table and sets the currently
	 * active row to the new row
	 * 
	 * @param   array  $options  Associative array of attributes for the row
	 * @param   int    $special  1 for a new row in the header, 2 for a new row in the footer
	 * 
	 * @return  JGrid This object for chaining
	 * 
	 * @since 11.3
	 */
	function addRow($options = array(), $special = false)
	{
		$this->rows[]['_row'] = $options;
		$this->activeRow = count($this->rows) - 1;
		if ($special)
		{
			if ($special === 1)
			{
				$this->specialRows['header'][] = $this->activeRow;
			}
			else
			{
				$this->specialRows['footer'][] = $this->activeRow;
			}
		}

		return $this;
	}

	/**
	 * Get the currently active row ID
	 * 
	 * @return  int ID of the currently active row
	 * 
	 * @since 11.3
	 */
	function getActiveRow()
	{
		return $this->activeRow;
	}

	/**
	 * Set the currently active row
	 * 
	 * @param   int  $id  ID of the row to be set to current
	 * 
	 * @return  JGrid This object for chaining
	 * 
	 * @since 11.3
	 */
	function setActiveRow($id)
	{
		$this->activeRow = (int) $id;
		return $this;
	}

	/**
	 * Add information for a specific column for the
	 * currently active row
	 * 
	 * @param   string  $name     Name of the column
	 * @param   string  $content  Content for the cell
	 * @param   array   $option   Associative array of attributes for the td-element
	 * @param   bool    $replace  If false, the content is appended to the current content of the cell
	 * 
	 * @return  JGrid This object for chaining
	 * 
	 * @since 11.3
	 */
	function addRowCell($name, $content, $option = array(), $replace = true)
	{
		if ($replace || !isset($this->rows[$this->activeRow][$name]))
		{
			$cell = new stdClass;
			$cell->options = $option;
			$cell->content = $content;
			$this->rows[$this->activeRow][$name] = $cell;
		}
		else
		{
			$this->rows[$this->activeRow][$name]->content .= $content;
			$this->rows[$this->activeRow][$name]->options = array_merge($this->rows[$this->activeRow][$name]->options, $option);
		}

		return $this;
	}

	/**
	 * Get all data for a row
	 * 
	 * @param   int  $id  ID of the row to return
	 * 
	 * @return  array Array of columns of a table row
	 * 
	 * @since 11.3
	 */
	function getRow($id = false)
	{
		if ($id === false)
		{
			$id = $this->activeRow;
		}

		if (isset($this->rows[(int)$id]))
		{
			return $this->rows[(int)$id];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the IDs of all rows in the table
	 * 
	 * @param   int  $special  false for the standard rows, 1 for the header rows, 2 for the footer rows
	 * 
	 * @return  array Array of IDs
	 * 
	 * @since 11.3
	 */
	function getRows($special = false)
	{
		if ($special)
		{
			if ($special === 1)
			{
				return $this->specialRows['header'];
			}
			else
			{
				return $this->specialRows['footer'];
			}
		}
		return array_diff(array_keys($this->rows), array_merge($this->specialRows['header'], $this->specialRows['footer']));
	}

	/**
	 * Delete a row from the object
	 * 
	 * @param   int  $id  ID of the row to be deleted
	 * 
	 * @return  JGrid This object for chaining
	 * 
	 * @since 11.3
	 */
	function deleteRow($id)
	{
		unset($this->rows[$id]);

		if (in_array($id, $this->specialRows['header']))
		{
			unset($this->specialRows['header'][array_search($id, $this->specialRows['header'])]);
		}

		if (in_array($id, $this->specialRows['footer']))
		{
			unset($this->specialRows['footer'][array_search($id, $this->specialRows['footer'])]);
		}

		if ($this->activeRow == $id)
		{
			end($this->rows);
			$this->activeRow = key($this->rows);
		}

		return $this;
	}

	/**
	 * Render the HTML table
	 * 
	 * @return  string The rendered HTML table
	 * 
	 * @since 11.3
	 */
	function toString()
	{
		$output = array();
		$output[] = '<table'.$this->renderAttributes($this->getTableOptions()).'>';

		$output[] = $this->renderHeader();

		$output[] = $this->renderFooter();

		$output[] = $this->renderBody();

		$output[] = '</table>';
		return implode('', $output);
	}

	/**
	 * Renders the table header
	 * 
	 * @return  string The table header
	 * 
	 * @since 11.3
	 */
	protected function renderHeader()
	{
		$output = array();
		if (count($this->specialRows['header']))
		{
			$output[] = "<thead>\n";
			foreach ($this->specialRows['header'] as $id)
			{
				$output[] = "\t<tr>\n";
				foreach ($this->getColumns() as $name)
				{
					if (isset($this->rows[$id][$name]))
					{
						$column = $this->rows[$id][$name];
						$output[] = "\t\t<th".$this->renderAttributes($column->options).'>'.$column->content."</th>\n";
					}
				}

				$output[] = "\t</tr>\n";
			}
			$output[] = "</thead>";
		}
		return implode('', $output);
	}

	/**
	 * Renders the table body
	 * 
	 * @return  string the body of the table
	 * 
	 * @since 11.3
	 */
	protected function renderBody()
	{
		$output = array();
		if (count(array_diff(array_keys($this->rows), $this->specialRows['header'])))
		{
			$output[] = "<tbody>\n";
			$row_ids = array_diff(array_keys($this->rows), array_merge($this->specialRows['header'], $this->specialRows['footer']));
			foreach ($row_ids as $id)
			{
				$output[] = "\t<tr>\n";
				foreach ($this->getColumns() as $name)
				{
					if (isset($this->rows[$id][$name]))
					{
						$column = $this->rows[$id][$name];
						$output[] = "\t\t<td".$this->renderAttributes($column->options).'>'.$column->content."</td>\n";
					}
				}

				$output[] = "\t</tr>\n";
			}
			$output[] = "</tbody>";
		}
		return implode('', $output);
	}

	/**
	 * Renders the footer of an HTML table
	 * 
	 * @return  string The footer of the table
	 * 
	 * @since 11.3
	 */
	protected function renderFooter()
	{
		$output = array();
		if (count($this->specialRows['footer']))
		{
			$output[] = "<tfooter>\n";
			foreach ($this->specialRows['footer'] as $id)
			{
				$output[] = "\t<tr>\n";
				foreach ($this->getColumns() as $name)
				{
					if (isset($this->rows[$id][$name]))
					{
						$column = $this->rows[$id][$name];
						$output[] = "\t\t<th".$this->renderAttributes($column->options).'>'.$column->content."</th>\n";
					}
				}

				$output[] = "\t</tr>\n";
			}
			$output[] = "</tfooter>";
		}
		return implode('', $output);
	}

	/**
	 * Renders an HTML attribute from an associative array
	 * 
	 * @param   array  $attributes  Associative array of attributes
	 * 
	 * @return  string The HTML attribute string
	 * 
	 * @since 11.3
	 */
	protected function renderAttributes($attributes)
	{
		if (count((array)$attributes) == 0)
		{
			return '';
		}
		$return = array();
		foreach ($attributes as $key => $option)
		{
			$return[] = $key.'="'.$option.'"';
		}
		return ' '.implode(' ', $return);
	}
}