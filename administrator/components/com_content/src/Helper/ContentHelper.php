<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Category;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Database\ParameterType;
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
	 * @param   int  $id  Id of state to delete
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public static function canDeleteState(int $id): bool
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->quoteName('#__content'))
			->where($db->quoteName('state') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);
		$db->setQuery($query);
		$states = $db->loadResult();

		return empty($states);
	}

	/**
	 * Method to filter transitions by given id of state
	 *
	 * @param   array  $transitions  Array of transitions
	 * @param   int    $pk           Id of state
	 * @param   int    $workflowId   Id of the workflow
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function filterTransitions(array $transitions, int $pk, int $workflowId = 0): array
	{
		return array_values(
			array_filter(
				$transitions,
				function ($var) use ($pk, $workflowId)
				{
					return in_array($var['from_stage_id'], [-1, $pk]) && $workflowId == $var['workflow_id'];
				}
			)
		);
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

		// Make workflows translatable
		Factory::getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

		$form->setFieldAttribute('workflow_id', 'default', 'inherit');

		$component = Factory::getApplication()->bootComponent('com_content');

		if (!$component instanceof WorkflowServiceInterface
			|| !$component->isWorkflowActive('com_content.article'))
		{
			$form->removeField('workflow_id', 'params');

			return;
		}

		$query = $db->getQuery(true);

		$query->select($db->quoteName('title'))
			->from($db->quoteName('#__workflows'))
			->where(
				[
					$db->quoteName('default') . ' = 1',
					$db->quoteName('published') . ' = 1',
					$db->quoteName('extension') . ' = ' . $db->quote('com_content.article'),
				]
			);

		$defaulttitle = $db->setQuery($query)->loadResult();

		$option = Text::_('COM_WORKFLOW_INHERIT_WORKFLOW_NEW');

		if (!empty($data['id']))
		{
			$category = new Category($db);

			$categories = $category->getPath((int) $data['id']);

			// Remove the current category, because we search for inheritance from parent.
			array_pop($categories);

			$option = Text::sprintf('COM_WORKFLOW_INHERIT_WORKFLOW', Text::_($defaulttitle));

			if (!empty($categories))
			{
				$categories = array_reverse($categories);

				$query = $db->getQuery(true);

				$query->select($db->quoteName('title'))
					->from($db->quoteName('#__workflows'))
					->where(
						[
							$db->quoteName('id') . ' = :workflowId',
							$db->quoteName('published') . ' = 1',
							$db->quoteName('extension') . ' = ' . $db->quote('com_content.article'),
						]
					)
					->bind(':workflowId', $workflow_id, ParameterType::INTEGER);

				$db->setQuery($query);

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
					elseif ($workflow_id = (int) $workflow_id)
					{
						$title = $db->loadResult();

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
