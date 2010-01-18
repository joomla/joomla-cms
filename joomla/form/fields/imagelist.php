<?php

/**
 * @version		$Id: category.php 13825 2009-12-23 01:03:06Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

// Import html library
jimport('joomla.html.html');

// Import joomla field list class
require_once dirname(__FILE__) . DS . 'filelist.php';

/**
 * Supports an HTML select list of image
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldImageList extends JFormFieldFileList
{

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'ImageList';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions() 
	{
		$filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$';
		$this->_element->addAttribute('filter', $filter);
		return parent::_getOptions();
	}
}

