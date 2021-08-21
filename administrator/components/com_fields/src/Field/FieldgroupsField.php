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
use Joomla\CMS\Form\Field\ListField;
use Joomla\Utilities\ArrayHelper;


/**
 * Fields Groups
 *
 * @since  3.7.0
 */
class FieldgroupsField extends ListField
{
	/**
	 * @var    string
	 */
	public $type = 'Fieldgroups';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		$context = (string) $this->element['context'];
		$states    = $this->element['state'] ?: '0,1';
		$states    = ArrayHelper::toInteger(explode(',', $states));

		$user       = Factory::getUser();
		$viewlevels = ArrayHelper::toInteger($user->getAuthorisedViewLevels());

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			[
				$db->quoteName('title', 'text'),
				$db->quoteName('id', 'value'),
				$db->quoteName('state'),
			]
		);
		$query->from($db->quoteName('#__fields_groups'));
		$query->whereIn($db->quoteName('state'), $states);
		$query->where($db->quoteName('context') . ' = :context');
		$query->whereIn($db->quoteName('access'), $viewlevels);
		$query->order('ordering asc, id asc');
		$query->bind(':context', $context);

		$db->setQuery($query);
		$options = $db->loadObjectList();

		foreach ($options AS $option)
		{
			if ($option->state == 0)
			{
				$option->text = '[' . $option->text . ']';
			}

			if ($option->state == 2)
			{
				$option->text = '{' . $option->text . '}';
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
