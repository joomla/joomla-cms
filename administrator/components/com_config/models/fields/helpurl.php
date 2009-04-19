<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

jimport('joomla.html.html');
jimport('joomla.form.field');

require_once JPATH_LIBRARIES.DS.'joomla'.DS.'form'.DS.'fields'.DS.'helpsites.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldHelpurl extends JFormFieldHelpsites
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Helpurl';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$return = parent::_getInput();
		$return .= '<input type="button" onclick="submitbutton(\'application.refreshhelp\')" value="'.JText::_('Reset').'" />';

		return $return;
	}

	protected function _getOptions()
	{
		jimport('joomla.language.help');

		$option = array();
		$option['text'] = 'Local';
		$option['value'] = 'local';

		$options	= array_merge(
						array($option),
						parent::_getOptions()
					);

		return $options;
	}
}