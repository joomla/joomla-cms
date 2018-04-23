<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Fields\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelperProviderInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

/**
 * Fields Contexts
 *
 * @since  3.7.0
 */
class FieldcontextsField extends \JFormFieldList
{
	public $type = 'Fieldcontexts';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		return $this->getOptions() ? parent::getInput() : '';
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		$parts = explode('.', $this->value);

		$component = Factory::getApplication()->bootComponent($parts[0]);

		if ($component instanceof ComponentHelperProviderInterface)
		{
			return $component->getHelper()->getContexts();
		}

		return [];
	}
}
