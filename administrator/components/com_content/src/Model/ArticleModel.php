<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Category;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\Workflow\Administrator\Table\StageTable;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Item Model for an Article.
 *
 * @since  1.6
 */

class ArticleModel extends AdminModel
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_CONTENT';

	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_content.article';

	/**
	 * The context used for the associations table
	 *
	 * @var    string
	 * @since  3.4.4
	 */
	protected $associationsContext = 'com_content.item';

	/**
	 * Function that can be overriden to do any data cleanup after batch copying data
	 *
	 * @param   TableInterface  $table  The table object containing the newly created item
	 * @param   integer         $newId  The id of the new item
	 * @param   integer         $oldId  The original item id
	 *
	 * @return  void
	 *
	 * @since  3.8.12
	 */
	protected function cleanupPostBatchCopy(TableInterface $table, $newId, $oldId)
	{
		// Check if the article was featured and update the #__content_frontpage table
		if ($table->featured == 1)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->select(
					[
						$db->quoteName('featured_up'),
						$db->quoteName('featured_down'),
					]
				)
				->from($db->quoteName('#__content_frontpage'))
				->where($db->quoteName('content_id') . ' = :oldId')
				->bind(':oldId', $oldId, ParameterType::INTEGER);

			$featured = $db->setQuery($query)->loadObject();

			if ($featured)
			{
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__content_frontpage'))
					->values(':newId, 0, :featuredUp, :featuredDown')
					->bind(':newId', $newId, ParameterType::INTEGER)
					->bind(':featuredUp', $featured->featured_up, $featured->featured_up ? ParameterType::STRING : ParameterType::NULL)
					->bind(':featuredDown', $featured->featured_down, $featured->featured_down ? ParameterType::STRING : ParameterType::NULL);

					$db->setQuery($query);
					$db->execute();
			}
		}

		// Copy workflow association
		$workflow = new Workflow(['extension' => 'com_content']);

		$assoc = $workflow->getAssociation((int) $oldId);

		$workflow->createAssociation((int) $newId, (int) $assoc->stage_id);

		// Register FieldsHelper
		\JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

		$oldItem = $this->getTable();
		$oldItem->load($oldId);
		$fields = FieldsHelper::getFields('com_content.article', $oldItem, true);

		$fieldsData = array();

		if (!empty($fields))
		{
			$fieldsData['com_fields'] = array();

			foreach ($fields as $field)
			{
				$fieldsData['com_fields'][$field->name] = $field->rawvalue;
			}
		}

		Factory::getApplication()->triggerEvent('onContentAfterSave', array('com_content.article', &$this->table, false, $fieldsData));
	}

	/**
	 * Batch change workflow stage or current.
	 *
	 * @param   integer  $value     The workflow stage ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since   4.0.0
	 */
	protected function batchWorkflowStage(int $value, array $pks, array $contexts)
	{
		$user = Factory::getUser();

		if (!$user->authorise('core.admin', 'com_content'))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EXECUTE_TRANSITION'));
		}

		// Get workflow stage information
		$stage = new StageTable($this->_db);

		if (empty($value) || !$stage->load($value))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_BATCH_WORKFLOW_STAGE_ROW_NOT_FOUND'), 'error');

			return false;
		}

		if (empty($pks))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_BATCH_WORKFLOW_STAGE_ROW_NOT_FOUND'), 'error');

			return false;
		}

		$workflow = new Workflow(['extension' => 'com_content']);

		// Update content state value and workflow associations
		return ContentHelper::updateContentState($pks, (int) $stage->condition)
				&& $workflow->updateAssociations($pks, $value);
	}

	/**
	 * Batch move categories to a new category.
	 *
	 * @param   integer  $value     The new category ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.8.6
	 */
	protected function batchMove($value, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user = Factory::getUser();
			$this->table = $this->getTable();
			$this->tableClassName = get_class($this->table);
			$this->contentType = new UCMType;
			$this->type = $this->contentType->getTypeByTable($this->tableClassName);
		}

		$categoryId = (int) $value;

		if (!$this->checkCategoryId($categoryId))
		{
			return false;
		}

		PluginHelper::importPlugin('system');

		// Parent exists so we proceed
		foreach ($pks as $pk)
		{
			if (!$this->user->authorise('core.edit', $contexts[$pk]))
			{
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			$fields = FieldsHelper::getFields('com_content.article', $this->table, true);

			$fieldsData = array();

			if (!empty($fields))
			{
				$fieldsData['com_fields'] = array();

				foreach ($fields as $field)
				{
					$fieldsData['com_fields'][$field->name] = $field->rawvalue;
				}
			}

			// Set the new category ID
			$this->table->catid = $categoryId;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Run event for moved article
			Factory::getApplication()->triggerEvent('onContentAfterSave', array('com_content.article', &$this->table, false, $fieldsData));
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			$stage = new StageTable($this->getDbo());

			$workflow = new Workflow(['extension' => 'com_content']);

			$assoc = $workflow->getAssociation((int) $record->id);

			if (!$stage->load($assoc->stage_id) || ($stage->condition != ContentComponent::CONDITION_TRASHED && !Factory::getApplication()->isClient('api')))
			{
				return false;
			}

			return Factory::getUser()->authorise('core.delete', 'com_content.article.' . (int) $record->id);
		}

		return false;
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = Factory::getUser();

		// Check for existing article.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_content.article.' . (int) $record->id);
		}

		// New article, so check against the category.
		if (!empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_content.category.' . (int) $record->catid);
		}

		// Default to component settings if neither article nor category known.
		return parent::canEditState($record);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   \Joomla\CMS\Table\Table  $table  A Table object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		// Set the publish date to now
		if ($table->state == Workflow::CONDITION_PUBLISHED && (int) $table->publish_up == 0)
		{
			$table->publish_up = Factory::getDate()->toSql();
		}

		if ($table->state == Workflow::CONDITION_PUBLISHED && intval($table->publish_down) == 0)
		{
			$table->publish_down = null;
		}

		// Increment the content version number.
		$table->version++;

		// Reorder the articles within the category so the new article is first
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		}
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0.0
	 */
	public function publish(&$pks, $value = 1)
	{
		$input = Factory::getApplication()->input;

		$user  = Factory::getUser();
		$table = $this->getTable();
		$pks   = (array) $pks;
		$value = (int) $value;

		$itrans = $input->get('publish_transitions', [], 'array');

		// Include the plugins for the change of state event.
		PluginHelper::importPlugin($this->events_map['change_state']);

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('wt.id'),
				$db->quoteName('wa.item_id'),
			]
		)
			->from(
				[
					$db->quoteName('#__workflow_transitions', 'wt'),
					$db->quoteName('#__workflow_stages', 'ws'),
					$db->quoteName('#__workflow_stages', 'ws2'),
					$db->quoteName('#__workflow_associations', 'wa'),
				]
			)
			->where(
				[
					$db->quoteName('wt.to_stage_id') . ' = ' . $db->quoteName('ws.id'),
					$db->quoteName('wa.stage_id') . ' = ' . $db->quoteName('ws2.id'),
					$db->quoteName('wt.workflow_id') . ' = ' . $db->quoteName('ws.workflow_id'),
					$db->quoteName('wt.workflow_id') . ' = ' . $db->quoteName('ws2.workflow_id'),
					$db->quoteName('wt.to_stage_id') . ' != ' . $db->quoteName('wa.stage_id'),
					$db->quoteName('wa.extension') . ' = ' . $db->quote('com_content'),
					$db->quoteName('ws.condition') . ' = :condition',
				]
			)
			->extendWhere(
				'AND',
				[
					$db->quoteName('wt.from_stage_id') . ' = -1',
					$db->quoteName('wt.from_stage_id') . ' = ' . $db->quoteName('wa.stage_id'),
				],
				'OR'
			)
			->whereIn($db->quoteName('wa.item_id'), $pks)
			->bind(':condition', $value, ParameterType::INTEGER);

		$transitions = $db->setQuery($query)->loadObjectList();

		$items = [];

		foreach ($transitions as $transition)
		{
			if ($user->authorise('core.execute.transition', 'com_content.transition.' . $transition->id))
			{
				if (!isset($itrans[$transition->item_id]) || $itrans[$transition->item_id] == $transition->id)
				{
					$items[$transition->item_id] = (int) $transition->id;
				}
			}
		}

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!isset($items[$pk]))
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING, 'jerror');

					return false;
				}

				// If the table is checked out by another user, drop it and report to the user trying to change its state.
				if ($table->hasField('checked_out') && $table->checked_out && ($table->checked_out != $user->id))
				{
					Log::add(Text::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'), Log::WARNING, 'jerror');

					// Prune items that you can't change.
					unset($pks[$i]);

					return false;
				}
			}
		}

		foreach ($pks as $i => $pk)
		{
			if (!$this->runTransition($pk, $items[$pk]))
			{
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the params field to an array.
			$registry = new Registry($item->attribs);
			$item->attribs = $registry->toArray();

			// Convert the metadata field to an array.
			$registry = new Registry($item->metadata);
			$item->metadata = $registry->toArray();

			// Convert the images field to an array.
			$registry = new Registry($item->images);
			$item->images = $registry->toArray();

			// Convert the urls field to an array.
			$registry = new Registry($item->urls);
			$item->urls = $registry->toArray();

			$item->articletext = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\">" . $item->fulltext : $item->introtext;

			if (!empty($item->id))
			{
				$item->tags = new TagsHelper;
				$item->tags->getTagIds($item->id, 'com_content.article');

				$item->featured_up   = null;
				$item->featured_down = null;

				if ($item->featured)
				{
					// Get featured dates.
					$db = $this->getDbo();
					$query = $db->getQuery(true)
						->select(
							[
								$db->quoteName('featured_up'),
								$db->quoteName('featured_down'),
							]
						)
						->from($db->quoteName('#__content_frontpage'))
						->where($db->quoteName('content_id') . ' = :id')
						->bind(':id', $item->id, ParameterType::INTEGER);

					$featured = $db->setQuery($query)->loadObject();

					if ($featured)
					{
						$item->featured_up   = $featured->featured_up;
						$item->featured_down = $featured->featured_down;
					}
				}
			}
		}

		// Load associated content items
		$assoc = Associations::isEnabled();

		if ($assoc)
		{
			$item->associations = array();

			if ($item->id != null)
			{
				$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $item->id);

				foreach ($associations as $tag => $association)
				{
					$item->associations[$tag] = $association->id;
				}
			}
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app  = Factory::getApplication();
		$user = $app->getIdentity();

		// Get the form.
		$form = $this->loadForm('com_content.article', 'article', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$jinput = $app->input;

		/*
		 * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		 * The back end uses id so we use that the rest of the time and set it to 0 by default.
		 */
		$id = $jinput->get('a_id', $jinput->get('id', 0));

		// Determine correct permissions to check.
		if ($id = $this->getState('article.id', $id))
		{
			if ($app->isClient('site'))
			// Existing record. We can't edit the category in frontend if not edit.state.
			{
				if ($id != 0 && (!$user->authorise('core.edit.state', 'com_content.article.' . (int) $id))
					|| ($id == 0 && !$user->authorise('core.edit.state', 'com_content')))
				{
					$form->setFieldAttribute('catid', 'readonly', 'true');
					$form->setFieldAttribute('catid', 'filter', 'unset');
				}
			}

			$table = $this->getTable();

			if ($table->load(array('id' => $id)))
			{
				$workflow = new Workflow(['extension' => 'com_content']);

				// Transition field
				$assoc = $workflow->getAssociation($table->id);

				$form->setFieldAttribute('transition', 'workflow_stage', (int) $assoc->stage_id);
			}
		}
		else
		{
			// For new articles we load the potential state + associations
			if ($formField = $form->getField('catid'))
			{
				$assignedCatids = (int) ($data['catid'] ?? $form->getValue('catid'));

				$assignedCatids = is_array($assignedCatids)
					? (int) reset($assignedCatids)
					: (int) $assignedCatids;

				// Try to get the category from the html code of the field
				if (empty($assignedCatids))
				{
					$assignedCatids = $formField->getAttribute('default', null);

					// Choose the first category available
					$xml = new \DOMDocument;
					libxml_use_internal_errors(true);
					$xml->loadHTML($formField->__get('input'));
					libxml_clear_errors();
					libxml_use_internal_errors(false);
					$options = $xml->getElementsByTagName('option');

					if (!$assignedCatids && $firstChoice = $options->item(0))
					{
						$assignedCatids = $firstChoice->getAttribute('value');
					}
				}

				// Activate the reload of the form when category is changed
				$form->setFieldAttribute('catid', 'refresh-enabled', true);
				$form->setFieldAttribute('catid', 'refresh-cat-id', $assignedCatids);
				$form->setFieldAttribute('catid', 'refresh-section', 'article');

				$workflow = $this->getWorkflowByCategory($assignedCatids);

				$form->setFieldAttribute('transition', 'workflow_stage', (int) $workflow->stage_id);
			}
		}

		// Check for existing article.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_content.article.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_content')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('featured_up', 'disabled', 'true');
			$form->setFieldAttribute('featured_down', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('featured_up', 'filter', 'unset');
			$form->setFieldAttribute('featured_down', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		// Prevent messing with article language and category when editing existing article with associations
		$assoc = Associations::isEnabled();

		// Check if article is associated
		if ($this->getState('article.id') && $app->isClient('site') && $assoc)
		{
			$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $id);

			// Make fields read only
			if (!empty($associations))
			{
				$form->setFieldAttribute('language', 'readonly', 'true');
				$form->setFieldAttribute('catid', 'readonly', 'true');
				$form->setFieldAttribute('language', 'filter', 'unset');
				$form->setFieldAttribute('catid', 'filter', 'unset');
			}
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = Factory::getApplication();
		$data = $app->getUserState('com_content.edit.article.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
			if ($this->getState('article.id') == 0)
			{
				$filters = (array) $app->getUserState('com_content.articles.filter');
				$data->set(
					'state',
					$app->input->getInt(
						'state',
						((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
					)
				);
				$data->set('catid', $app->input->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null)));
				$data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
				$data->set('access',
					$app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : $app->get('access')))
				);
			}
		}

		// If there are params fieldsets in the form it will fail with a registry object
		if (isset($data->params) && $data->params instanceof Registry)
		{
			$data->params = $data->params->toArray();
		}

		$this->preprocessData('com_content.article', $data);

		return $data;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     \Joomla\CMS\Form\FormRule
	 * @see     JFilterInput
	 * @since   3.7.0
	 */
	public function validate($form, $data, $group = null)
	{
		// Don't allow to change the users if not allowed to access com_users.
		if (Factory::getApplication()->isClient('administrator') && !Factory::getUser()->authorise('core.manage', 'com_users'))
		{
			if (isset($data['created_by']))
			{
				unset($data['created_by']);
			}

			if (isset($data['modified_by']))
			{
				unset($data['modified_by']);
			}
		}

		return parent::validate($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$input  = Factory::getApplication()->input;
		$filter = \JFilterInput::getInstance();
		$db     = $this->getDbo();
		$user	= Factory::getUser();

		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
		}

		if (isset($data['created_by_alias']))
		{
			$data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
		}

		if (isset($data['images']) && is_array($data['images']))
		{
			$registry = new Registry($data['images']);

			$data['images'] = (string) $registry;
		}

		// Create new category, if needed.
		$createCategory = true;

		// If category ID is provided, check if it's valid.
		if (is_numeric($data['catid']) && $data['catid'])
		{
			$createCategory = !CategoriesHelper::validateCategoryId($data['catid'], 'com_content');
		}

		// Save New Category
		if ($createCategory && $this->canCreateCategory())
		{
			$category = [
				// Remove #new# prefix, if exists.
				'title'     => strpos($data['catid'], '#new#') === 0 ? substr($data['catid'], 5) : $data['catid'],
				'parent_id' => 1,
				'extension' => 'com_content',
				'language'  => $data['language'],
				'published' => 1,
			];

			/** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $categoryModel */
			$categoryModel = Factory::getApplication()->bootComponent('com_categories')
				->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);

			// Create new category.
			if (!$categoryModel->save($category))
			{
				$this->setError($categoryModel->getError());

				return false;
			}

			// Get the Category ID.
			$data['catid'] = $categoryModel->getState('category.id');
		}

		if (isset($data['urls']) && is_array($data['urls']))
		{
			$check = $input->post->get('jform', array(), 'array');

			foreach ($data['urls'] as $i => $url)
			{
				if ($url != false && ($i == 'urla' || $i == 'urlb' || $i == 'urlc'))
				{
					if (preg_match('~^#[a-zA-Z]{1}[a-zA-Z0-9-_:.]*$~', $check['urls'][$i]) == 1)
					{
						$data['urls'][$i] = $check['urls'][$i];
					}
					else
					{
						$data['urls'][$i] = PunycodeHelper::urlToPunycode($url);
					}
				}
			}

			unset($check);

			$registry = new Registry($data['urls']);

			$data['urls'] = (string) $registry;
		}

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['title'] == $origTable->title)
			{
				list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
				$data['title'] = $title;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}
		}

		$stageId = 0;

		// Set status depending on category
		if (empty($data['id']))
		{
			$workflow = $this->getWorkflowByCategory($data['catid']);

			if (empty($workflow->id))
			{
				$this->setError(Text::_('COM_CONTENT_WORKFLOW_NOT_FOUND'));

				return false;
			}

			$stageId = (int) $workflow->stage_id;

			// B/C state
			$data['state'] = (int) $workflow->condition;
		}

		// Calculate new status depending on transition
		if (!empty($data['transition']))
		{
			// Check if the user is allowed to execute this transition
			if (!$user->authorise('core.execute.transition', 'com_content.transition.' . (int) $data['transition']))
			{
				$this->setError(Text::_('COM_CONTENT_WORKFLOW_TRANSITION_NOT_ALLOWED'));

				return false;
			}

			// Set the new state
			$query = $db->getQuery(true);
			$transition = (int) $data['transition'];

			$query->select($db->quoteName(['ws.id', 'ws.condition']))
				->from(
					[
						$db->quoteName('#__workflow_stages', 'ws'),
						$db->quoteName('#__workflow_transitions', 'wt'),
					]
				)
				->where(
					[
						$db->quoteName('wt.to_stage_id') . ' = ' . $db->quoteName('ws.id'),
						$db->quoteName('wt.id') . ' = :transition',
						$db->quoteName('ws.published') . ' = 1',
						$db->quoteName('wt.published') . ' = 1',
					]
				)
				->bind(':transition', $transition, ParameterType::INTEGER);

			$stage = $db->setQuery($query)->loadObject();

			if (empty($stage->id))
			{
				$this->setError(Text::_('COM_CONTENT_WORKFLOW_TRANSITION_NOT_ALLOWED'));

				return false;
			}

			$data['state'] = (int) $stage->condition;
		}

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0))
		{
			if ($data['alias'] == null)
			{
				if (Factory::getApplication()->get('unicodeslugs') == 1)
				{
					$data['alias'] = \JFilterOutput::stringURLUnicodeSlug($data['title']);
				}
				else
				{
					$data['alias'] = \JFilterOutput::stringURLSafe($data['title']);
				}

				$table = Table::getInstance('Content', 'JTable');

				if ($table->load(array('alias' => $data['alias'], 'catid' => $data['catid'])))
				{
					$msg = Text::_('COM_CONTENT_SAVE_WARNING');
				}

				list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
				$data['alias'] = $alias;

				if (isset($msg))
				{
					Factory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

		$workflow = new Workflow(['extension' => 'com_content']);

		if (parent::save($data))
		{
			if (isset($data['featured']))
			{
				if (!$this->featured(
					$this->getState($this->getName() . '.id'),
					$data['featured'],
					$data['featured_up'] ?? null,
					$data['featured_down'] ?? null
				))
				{
					return false;
				}
			}

			// Let's check if we have workflow association (perhaps something went wrong before)
			if (empty($stageId))
			{
				$assoc = $workflow->getAssociation((int) $this->getState($this->getName() . '.id'));

				// If not, reset the state and let's create the associations
				if (empty($assoc->item_id))
				{
					$table = $this->getTable();

					$table->load((int) $this->getState($this->getName() . '.id'));

					$workflow = $this->getWorkflowByCategory((int) $table->catid);

					if (empty($workflow->id))
					{
						$this->setError(Text::_('COM_CONTENT_WORKFLOW_NOT_FOUND'));

						return false;
					}

					$stageId = (int) $workflow->stage_id;

					// B/C state
					$table->state = $workflow->condition;

					$table->store();
				}
			}

			// If we have a new state, create the workflow association
			if (!empty($stageId))
			{
				$workflow->createAssociation((int) $this->getState($this->getName() . '.id'), (int) $stageId);
			}

			// Run the transition and update the workflow association
			if (!empty($data['transition']))
			{
				$this->runTransition((int) $this->getState($this->getName() . '.id'), (int) $data['transition']);
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to toggle the featured setting of articles.
	 *
	 * @param   array        $pks           The ids of the items to toggle.
	 * @param   integer      $value         The value to toggle to.
	 * @param   string|Date  $featuredUp    The date which item featured up.
	 * @param   string|Date  $featuredDown  The date which item featured down.
	 *
	 * @return  boolean  True on success.
	 */
	public function featured($pks, $value = 0, $featuredUp = null, $featuredDown = null)
	{
		// Sanitize the ids.
		$pks   = (array) $pks;
		$pks   = ArrayHelper::toInteger($pks);
		$value = (int) $value;

		// Convert empty strings to null for the query.
		if ($featuredUp === '')
		{
			$featuredUp = null;
		}

		if ($featuredDown === '')
		{
			$featuredDown = null;
		}

		if (empty($pks))
		{
			$this->setError(Text::_('COM_CONTENT_NO_ITEM_SELECTED'));

			return false;
		}

		$table = $this->getTable('Featured', 'Administrator');

		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__content'))
				->set($db->quoteName('featured') . ' = :featured')
				->whereIn($db->quoteName('id'), $pks)
				->bind(':featured', $value, ParameterType::INTEGER);
			$db->setQuery($query);
			$db->execute();

			if ($value === 0)
			{
				// Adjust the mapping table.
				// Clear the existing features settings.
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__content_frontpage'))
					->whereIn($db->quoteName('content_id'), $pks);
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// First, we find out which of our new featured articles are already featured.
				$query = $db->getQuery(true)
					->select($db->quoteName('content_id'))
					->from($db->quoteName('#__content_frontpage'))
					->whereIn($db->quoteName('content_id'), $pks);
				$db->setQuery($query);

				$oldFeatured = $db->loadColumn();

				// Update old featured articles
				if (count($oldFeatured))
				{
					$query = $db->getQuery(true)
						->update($db->quoteName('#__content_frontpage'))
						->set(
							[
								$db->quoteName('featured_up') . ' = :featuredUp',
								$db->quoteName('featured_down') . ' = :featuredDown',
							]
						)
						->whereIn($db->quoteName('content_id'), $oldFeatured)
						->bind(':featuredUp', $featuredUp, $featuredUp ? ParameterType::STRING : ParameterType::NULL)
						->bind(':featuredDown', $featuredDown, $featuredDown ? ParameterType::STRING : ParameterType::NULL);
					$db->setQuery($query);
					$db->execute();
				}

				// We diff the arrays to get a list of the articles that are newly featured
				$newFeatured = array_diff($pks, $oldFeatured);

				// Featuring.
				if ($newFeatured)
				{
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__content_frontpage'))
						->columns(
							[
								$db->quoteName('content_id'),
								$db->quoteName('ordering'),
								$db->quoteName('featured_up'),
								$db->quoteName('featured_down'),
							]
						);

					$dataTypes = [
						ParameterType::INTEGER,
						ParameterType::INTEGER,
						$featuredUp ? ParameterType::STRING : ParameterType::NULL,
						$featuredDown ? ParameterType::STRING : ParameterType::NULL,
					];

					foreach ($newFeatured as $pk)
					{
						$query->values(implode(',', $query->bindArray([$pk, 0, $featuredUp, $featuredDown], $dataTypes)));
					}

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$table->reorder();

		$this->cleanCache();

		return true;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   1.6
	 */
	protected function getReorderConditions($table)
	{
		return [
			$this->_db->quoteName('catid') . ' = ' . (int) $table->catid,
		];
	}

	/**
	 * Allows preprocessing of the Form object.
	 *
	 * @param   Form    $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function preprocessForm(Form $form, $data, $group = 'content')
	{
		if ($this->canCreateCategory())
		{
			$form->setFieldAttribute('catid', 'allowAdd', 'true');

			// Add a prefix for categories created on the fly.
			$form->setFieldAttribute('catid', 'customPrefix', '#new#');
		}

		// Association content items
		if (Associations::isEnabled())
		{
			$languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');

			if (count($languages) > 1)
			{
				$addform = new \SimpleXMLElement('<form />');
				$fields = $addform->addChild('fields');
				$fields->addAttribute('name', 'associations');
				$fieldset = $fields->addChild('fieldset');
				$fieldset->addAttribute('name', 'item_associations');

				foreach ($languages as $language)
				{
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $language->lang_code);
					$field->addAttribute('type', 'modal_article');
					$field->addAttribute('language', $language->lang_code);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');
					$field->addAttribute('select', 'true');
					$field->addAttribute('new', 'true');
					$field->addAttribute('edit', 'true');
					$field->addAttribute('clear', 'true');
					$field->addAttribute('propagate', 'true');
				}

				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Custom clean the cache of com_content and content modules
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_content');
		parent::cleanCache('mod_articles_archive');
		parent::cleanCache('mod_articles_categories');
		parent::cleanCache('mod_articles_category');
		parent::cleanCache('mod_articles_latest');
		parent::cleanCache('mod_articles_news');
		parent::cleanCache('mod_articles_popular');
	}

	/**
	 * Void hit function for pagebreak when editing content from frontend
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function hit()
	{
		return;
	}

	/**
	 * Is the user allowed to create an on the fly category?
	 *
	 * @return  boolean
	 *
	 * @since   3.6.1
	 */
	private function canCreateCategory()
	{
		return Factory::getUser()->authorise('core.create', 'com_content');
	}

	/**
	 * Delete #__content_frontpage items if the deleted articles was featured
	 *
	 * @param   object  $pks  The primary key related to the contents that was deleted.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public function delete(&$pks)
	{
		$return = parent::delete($pks);

		if ($return)
		{
			// Now check to see if this articles was featured if so delete it from the #__content_frontpage table
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__content_frontpage'))
				->whereIn($db->quoteName('content_id'), $pks);
			$db->setQuery($query);
			$db->execute();

			$workflow = new Workflow(['extension' => 'com_content']);

			$workflow->deleteAssociation($pks);
		}

		return $return;
	}

	/**
	 * Load the assigned workflow information by a given category ID
	 *
	 * @param   integer  $catId  The given category
	 *
	 * @return  integer|boolean  If found, the workflow ID, otherwise false
	 */
	protected function getWorkflowByCategory(int $catId)
	{
		$db = $this->getDbo();

		// Search categories and parents (if requested) for a workflow
		$category = new Category($db);

		$categories = array_reverse($category->getPath($catId));

		$workflow_id = 0;

		foreach ($categories as $cat)
		{
			$cat->params = new Registry($cat->params);

			$workflow_id = $cat->params->get('workflow_id');

			if ($workflow_id == 'inherit')
			{
				$workflow_id = 0;

				continue;
			}
			elseif ($workflow_id == 'use_default')
			{
				$workflow_id = 0;

				break;
			}
			elseif ($workflow_id > 0)
			{
				break;
			}
		}

		// Check if the workflow exists
		if ($workflow_id = (int) $workflow_id)
		{
			$query = $db->getQuery(true);

			$query->select(
				[
					$db->quoteName('w.id'),
					$db->quoteName('ws.condition'),
					$db->quoteName('ws.id', 'stage_id'),
				]
			)
				->from(
					[
						$db->quoteName('#__workflow_stages', 'ws'),
						$db->quoteName('#__workflows', 'w'),
					]
				)
				->where(
					[
						$db->quoteName('ws.workflow_id') . ' = ' . $db->quoteName('w.id'),
						$db->quoteName('ws.default') . ' = 1',
						$db->quoteName('w.published') . ' = 1',
						$db->quoteName('ws.published') . ' = 1',
						$db->quoteName('w.id') . ' = :workflowId',
					]
				)
				->bind(':workflowId', $workflow_id, ParameterType::INTEGER);

			$workflow = $db->setQuery($query)->loadObject();

			if (!empty($workflow->id))
			{
				return $workflow;
			}
		}

		// Use default workflow
		$query  = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('w.id'),
				$db->quoteName('ws.condition'),
				$db->quoteName('ws.id', 'stage_id'),
			]
		)
			->from(
				[
					$db->quoteName('#__workflow_stages', 'ws'),
					$db->quoteName('#__workflows', 'w'),
				]
			)
			->where(
				[
					$db->quoteName('ws.default') . ' = 1',
					$db->quoteName('ws.workflow_id') . ' = ' . $db->quoteName('w.id'),
					$db->quoteName('w.published') . ' = 1',
					$db->quoteName('ws.published') . ' = 1',
					$db->quoteName('w.default') . ' = 1',
				]
			);

		$workflow = $db->setQuery($query)->loadObject();

		// Last check if we have a workflow ID
		if (!empty($workflow->id))
		{
			return $workflow;
		}

		return false;
	}

	/**
	 * Runs transition for item.
	 *
	 * @param   integer  $pk             Id of article
	 * @param   integer  $transition_id  Id of transition
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function runTransition(int $pk, int $transition_id): bool
	{
		$workflow = new Workflow(['extension' => 'com_content']);

		$runTransaction = $workflow->executeTransition([$pk], $transition_id);

		if (!$runTransaction)
		{
			$this->setError(Text::_('COM_CONTENT_ERROR_UPDATE_STAGE'));

			return false;
		}

		// B/C state change trigger for UCM
		$context = $this->option . '.' . $this->name;

		// Include the plugins for the change of stage event.
		PluginHelper::importPlugin($this->events_map['change_state']);

		// Trigger the change stage event.
		Factory::getApplication()->triggerEvent($this->event_change_state, [$context, [$pk], $workflow->getConditionForTransition($transition_id)]);

		return true;
	}
}
