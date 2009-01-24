<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

jimport('joomla.html.html');
jimport('joomla.form.fields.list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldIntegers extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @access	public
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Integers';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @access	protected
	 * @return	array		An array of JHtml options.
	 * @since	1.6
	 */
	protected function _getOptions()
	{
		$first		= (int)$this->_element->attributes('first');
		$last		= (int)$this->_element->attributes('last');
		$step		= (int)max(1, $this->_element->attributes('step'));
		$options	= array();

		for ($i = $first; $i <= $last; $i += $step) {
			$options[] = JHTML::_('select.option', $i);
		}

		return $options;
	}
}