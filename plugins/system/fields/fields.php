<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.filesystem.file');
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

/**
 * Fields Plugin
 *
 * @since  3.6
 */
class PlgSystemFields extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.6
	 */
	protected $autoloadLanguage = true;

	/**
	 * The save event.
	 *
	 * @param   string    $context  The context
	 * @param   stdClass  $item     The item
	 * @param   boolean   $isNew    Is new
	 *
	 * @return boolean
	 */
	public function onContentBeforeSave($context, $item, $isNew)
	{
		// Load the category context based on the extension
		if ($context == 'com_categories.category')
		{
			$context = JFactory::getApplication()->input->getCmd('extension') . '.category';
		}

		$parts = $this->getParts($context);

		if (!$parts)
		{
			return true;
		}

		$context = $parts[0] . '.' . $parts[1];

		// Loading the fields
		$fieldsObjects = FieldsHelper::getFields($context, $item);

		if (!$fieldsObjects)
		{
			return true;
		}

		$params = new Registry;

		// Load the item params from the request
		$data = JFactory::getApplication()->input->post->get('jform', array(), 'array');

		if (key_exists('params', $data))
		{
			$params->loadArray($data['params']);
		}

		// Load the params from the item itself
		if (isset($item->params))
		{
			$params->loadString($item->params);
		}

		$params = $params->toArray();

		if (!$params)
		{
			return true;
		}

		// Create the new internal fields field
		$fields = array();

		foreach ($fieldsObjects as $field)
		{
			// Only save the fields with the alias from the data
			if (!key_exists($field->alias, $params))
			{
				continue;
			}

			// Set the param on the fields variable
			$fields[$field->alias] = $params[$field->alias];

			// Remove it from the params array
			unset($params[$field->alias]);
		}

		$item->_fields = $fields;

		// Update the cleaned up params
		if (isset($item->params))
		{
			$item->params = json_encode($params);
		}
	}

	/**
	 * The save event.
	 *
	 * @param   string    $context  The context
	 * @param   stdClass  $item     The item
	 * @param   boolean   $isNew    Is new
	 *
	 * @return boolean
	 */
	public function onContentAfterSave($context, $item, $isNew)
	{
		// Load the category context based on the extension
		if ($context == 'com_categories.category')
		{
			$context = JFactory::getApplication()->input->getCmd('extension') . '.category';
		}

		$parts = $this->getParts($context);

		if (!$parts)
		{
			return true;
		}

		$context = $parts[0] . '.' . $parts[1];

		// Return if the item has no valid state
		$fields = null;

		if (isset($item->_fields))
		{
			$fields = $item->_fields;
		}

		if (!$fields)
		{
			return true;
		}

		// Loading the fields
		$fieldsObjects = FieldsHelper::getFields($context, $item);

		if (!$fieldsObjects)
		{
			return true;
		}

		// Loading the model
		$model = JModelLegacy::getInstance('Field', 'FieldsModel', array('ignore_request' => true));

		foreach ($fieldsObjects as $field)
		{
			// Only save the fields with the alias from the data
			if (!key_exists($field->alias, $fields))
			{
				continue;
			}

			$id = null;

			if (isset($item->id))
			{
				$id = $item->id;
			}

			if (!$id)
			{
				continue;
			}

			// Setting the value for the field and the item
			$model->setFieldValue($field->id, $context, $id, $fields[$field->alias]);
		}

		return true;
	}

	/**
	 * The save event.
	 *
	 * @param   array    $userData  The date
	 * @param   boolean  $isNew     Is new
	 * @param   boolean  $success   Is success
	 * @param   string   $msg       The message
	 *
	 * @return boolean
	 */
	public function onUserAfterSave($userData, $isNew, $success, $msg)
	{
		// It is not possible to manipulate the user during save events
		// Check if data is valid or we are in a recursion
		if (!$userData['id'] || !$success)
		{
			return true;
		}

		$user = JFactory::getUser($userData['id']);
		$user->params = (string) $user->getParameters();

		// Trigger the events with a real user
		$this->onContentBeforeSave('com_users.user', $user, false);
		$this->onContentAfterSave('com_users.user', $user, false);

		// Save the user with the modified params
		$db = JFactory::getDbo();
		$db->setQuery('update #__users set params = ' . $db->q($user->params));
		$db->execute();

		return true;
	}

	/**
	 * The delete event.
	 *
	 * @param   string    $context  The context
	 * @param   stdClass  $item     The item
	 *
	 * @return boolean
	 */
	public function onContentAfterDelete($context, $item)
	{
		$parts = $this->getParts($context);

		if (!$parts)
		{
			return true;
		}

		$context = $parts[0] . '.' . $parts[1];

		JLoader::import('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');

		$model = JModelLegacy::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
		$model->cleanupValues($context, $item->id);

		return true;
	}

	/**
	 * The user delete event.
	 *
	 * @param   stdClass  $user    The context
	 * @param   boolean   $succes  Is success
	 * @param   string    $msg     The message
	 *
	 * @return boolean
	 */
	public function onUserAfterDelete($user, $succes, $msg)
	{
		$item     = new stdClass;
		$item->id = $user['id'];

		return $this->onContentAfterDelete('com_users.user', $item);
	}

	/**
	 * The form event.
	 *
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return boolean
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		$context = $form->getName();

		// Transform categories form name to a valid context
		if (strpos($context, 'com_categories.category') !== false)
		{
			$context = str_replace('com_categories.category', '', $context) . '.category';
		}

		// Extracting the component and section
		$parts = $this->getParts($context);

		if (!$parts)
		{
			return true;
		}

		$app = JFactory::getApplication();
		$input = $app->input;

		// If we are on the save command we need the actual data
		$jformData = $input->get('jform', array(), 'array');

		if ($jformData && !$data)
		{
			$data = $jformData;
		}

		if (is_array($data))
		{
			$data = (object) $data;
		}

		if ((!isset($data->catid) || !$data->catid) && JFactory::getApplication()->isSite() && $component = 'com_content')
		{
			$activeMenu = $app->getMenu()->getActive();

			if ($activeMenu && $activeMenu->params)
			{
				$data->catid = $activeMenu->params->get('catid');
			}
		}

		FieldsHelper::prepareForm($parts[0] . '.' . $parts[1], $form, $data);

		if ($app->isAdmin() && $input->get('option') == 'com_categories' && strpos($input->get('extension'), 'fields') !== false)
		{
			// Set the right permission extension
			$form->setFieldAttribute('rules', 'component', 'com_fields');
			$form->setFieldAttribute('rules', 'section', 'category');
		}

		return true;
	}

	/**
	 * The prepare data event.
	 *
	 * @param   string    $context  The context
	 * @param   stdClass  $data     The data
	 *
	 * @return void
	 */
	public function onContentPrepareData($context, $data)
	{
		$parts = $this->getParts($context);

		if (! $parts)
		{
			return;
		}

		if (isset($data->params) && $data->params instanceof Registry)
		{
			$data->params = $data->params->toArray();
		}
	}

	/**
	 * The display event.
	 *
	 * @param   string    $context     The context
	 * @param   stdClass  $item        The item
	 * @param   Registry  $params      The params
	 * @param   number    $limitstart  The start
	 *
	 * @return string
	 */
	public function onContentAfterTitle($context, $item, $params, $limitstart = 0)
	{
		return $this->display($context, $item, $params, 1);
	}

	/**
	 * The display event.
	 *
	 * @param   string    $context     The context
	 * @param   stdClass  $item        The item
	 * @param   Registry  $params      The params
	 * @param   number    $limitstart  The start
	 *
	 * @return string
	 */
	public function onContentBeforeDisplay($context, $item, $params, $limitstart = 0)
	{
		return $this->display($context, $item, $params, 2);
	}

	/**
	 * The display event.
	 *
	 * @param   string    $context     The context
	 * @param   stdClass  $item        The item
	 * @param   Registry  $params      The params
	 * @param   number    $limitstart  The start
	 *
	 * @return string
	 */
	public function onContentAfterDisplay($context, $item, $params, $limitstart = 0)
	{
		return $this->display($context, $item, $params, 3);
	}

	/**
	 * Performs the display event.
	 *
	 * @param   string    $context      The context
	 * @param   stdClass  $item         The item
	 * @param   Registry  $params       The params
	 * @param   integer   $displayType  The type
	 *
	 * @return string
	 */
	private function display($context, $item, $params, $displayType)
	{
		$parts = $this->getParts($context);

		if (!$parts)
		{
			return '';
		}

		$context = $parts[0] . '.' . $parts[1];

		if (is_string($params) || !$params)
		{
			$params = new Registry($params);
		}

		$fields = FieldsHelper::getFields($context, $item, true);

		if ($fields)
		{
			foreach ($fields as $key => $field)
			{
				$fieldDisplayType = $field->params->get('display', '-1');

				if ($fieldDisplayType == '-1')
				{
					$fieldDisplayType = $this->params->get('display', '2');
				}

				if ($fieldDisplayType == $displayType)
				{
					continue;
				}

				unset($fields[$key]);
			}
		}

		if ($fields)
		{
			return FieldsHelper::render(
				$context,
				'fields.render',
				array(
					'item'            => $item,
					'context'         => $context,
					'fields'          => $fields,
					'container'       => $params->get('fields-container'),
					'container-class' => $params->get('fields-container-class')
				)
			);
		}

		return '';
	}

	/**
	 * Performs the display event.
	 *
	 * @param   string    $context  The context
	 * @param   stdClass  $item     The item
	 *
	 * @return boolean
	 */
	public function onContentPrepare ($context, $item)
	{
		$parts = $this->getParts($context);

		if (!$parts)
		{
			return;
		}

		$fields = FieldsHelper::getFields($parts[0] . '.' . $parts[1], $item, true);

		// Adding the fields to the object
		$item->fields = array();

		foreach ($fields as $key => $field)
		{
			$item->fields[$field->id] = $field;
		}

		return true;
	}

	/**
	 * The finder event.
	 *
	 * @param   stdClass  $item  The item
	 *
	 * @return boolean
	 */
	public function onPrepareFinderContent($item)
	{
		$section = strtolower($item->layout);
		$tax     = $item->getTaxonomy('Type');

		if ($tax)
		{
			foreach ($tax as $context => $value)
			{
				// This is only a guess, needs to be improved
				$component = strtolower($context);

				if (strpos($context, 'com_') !== 0)
				{
					$component = 'com_' . $component;
				}

				// Transofrm com_article to com_content
				if ($component == 'com_article')
				{
					$component = 'com_content';
				}

				// Create a dummy object with the required fields
				$tmp     = new stdClass;
				$tmp->id = $item->__get('id');

				if ($item->__get('catid'))
				{
					$tmp->catid = $item->__get('catid');
				}

				// Getting the fields for the constructed context
				$fields = FieldsHelper::getFields($component . '.' . $section, $tmp, true);

				if (is_array($fields))
				{
					foreach ($fields as $field)
					{
						// Adding the instructions how to handle the text
						$item->addInstruction(FinderIndexer::TEXT_CONTEXT, $field->alias);

						// Adding the field value as a field
						$item->{$field->alias} = $field->value;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Returns the parts for the context.
	 *
	 * @param   string  $context  The context
	 *
	 * @return  array
	 */
	private function getParts($context)
	{
		// Some context mapping
		// @todo needs to be done in a general lookup table on some point
		$mapping = array(
				'com_users.registration' => 'com_users.user',
				'com_content.category'   => 'com_content.article'
		);

		if (key_exists($context, $mapping))
		{
			$context = $mapping[$context];
		}

		$parts = FieldsHelper::extract($context);

		if (!$parts)
		{
			return null;
		}

		if ($parts[1] == 'form')
		{
			// The context is not from a known one, we need to do a lookup
			$db = JFactory::getDbo();
			$db->setQuery('select context from #__fields where context like ' . $db->q($parts[0] . '.%') . ' group by context');
			$tmp = $db->loadObjectList();

			if (count($tmp) == 1)
			{
				$parts = FieldsHelper::extract($tmp[0]->context);

				if (count($parts) < 2)
				{
					return null;
				}
			}
		}

		return $parts;
	}
}
