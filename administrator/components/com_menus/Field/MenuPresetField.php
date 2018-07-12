<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuHelper;

/**
 * Administrator Menu Presets list field.
 *
 * @since  3.8.0
 */
class MenuPresetField extends ListField
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
			$options[] = HTMLHelper::_('select.option', $preset->name, Text::_($preset->title));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
