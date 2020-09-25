<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Menu\MenuHelper;

JFormHelper::loadFieldClass('list');

/**
 * Administrator Menu Presets list field.
 *
 * @since  3.8.0
 */
class JFormFieldMenuPreset extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 *
	 * @since   3.8.0
	 */
	protected $type = 'MenuPreset';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  3.8.0
	 */
	protected function getOptions()
	{
		$options = array();
		$presets = MenuHelper::getPresets();

		foreach ($presets as $preset)
		{
			$options[] = JHtml::_('select.option', $preset->name, JText::_($preset->title));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
