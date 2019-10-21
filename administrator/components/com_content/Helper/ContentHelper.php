<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Category;
use Joomla\Registry\Registry;

/**
 * Content component helper.
 *
 * @since  1.6
 */
class ContentHelper extends \Joomla\CMS\Helper\ContentHelper
{
	/**
	 * Check if state can be deleted
	 *
	 * @param   int  $stateID  Id of state to delete
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public static function canDeleteState($stateID)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->quoteName('#__content'))
			->where('state = ' . (int) $stateID);
		$db->setQuery($query);
		$states = $db->loadResult();

		return empty($states);
	}

	/**
	 * Method to filter transitions by given id of state
	 *
	 * @param   array  $transitions  Array of transitions
	 * @param   int    $pk           Id of state
	 * @param   int    $workflow_id  Id of the workflow
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function filterTransitions($transitions, $pk, $workflow_id = 0): array
	{
		return array_values(
			array_filter(
				$transitions,
				function ($var) use ($pk, $workflow_id)
				{
					return in_array($var['from_stage_id'], [-1, $pk]) && $var['to_stage_id'] != $pk && $workflow_id == $var['workflow_id'];
				}
			)
		);
	}

	/**
	 * Method to change state of multiple ids
	 *
	 * @param   array  $pks        Array of IDs
	 * @param   int    $condition  Condition of the workflow state
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public static function updateContentState($pks, $condition): bool
	{
		if (empty($pks))
		{
			return false;
		}

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__content'))
				->set($db->quoteName('state') . '=' . (int) $condition)
				->where($db->quoteName('id') . ' IN (' . implode(', ', $pks) . ')');

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Prepares a form
	 *
	 * @param   Form          $form  The form to change
	 * @param   array|object  $data  The form data
	 *
	 * @return void
	 */
	public static function onPrepareForm(Form $form, $data)
	{
		if ($form->getName() != 'com_categories.categorycom_content')
		{
			return;
		}

		$db = Factory::getDbo();

		$data = (array) $data;

		// Make workflows translateable
		Factory::getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

		$form->setFieldAttribute('workflow_id', 'default', 'inherit');

		$query = $db->getQuery(true);

		$query->select($db->quoteName('title'))
			->from($db->quoteName('#__workflows'))
			->where($db->quoteName('default') . ' = 1')
			->where($db->quoteName('published') . ' = 1');

		$defaulttitle = $db->setQuery($query)->loadResult();

		$option = Text::_('COM_WORKFLOW_INHERIT_WORKFLOW_NEW');

		if (!empty($data['id']))
		{
			$category = new Category($db);

			$categories = $category->getPath((int) $data['id']);

			// Remove the current category, because we search vor inherit from parent
			array_shift($categories);

			$option = Text::sprintf('COM_WORKFLOW_INHERIT_WORKFLOW', Text::_($defaulttitle));

			if (!empty($categories))
			{
				$categories = array_reverse($categories);

				foreach ($categories as $cat)
				{
					$cat->params = new Registry($cat->params);

					$workflow_id = $cat->params->get('workflow_id');

					if ($workflow_id == 'inherit')
					{
						continue;
					}
					elseif ($workflow_id == 'use_default')
					{
						break;
					}
					elseif ((int) $workflow_id > 0)
					{
						$query->clear('where')
							->where($db->quoteName('id') . ' = ' . (int) $workflow_id)
							->where($db->quoteName('published') . ' = 1');

						$title = $db->setQuery($query)->loadResult();

						if (!is_null($title))
						{
							$option = Text::sprintf('COM_WORKFLOW_INHERIT_WORKFLOW', Text::_($title));

							break;
						}
					}
				}
			}
		}

		$field = $form->getField('workflow_id', 'params');

		$field->addOption($option, ['value' => 'inherit']);

		$field->addOption(Text::sprintf('COM_WORKFLOW_USE_DEFAULT_WORKFLOW', Text::_($defaulttitle)), ['value' => 'use_default']);

		$field->addOption('- ' . Text::_('COM_CONTENT_WORKFLOWS') . ' -', ['disabled' => 'true']);
	}
}
