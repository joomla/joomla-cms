<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Helper\UserGroupsHelper;

FormHelper::loadFieldClass('list');

/**
 * Field to load a dropdown list of available user groups
 *
 * @since  3.2
 */
class UsergrouplistField extends \JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.2
	 */
	protected $type = 'UserGroupList';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected static $options = array();

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		if (is_string($value) && strpos($value, ',') !== false)
		{
			$value = explode(',', $value);
		}

		return parent::setup($element, $value, $group);
	}

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.2
	 */
	protected function getOptions()
	{
		$options        = parent::getOptions();
		$checkSuperUser = (int) $this->getAttribute('checksuperusergroup', 0);

		// Cache user groups base on checksuperusergroup attribute value
		if (!isset(static::$options[$checkSuperUser]))
		{
			$groups       = UserGroupsHelper::getInstance()->getAll();
			$isSuperUser  = Factory::getUser()->authorise('core.admin');
			$cacheOptions = array();

			foreach ($groups as $group)
			{
				// Don't show super user groups to non super users.
				if ($checkSuperUser && !$isSuperUser && Access::checkGroup($group->id, 'core.admin'))
				{
					continue;
				}

				$cacheOptions[] = (object) array(
					'text'  => str_repeat('- ', $group->level) . $group->title,
					'value' => $group->id,
					'level' => $group->level,
				);
			}

			static::$options[$checkSuperUser] = $cacheOptions;
		}

		return array_merge($options, static::$options[$checkSuperUser]);
	}
}
