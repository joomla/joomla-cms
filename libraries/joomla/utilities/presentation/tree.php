<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Description
 * 
 * @package 	Joomla.Framework
 * @since 1.1
 */
class JTree {
	/** @var string The name of the primary key */
	var $idName = '';
	/** @var string The name of the parent field */
	var $parentName = '';
	/** @var string The name of the text field to display */
	var $textName = '';
	/** @var array The data array of objects */
	var $_data = null;
	/** @var array Child relationships */
	var $_children = null;
	/** @var string Prefix for text value */
	var $_prefix = '';
	/** @var string The indent spacer */
	var $_spacer = '';
	/** @var int Maximum levels to process */
	var $_maxLevel = 0;

	/**
	 * Constructor
	 */
	function JTree() {
		$this->_maxLevel = 9999;
	}

	/**
	 * @param array An array of data objects
	 * @param string The name of the primary key or id field, defaults to id
	 * @param string The name of the parent field, defaults to parent_id
	 * @param string The name of the text field to display, defaults to name
	 */
	function importObjectList( &$data, $idName='id', $parentName='parent_id', $textName='name' ) {
		$this->idName = $idName;
		$this->parentName = $parentName;
		$this->textName = $textName;

		$this->_data = &$data;
		$this->_maxLevel = 9999;

		// preprocess children relationships
		$this->_children = array();
		foreach ($this->_data as $v) {
			$this->addChild( $v->$parentName, $v );
		}
	}

	/**
	 * Imports a /path/path/item style of structure
	 */
	function importPathList( &$paths, $separator, $idName='id', $parentName='parent_id', $textName='name' ) {
		$data = array();
		// first pass, assemble an object list
		foreach ($paths as $path) {

			//$path = str_replace( $stub, '', $path );
			$dirName = dirname( $path ) . $separator;

			$obj = new stdClass();
			$obj->$idName = $path;
			$obj->$parentName = dirname( $path );
			$obj->$textName = str_replace( $dirName, '', $path );
			$data[] = $obj;
		}
		//print_r($data);
		$this->importObjectList( $data, $idName, $parentName, $textName );
	}

	/**
	 * Add child data
	 * @param mixed The id field for the parent item
	 * @param mixed The data variable
	 */
	function addChild( $parent_id, &$data ) {
		if (!isset( $this->_children[$parent_id] )) {
			$this->_children[$parent_id] = array();
		}
		$this->_children[$parent_id][] = $data;
	}

	/**
	 * Sets a predfined indent style
	 * @param int 0=none, 1=dashed, 2=hooked
	 */
	function setIndentType( $type=0 ) {
		switch ($type) {
			case 0:
				break;
			case 1:
				$this->_prefix = '- ';
				$this->_spacer = '&nbsp;&nbsp;';
				break;
			case 2:
				$this->_prefix = '<sup>L</sup>&nbsp;';
				$this->_spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				break;
		}
	}

	/**
	 * Manual sets the indent style
	 * @param string The text prefix
	 * @param string The spacer for the indent
	 */
	function setIntent( $prefix, $spacer ) {
		$this->_prefix = $prefix;
		$this->_spacer = $spacer;
	}

	/**
	 * @param int The maximum levels to process
	 */
	function setMaxLevel( $maxLevel ) {
		$this->_maxLevel = intval( $maxLevel );
	}

	/**
	 * @param int Indent style: 0=none, 1=dashed, 2=hooked
	 * @param int The parent_id to start with
	 * @param int The offset to slice the tree array
	 * @param int The number of items to have in the returned array
	 * @return array
	 */
	function toArray( $type=0, $start=0, $limitstart=0, $limit=0 ) {
		if (count( $this->_children ) == 0) {
			return array();
		}

		$this->setIndentType( $type );
		$keys = array_keys( $this->_children );
		if (!in_array( $start, $keys )) {
			$start = $keys[0];
		}
		$list = $this->_toArray( $start );

		if ($limitstart > 0 && $limit > 0) {
			$list = array_slice( $list, $limitstart, $limit );
		}

		return $list;
	}

	/**
	 * @return array
	 */
	function _toArray( $id=0, $indent='', $list=array(), $level=0 ) {
		$idName = $this->idName;
		$parentName = $this->parentName;
		$textName = $this->textName;

		if (@$this->_children[$id] && $level <= $this->_maxLevel) {
			foreach ($this->_children[$id] as $v) {
				$id = $v->$idName;
				$text = $v->$textName;
				$parent = $v->$parentName;

				if ($parent != 0) {
					$text = $this->_prefix . $text;
				}

				$v->treename = $indent . $text;
				$v->treelevel = $level;
				$v->children = count( @$this->_children[$id] );
				$list[] = $v;
				$list = $this->_toArray( $id, $indent . $this->_spacer, $list, $level+1 );
			}
		}
		return $list;
	}

	/**
	 * Render list in unordered list format with templates
	 * @param object patTemplate object
	 * @param int The parent_id to start with
	 * @return string
	 */
	function toUL( &$tmpl, $start=0 ) {
		if (count( $this->_children ) == 0) {
			return array();
		}
		$keys = array_keys( $this->_children );
		if (!in_array( $start, $keys )) {
			$start = $keys[0];
		}

		return $this->_toUL( $tmpl, $start );
	}

	/**
	 * Recursive renderer for unordered lists
	 * @param object patTemplate
	 * @param mixed The array index of the current element
	 * @param int The current level in the tree
	 * @return string
	 */
	function _toUL( &$tmpl, $id=0, $level=0 ) {
		$idName = $this->idName;
		$html = '';

		if (@$this->_children[$id] && $level <= $this->_maxLevel) {
			$tmpl->clearTemplate( 'tree-open' );
			$tmpl->addVar( 'tree-open', 'level', $level );
			$html .= $tmpl->getParsedTemplate( 'tree-open' );
			foreach ($this->_children[$id] as $v) {
				$id = $v->$idName;

				$tmpl->clearTemplate( 'tree-item' );
				$tmpl->addObject( 'tree-item', $v );
				$html .= $tmpl->getParsedTemplate( 'tree-item' );
				$html .= $this->_toUL( $tmpl, $id, $level+1 );
			}
			$html .= $tmpl->getParsedTemplate( 'tree-close' );
		}
		return $html;
	}
}
?>
