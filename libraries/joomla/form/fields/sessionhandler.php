<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldSessionHandler extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'SessionHandler';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		// Get the session handlers.
		jimport('joomla.session.session');
		$sessionOptions		= JSession::getStores();

		foreach ($sessionOptions as $i=>$option) {
			$sessionOptions[$i]=new JObject(array('value'=>$option,'text'=>JText::_('JLIB_VALUE_SESSION_'.$option)));
		}

		// Merge the options together.
		$options	= array_merge(
						parent::_getOptions(),
						$sessionOptions
					);

		return $options;
	}
}
