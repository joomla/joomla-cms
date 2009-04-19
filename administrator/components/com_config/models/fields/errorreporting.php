<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

jimport('joomla.html.html');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @since		1.6
 */
class JFormFieldErrorreporting extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Errorreporting';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$options = array (
			JHtml::_('select.option',	-1,								JText::_('Config Error System Default')),
			JHtml::_('select.option',	0,								JText::_('Config Error None')),
			JHtml::_('select.option',	E_ERROR | E_WARNING | E_PARSE,	JText::_('Config Error Simple')),
			JHtml::_('select.option',	E_ALL,							JText::_('Config Error Maximum')),
			JHtml::_('select.option',	E_ALL | E_STRICT,				JText::_('Config Error Strict'))
		);
		$options	= array_merge(
						parent::_getOptions(),
						$options
					);

		return $options;
	}
}