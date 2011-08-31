<?php
/**
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
 * @since       11.1
 */
class JGrid
{
	/**
	 * Array of columns
	 * @var array
	 */
	protected $columns = array();
	
	/**
	 * Current active row
	 * @var int
	 */
	protected $activeRow = 0;
	
	/**
	 * Rows of the table (including header and footer rows)
	 * @var array
	 */
	protected $rows = array();
	
	/**
	 * Header and Footer row-IDs
	 * @var array
	 */
	protected $specialRows = array('header' => array(), 'footer' => array());
	
	/**
	 * Associative array of attributes for the table-tag
	 * @var array
	 */
	protected $options;
	
	/**
	 * Constructor for a JGrid object
	 * 
	 * @param array Associative array of attributes for the table-tag
	 */
	function __construct($options = array())
	{
		$this->setTableOptions($options, true);
	}
	
	/**
	 * Method to set the attributes for a table-tag
	 * 
	 * @param array Associative array of attributes for the table-tag
	 * @param bool Replace possibly existing attributes
	 * @return JGrid This object for chaining
	 */
	function setTableOptions($options = array(), $replace = false)
	{
		if($replace) {
			$this->options = $options;
		} else {
			$this->options = array_merge($this->options, $options);
		}
		return $this;
	}
	
	/**
	 * Get the Attributes of the current table
	 * @return array Associative array of attributes
	 */
	function getTableOptions()
	{
		return $this->options;
	}

	/**
	 * Add new column name to process
	 * 
	 * @param string Internal column name
	 * @return JGrid This object for chaining
	 */
	function addColumn($name)
	{
		$this->columns[] = $name;
		
		return $this;
	}
	
	/**
	 * Returns the list of internal columns
	 * @return array List of internal columns
	 */
	function getColumns()
	{
		return $this->columns;
	}
	
	/**
	 * Delete column by name
	 * 
	 * @param string Name of the column to be deleted
	 * @return JGrid This object for chaining
	 */
	function deleteColumn($name)
	{
		$index = array_search($name, $this->columns);
		if($index !== false)
		{
			unset($this->columns[$index]);
		}
		
		return $this;
	}
	
	/**
	 * Method to set a whole range of columns at once
	 * This can be used to re-order the columns, too
	 * 
	 * @param array List of internal column names
	 * @return JGrid This object for chaining
	 */
	function setColumns($columns)
	{
		$this->columns = $columns;
		
		return $this;
	}
	
	/**
	 * Adds a row to the table and sets the currently
	 * active row to the new row
	 * 
	 * @param array Associative array of attributes for the row
	 * @param int 1 for a new row in the header, 2 for a new row in the footer
	 * @return JGrid This object for chaining
	 */
	function addRow($options = array(), $special = false)
	{
		$this->rows[]['_row'] = $options;
		$this->activeRow = count($this->rows) - 1;
		if($special) {
			if($special === 1) {
				$this->specialRows['header'][] = $this->activeRow;
			} else {
				$this->specialRows['footer'][] = $this->activeRow;
			}
		}
		
		return $this;
	}
	
	/**
	 * Set the currently active row
	 * 
	 * @param int ID of the row to be set to current
	 * @return JGrid This object for chaining
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
	 * @param string Name of the column
	 * @param string Content for the cell
	 * @param array Associative array of attributes for the td-element
	 * @param bool If false, the content is appended to the current content of the cell
	 * @return JGrid This object for chaining
	 */
	function addRowCell($name, $content, $option = array(), $replace = true)
	{
		if($replace || !isset($this->rows[$this->activeRow][$name]))
		{
			$cell = new stdClass();
			$cell->options = $option;
			$cell->content = $content;
			$this->rows[$this->activeRow][$name] = $cell;
		} else {
			$this->rows[$this->activeRow][$name]->content .= $content;
			$this->rows[$this->activeRow][$name]->options = array_merge($this->rows[$this->activeRow][$name]->options, $option);
		}
		
		return $this;
	}	
	
	/**
	 * Get all data for a row
	 * 
	 * @param int ID of the row to return
	 * @return array Array of columns of a table row
	 */
	function getRow($id)
	{
		return $this->rows[$id];
	}
	
	/**
	 * Get the IDs of all rows in the table
	 * 
	 * @param int false for the standard rows, 1 for the header rows, 2 for the footer rows
	 * @return array Array of IDs
	 */
	function getRows($special = false)
	{
		if($special) {
			if($special === 1) {
				return array_keys($this->specialRows['header']);
			} else {
				return array_keys($this->specialRows['footer']);
			}
		}
		return array_keys($this->rows);
	}
		
	/**
	 * Delete a row from the object
	 * 
	 * @param int ID of the row to be deleted
	 * @return JGrid This object for chaining
	 */
	function deleteRow($id)
	{
		unset($this->rows[$id]);
		
		if(in_array($id, $this->specialRows['header'])) {
			unset($this->specialRows['header'][array_search($id, $this->specialRows['header'])]);
		}

		if(in_array($id, $this->specialRows['footer'])) {
			unset($this->specialRows['footer'][array_search($id, $this->specialRows['footer'])]);
		}
		
		return $this;
	}
		
	/**
	 * Render the HTML table
	 * 
	 * @return string The rendered HTML table
	 */
	function render()
	{
		$output = array();
		$output[] = '<table'.$this->renderAttributes($this->getTableOptions()).'>';
		
		$output[] = $this->renderHeader();
		
		$output[] = $this->renderFooter();
		
		$output[] = $this->renderBody();
		
		$output[] = '</table>';
		return implode('', $output);		
	}
	
	protected function renderHeader()
	{
		$output = array();
		if(count($this->specialRows['header'])) {
			$output[] = "<thead>\n";
			foreach($this->specialRows['header'] as $id) {
				$output[] = "\t<tr>\n";
				foreach($this->getColumns() as $name)
				{
					if(isset($this->rows[$id][$name])) {
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

	protected function renderBody()
	{
		$output = array();
		if(count(array_diff(array_keys($this->rows), $this->specialRows['header']))) {
			$output[] = "<tbody>\n";
			$row_ids = array_diff(array_keys($this->rows), $this->specialRows['header']);
			foreach($row_ids as $id) {
				$output[] = "\t<tr>\n";
				foreach($this->getColumns() as $name)
				{
					if(isset($this->rows[$id][$name])) {
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
	
	protected function renderFooter()
	{
		$output = array();
		if(count($this->specialRows['footer'])) {
			$output[] = "<tfooter>\n";
			foreach($this->specialRows['footer'] as $id) {
				$output[] = "\t<tr>\n";
				foreach($this->getColumns() as $name)
				{
					if(isset($this->rows[$id][$name])) {
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
	
	protected function renderAttributes($attributes)
	{
		if(count((array)$attributes) == 0) {
			return '';
		}
		$return = array();
		foreach($attributes as $key => $option)
		{
			$return[] = $key.'="'.$option.'"';
		}
		return ' '.implode(' ', $return);
	}
}