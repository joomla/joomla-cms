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

jimport('joomla.form.field');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldSpacer extends JFormField
{
	/**
	 * The field type.
	 *
	 * @access	public
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Spacer';

	/**
	 * Method to get the field input.
	 *
	 * @access	protected
	 * @return	string		The field input.
	 * @since	1.6
	 */
	protected function _getInput()
	{
		return '<hr />';
	}
}