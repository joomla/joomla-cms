<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Template style model.
 *
 * @since  1.6
 */
class StyleModel extends AdminModel
{
	/**
	 * The help screen key for the module.
	 *
	 * @var	    string
	 * @since   1.6
	 */
	protected $helpKey = 'Templates:_Edit_Style';

	/**
	 * The help screen base URL for the module.
	 *
	 * @var     string
	 * @since   1.6
	 */
	protected $helpURL;

	/**
	 * Item cache.
	 *
	 * @var    array
	 * @since  1.6
	 */
	private $_cache = array();

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		$config = array_merge(
			array(
				'event_before_delete' => 'onExtensionBeforeDelete',
				'event_after_delete'  => 'onExtensionAfterDelete',
				'event_before_save'   => 'onExtensionBeforeSave',
				'event_after_save'    => 'onExtensionAfterSave',
				'events_map'          => array('delete' => 'extension', 'save' => 'extension')
			), $config
		);

		parent::__construct($config, $factory);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @note    Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load the User state.
		$pk = $app->input->getInt('id');
		$this->setState('style.id', $pk);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_templates');
		$this->setState('params', $params);
	}

	/**
	 * Method to delete rows.
	 *
	 * @param   array  &$pks  An array of item ids.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function delete(&$pks)
	{
		$pks        = (array) $pks;
		$user       = Factory::getUser();
		$table      = $this->getTable();
		$context    = $this->option . '.' . $this->name;

		PluginHelper::importPlugin($this->events_map['delete']);

		// Iterate the items to delete each one.
		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				if (!$user->authorise('core.delete', 'com_templates'))
				{
					throw new \Exception(Text::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}

				// You should not delete a default style
				if ($table->home != '0')
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_TEMPLATES_STYLE_CANNOT_DELETE_DEFAULT_STYLE'), 'error');

					return false;
				}

				// Trigger the before delete event.
				$result = Factory::getApplication()->triggerEvent($this->event_before_delete, array($context, $table));

				if (in_array(false, $result, true) || !$table->delete($pk))
				{
					$this->setError($table->getError());

					return false;
				}

				// Trigger the after delete event.
				Factory::getApplication()->triggerEvent($this->event_after_delete, array($context, $table));
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to duplicate styles.
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  \Exception
	 */
	public function duplicate(&$pks)
	{
		$user = Factory::getUser();

		// Access checks.
		if (!$user->authorise('core.create', 'com_templates'))
		{
			throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->id = 0;

				// Reset the home (don't want dupes of that field).
				$table->home = 0;

				// Alter the title.
				$m = null;
				$table->title = $this->generateNewTitle(null, null, $table->title);

				if (!$table->check())
				{
					throw new \Exception($table->getError());
				}

				// Trigger the before save event.
				$result = Factory::getApplication()->triggerEvent($this->event_before_save, array($context, &$table, true));

				if (in_array(false, $result, true) || !$table->store())
				{
					throw new \Exception($table->getError());
				}

				// Trigger the after save event.
				Factory::getApplication()->triggerEvent($this->event_after_save, array($context, &$table, true));
			}
			else
			{
				throw new \Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the title.
	 *
	 * @param   integer  $categoryId  The id of the category.
	 * @param   string   $alias       The alias.
	 * @param   string   $title       The title.
	 *
	 * @return  string  New title.
	 *
	 * @since   1.7.1
	 */
	protected function generateNewTitle($categoryId, $alias, $title)
	{
		// Alter the title
		$table = $this->getTable();

		while ($table->load(array('title' => $title)))
		{
			$title = StringHelper::increment($title);
		}

		return $title;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// The folder and element vars are passed when saving the form.
		if (empty($data))
		{
			$item	   = $this->getItem();
			$clientId  = $item->client_id;
			$template  = $item->template;
		}
		else
		{
			$clientId  = ArrayHelper::getValue($data, 'client_id');
			$template  = ArrayHelper::getValue($data, 'template');
		}

		// Add the default fields directory
		$baseFolder = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;
		Form::addFieldPath($baseFolder . '/templates/' . $template . '/field');

		// These variables are used to add data from the plugin XML files.
		$this->setState('item.client_id', $clientId);
		$this->setState('item.template', $template);

		// Get the form.
		$form = $this->loadForm('com_templates.style', 'style', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('home', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('home', 'filter', 'unset');
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
		$data = Factory::getApplication()->getUserState('com_templates.edit.style.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_templates.style', $data);

		return $data;
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
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('style.id');

		if (!isset($this->_cache[$pk]))
		{
			// Get a row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}

			// Convert to the \JObject before adding other data.
			$properties        = $table->getProperties(1);
			$this->_cache[$pk] = ArrayHelper::toObject($properties, CMSObject::class);

			// Convert the params field to an array.
			$registry = new Registry($table->params);
			$this->_cache[$pk]->params = $registry->toArray();

			// Get the template XML.
			$client = ApplicationHelper::getClientInfo($table->client_id);
			$path   = Path::clean($client->path . '/templates/' . $table->template . '/templateDetails.xml');

			if (file_exists($path))
			{
				$this->_cache[$pk]->xml = simplexml_load_file($path);
			}
			else
			{
				$this->_cache[$pk]->xml = null;
			}
		}

		return $this->_cache[$pk];
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   Form   $form   A Form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  \Exception if there is an error in the form event.
	 */
	protected function preprocessForm(Form $form, $data, $group = 'content')
	{
		$clientId = $this->getState('item.client_id');
		$template = $this->getState('item.template');
		$lang     = Factory::getLanguage();
		$client   = ApplicationHelper::getClientInfo($clientId);

		if (!$form->loadFile('style_' . $client->name, true))
		{
			throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
		}

		$formFile = Path::clean($client->path . '/templates/' . $template . '/templateDetails.xml');

		// Load the core and/or local language file(s).
			$lang->load('tpl_' . $template, $client->path)
		||	$lang->load('tpl_' . $template, $client->path . '/templates/' . $template);

		if (file_exists($formFile))
		{
			// Get the template form.
			if (!$form->loadFile($formFile, false, '//config'))
			{
				throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
			}
		}

		// Disable home field if it is default style

		if ((is_array($data) && array_key_exists('home', $data) && $data['home'] == '1')
			|| (is_object($data) && isset($data->home) && $data->home == '1'))
		{
			$form->setFieldAttribute('home', 'readonly', 'true');
		}

		if ($client->name === 'site' && !Multilanguage::isEnabled())
		{
			$form->setFieldAttribute('home', 'type', 'radio');
			$form->setFieldAttribute('home', 'layout', 'joomla.form.field.radio.switcher');
		}

		// Attempt to load the xml file.
		if (!$xml = simplexml_load_file($formFile))
		{
			throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
		}

		// Get the help data from the XML file if present.
		$help = $xml->xpath('/extension/help');

		if (!empty($help))
		{
			$helpKey = trim((string) $help[0]['key']);
			$helpURL = trim((string) $help[0]['url']);

			$this->helpKey = $helpKey ?: $this->helpKey;
			$this->helpURL = $helpURL ?: $this->helpURL;
		}

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		// Detect disabled extension
		$extension = Table::getInstance('Extension', 'Joomla\\CMS\\Table\\');

		if ($extension->load(array('enabled' => 0, 'type' => 'template', 'element' => $data['template'], 'client_id' => $data['client_id'])))
		{
			$this->setError(Text::_('COM_TEMPLATES_ERROR_SAVE_DISABLED_TEMPLATE'));

			return false;
		}

		$app        = Factory::getApplication();
		$table      = $this->getTable();
		$pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('style.id');
		$isNew      = true;

		// Include the extension plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		if ($app->input->get('task') == 'save2copy')
		{
			$data['title']    = $this->generateNewTitle(null, null, $data['title']);
			$data['home']     = 0;
			$data['assigned'] = '';
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the before save event.
		$result = Factory::getApplication()->triggerEvent($this->event_before_save, array('com_templates.style', &$table, $isNew));

		// Store the data.
		if (in_array(false, $result, true) || !$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$user = Factory::getUser();

		if ($user->authorise('core.edit', 'com_menus') && $table->client_id == 0)
		{
			$n       = 0;
			$db      = $this->getDbo();
			$user    = Factory::getUser();
			$tableId = (int) $table->id;
			$userId  = (int) $user->id;

			if (!empty($data['assigned']) && is_array($data['assigned']))
			{
				$data['assigned'] = ArrayHelper::toInteger($data['assigned']);

				// Update the mapping for menu items that this style IS assigned to.
				$query = $db->getQuery(true)
					->update($db->quoteName('#__menu'))
					->set($db->quoteName('template_style_id') . ' = :newtsid')
					->whereIn($db->quoteName('id'), $data['assigned'])
					->where($db->quoteName('template_style_id') . ' != :tsid')
					->where('(' . $db->quoteName('checked_out') . ' IS NULL OR ' . $db->quoteName('checked_out') . ' = :userid)')
					->bind(':userid', $userId, ParameterType::INTEGER)
					->bind(':newtsid', $tableId, ParameterType::INTEGER)
					->bind(':tsid', $tableId, ParameterType::INTEGER);
				$db->setQuery($query);
				$db->execute();
				$n += $db->getAffectedRows();
			}

			// Remove style mappings for menu items this style is NOT assigned to.
			// If unassigned then all existing maps will be removed.
			$query = $db->getQuery(true)
				->update($db->quoteName('#__menu'))
				->set($db->quoteName('template_style_id') . ' = 0');

			if (!empty($data['assigned']))
			{
				$query->whereNotIn($db->quoteName('id'), $data['assigned']);
			}

			$query->where($db->quoteName('template_style_id') . ' = :templatestyleid')
				->where('(' . $db->quoteName('checked_out') . ' IS NULL OR ' . $db->quoteName('checked_out') . ' = :userid)')
				->bind(':userid', $userId, ParameterType::INTEGER)
				->bind(':templatestyleid', $tableId, ParameterType::INTEGER);
			$db->setQuery($query);
			$db->execute();

			$n += $db->getAffectedRows();

			if ($n > 0)
			{
				$app->enqueueMessage(Text::plural('COM_TEMPLATES_MENU_CHANGED', $n));
			}
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the after save event.
		Factory::getApplication()->triggerEvent($this->event_after_save, array('com_templates.style', &$table, $isNew));

		$this->setState('style.id', $table->id);

		return true;
	}

	/**
	 * Method to set a template style as home.
	 *
	 * @param   integer  $id  The primary key ID for the style.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws	\Exception
	 */
	public function setHome($id = 0)
	{
		$user = Factory::getUser();
		$db   = $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.edit.state', 'com_templates'))
		{
			throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$style = $this->getTable();

		if (!$style->load((int) $id))
		{
			throw new \Exception(Text::_('COM_TEMPLATES_ERROR_STYLE_NOT_FOUND'));
		}

		// Detect disabled extension
		$extension = Table::getInstance('Extension', 'Joomla\\CMS\\Table\\');

		if ($extension->load(array('enabled' => 0, 'type' => 'template', 'element' => $style->template, 'client_id' => $style->client_id)))
		{
			throw new \Exception(Text::_('COM_TEMPLATES_ERROR_SAVE_DISABLED_TEMPLATE'));
		}

		$clientId = (int) $style->client_id;
		$id       = (int) $id;

		// Reset the home fields for the client_id.
		$query = $db->getQuery(true)
			->update($db->quoteName('#__template_styles'))
			->set($db->quoteName('home') . ' = ' . $db->quote('0'))
			->where($db->quoteName('client_id') . ' = :clientid')
			->where($db->quoteName('home') . ' = ' . $db->quote('1'))
			->bind(':clientid', $clientId, ParameterType::INTEGER);
		$db->setQuery($query);
		$db->execute();

		// Set the new home style.
		$query = $db->getQuery(true)
			->update($db->quoteName('#__template_styles'))
			->set($db->quoteName('home') . ' = ' . $db->quote('1'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);
		$db->setQuery($query);
		$db->execute();

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to unset a template style as default for a language.
	 *
	 * @param   integer  $id  The primary key ID for the style.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws	\Exception
	 */
	public function unsetHome($id = 0)
	{
		$user = Factory::getUser();
		$db   = $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.edit.state', 'com_templates'))
		{
			throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$id = (int) $id;

		// Lookup the client_id.
		$query = $db->getQuery(true)
			->select($db->quoteName(['client_id', 'home']))
			->from($db->quoteName('#__template_styles'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);
		$db->setQuery($query);
		$style = $db->loadObject();

		if (!is_numeric($style->client_id))
		{
			throw new \Exception(Text::_('COM_TEMPLATES_ERROR_STYLE_NOT_FOUND'));
		}
		elseif ($style->home == '1')
		{
			throw new \Exception(Text::_('COM_TEMPLATES_ERROR_CANNOT_UNSET_DEFAULT_STYLE'));
		}

		// Set the new home style.
		$query = $db->getQuery(true)
			->update($db->quoteName('#__template_styles'))
			->set($db->quoteName('home') . ' = ' . $db->quote('0'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);
		$db->setQuery($query);
		$db->execute();

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Get the necessary data to load an item help screen.
	 *
	 * @return  object  An object with key, url, and local properties for loading the item help screen.
	 *
	 * @since   1.6
	 */
	public function getHelp()
	{
		return (object) array('key' => $this->helpKey, 'url' => $this->helpURL);
	}

	/**
	 * Custom clean cache method
	 *
	 * @param   string   $group     The cache group
	 * @param   integer  $clientId  @deprecated   5.0   No longer used.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function cleanCache($group = null, $clientId = 0)
	{
		parent::cleanCache('com_templates');
		parent::cleanCache('_system');
	}
}
