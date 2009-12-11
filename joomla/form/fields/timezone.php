<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
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
class JFormFieldTimezone extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Timezone';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		if (strlen($this->value) == 0) {
			$conf = &JFactory::getConfig();
			$value = $conf->getValue('config.offset');
		}

		$options = array();
		$group = '';
		foreach (DateTimeZone::listIdentifiers() as $tz) {
			if ($group != substr($tz, 0, strpos($tz, '/'))) {
				$group = substr($tz, 0, strpos($tz, '/'));
				$options[] = JHtml::_('select.optgroup', $group);
			}
			$options[] = JHtml::_('select.option', $tz);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);

		return $options;
	}
}