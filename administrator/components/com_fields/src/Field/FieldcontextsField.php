<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\Form\Field\ListField;

/**
 * Fields Contexts
 *
 * @since  3.7.0
 */
class FieldcontextsField extends ListField
{
	/**
	 * Type of the field
	 *
	 * @var    string
	 */
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

		if ($component instanceof FieldsServiceInterface)
		{
			return $component->getContexts();
		}

		return [];
	}
}
