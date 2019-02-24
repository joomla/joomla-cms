<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;

FormHelper::loadFieldClass('list');

/**
 * Modules Module field.
 *
 * @since  3.4.2
 */
class ModulesModuleField extends \JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.4.2
	 */
	protected $type = 'ModulesModule';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.4.2
	 */
	public function getOptions()
	{
		$clientId = Factory::getApplication()->getUserState('com_modules.modules.client_id', 0);
		$options  = ModulesHelper::getModules($clientId);

		return array_merge(parent::getOptions(), $options);
	}
}
