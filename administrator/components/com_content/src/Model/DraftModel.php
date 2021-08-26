<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

\defined('_JEXEC') or die;

use \Datetime;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\WorkflowBehaviorTrait;
use Joomla\CMS\MVC\Model\WorkflowModelInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Item Model for a Draft.
 *
 * @since  1.6
 */

class DraftModel extends AdminModel implements WorkflowModelInterface
{
	use WorkflowBehaviorTrait, VersionableModelTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_CONTENT';

	/**
	 * The type alias for this content type (for example, 'com_content.draft').
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_content.draft';

	/**
	 * The context used for the associations table
	 *
	 * @var    string
	 * @since  3.4.4
	 */
	protected $associationsContext = 'com_content.item';

	/**
	 * The event to trigger before changing featured status one or more items.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $event_before_change_featured = null;

	/**
	 * The event to trigger after changing featured status one or more items.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $event_after_change_featured = null;

	/**
	 * Constructor.
	 *
	 * @param   array                 $config       An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface   $factory      The factory.
	 * @param   FormFactoryInterface  $formFactory  The form factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		$config['events_map'] = $config['events_map'] ?? [];

		$config['events_map'] = array_merge(
			['featured' => 'content'],
			$config['events_map']
		);

		parent::__construct($config, $factory, $formFactory);
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

		// Get the form.
		$form = $this->loadForm('com_content.draft', 'draft', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// Object uses for checking edit state permission of draft
		$record = new \stdClass;

		// Get ID of the draft from input, for frontend, we use a_id while backend uses id
		$draftIdFromInput = (int) $app->input->getInt('a_id') ?: $app->input->getInt('id', 0);

		// On edit draft, we get ID of draft from draft.id state, but on save, we use data from input
		$id = (int) $this->getState('draft.id', $draftIdFromInput);

		$record->id = $id;

		// For new drafts we load the potential state + associations
		if ($id == 0 && $formField = $form->getField('catid'))
		{
			$assignedCatids = $data['catid'] ?? $form->getValue('catid');

			$assignedCatids = is_array($assignedCatids)
				? (int) reset($assignedCatids)
				: (int) $assignedCatids;

			// Try to get the category from the category field
			if (empty($assignedCatids))
			{
				$assignedCatids = $formField->getAttribute('default', null);

				if (!$assignedCatids)
				{
					// Choose the first category available
					$catOptions = $formField->options;

					if ($catOptions && !empty($catOptions[0]->value))
					{
						$assignedCatids = (int) $catOptions[0]->value;
					}
				}
			}

			// Activate the reload of the form when category is changed
			$form->setFieldAttribute('catid', 'refresh-enabled', true);
			$form->setFieldAttribute('catid', 'refresh-cat-id', $assignedCatids);
			$form->setFieldAttribute('catid', 'refresh-section', 'draft');

			// Store ID of the category uses for edit state permission check
			$record->catid = $assignedCatids;
		}
		else
		{
			// Get the category which the draft is being added to
			if (!empty($data['catid']))
			{
				$catId = (int) $data['catid'];
			}
			else
			{
				$catIds  = $form->getValue('catid');

				$catId = is_array($catIds)
					? (int) reset($catIds)
					: (int) $catIds;

				if (!$catId)
				{
					$catId = (int) $form->getFieldAttribute('catid', 'default', 0);
				}
			}

			$record->catid = $catId;
		}

		// Modify the form based on Edit State access controls.
		if (!$this->canEditState($record))
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
			// The controller has already verified this is an draft you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('featured_up', 'filter', 'unset');
			$form->setFieldAttribute('featured_down', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		// Don't allow to change the created_by user if not allowed to access com_users.
		if (!Factory::getUser()->authorise('core.manage', 'com_users'))
		{
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to unshare drafts.
	 *
	 * @param   array        $pks           The ids of the items to toggle.
	 * @param   integer      $value         The value to toggle to.
	 * @param   string|Date  $featuredUp    The date which item featured up.
	 * @param   string|Date  $featuredDown  The date which item featured down.
	 *
	 * @return  boolean  True on success.
	 */
	public function unshare($pks)
	{
		// Sanitize the ids.
		$pks = (array) $pks;

		if (empty($pks))
		{
			$this->setError(Text::_('COM_CONTENT_NO_ITEM_SELECTED'));

			return false;
		}

		try
		{
			$value = 0;

			// Adjust the mapping table.
			// Clear the existing features settings.
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__draft'))
				->set($db->quoteName('state') . ' = :state')
				->set($db->quoteName('shared_date') . ' = NULL')
				->whereIn($db->quoteName('hashval'), $pks, ParameterType::STRING)
				->bind(':state', $value, ParameterType::INTEGER);

			$db->setQuery($query);
			$db->execute();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	public function share($pks)
	{
		// Sanitize the ids.
		if (empty($pks))
		{
			$this->setError(Text::_('COM_CONTENT_NO_ITEM_SELECTED'));

			return false;
		}

		try
		{
			$value = 1;

			// Adjust the mapping table.
			// Clear the existing features settings.
			$now = new DateTime;
			$date_sql = $now->format('Y-m-d H:i:s');
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__draft'))
				->set($db->quoteName('state') . ' = :state')
				->set($db->quoteName('shared_date') . ' = :date')
				->whereIn($db->quoteName('hashval'), $pks, ParameterType::STRING)
				->bind(':state', $value, ParameterType::INTEGER)
				->bind(':date', $date_sql, ParameterType::STRING);

			$db->setQuery($query);
			$db->execute();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	public function delete($pks)
	{
		// Sanitize the ids.
		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__draft'))
				->where($db->quoteName('hashval') . ' = ' . ':hashval')
				->bind(':hashval', $pks, ParameterType::STRING);
			$db->setQuery($query);
			$db->execute();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}
}
