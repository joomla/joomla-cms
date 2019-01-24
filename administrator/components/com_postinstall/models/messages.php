<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model class to manage postinstall messages
 *
 * @since  3.2
 */
class PostinstallModelMessages extends FOFModel
{
	/**
	 * Builds the SELECT query
	 *
	 * @param   boolean  $overrideLimits  Are we requested to override the set limits?
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.2
	 */
	public function buildQuery($overrideLimits = false)
	{
		$query = parent::buildQuery($overrideLimits);

		$db = $this->getDbo();

		// Add a forced extension filtering to the list
		$eid = $this->getState('eid', 700);
		$query->where($db->qn('extension_id') . ' = ' . $db->q($eid));

		// Force filter only enabled messages
		$published = $this->getState('published', 1, 'int');
		$query->where($db->qn('enabled') . ' = ' . (int) $published);

		return $query;
	}

	/**
	 * Returns the name of an extension, as registered in the #__extensions table
	 *
	 * @param   integer  $eid  The extension ID
	 *
	 * @return  string  The extension name
	 *
	 * @since   3.2
	 */
	public function getExtensionName($eid)
	{
		// Load the extension's information from the database
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(array('name', 'element', 'client_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('extension_id') . ' = ' . (int) $eid);

		$db->setQuery($query, 0, 1);

		$extension = $db->loadObject();

		if (!is_object($extension))
		{
			return '';
		}

		// Load language files
		$basePath = JPATH_ADMINISTRATOR;

		if ($extension->client_id == 0)
		{
			$basePath = JPATH_SITE;
		}

		$lang = JFactory::getLanguage();
		$lang->load($extension->element, $basePath);

		// Return the localised name
		return JText::_(strtoupper($extension->name));
	}

	/**
	 * Resets all messages for an extension
	 *
	 * @param   integer  $eid  The extension ID whose messages we'll reset
	 *
	 * @return  mixed  False if we fail, a db cursor otherwise
	 *
	 * @since   3.2
	 */
	public function resetMessages($eid)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->update($db->qn('#__postinstall_messages'))
			->set($db->qn('enabled') . ' = 1')
			->where($db->qn('extension_id') . ' = ' . (int) $eid);
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Hides all messages for an extension
	 *
	 * @param   integer  $eid  The extension ID whose messages we'll hide
	 *
	 * @return  mixed  False if we fail, a db cursor otherwise
	 *
	 * @since   3.8.7
	 */
	public function hideMessages($eid)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->update($db->qn('#__postinstall_messages'))
			->set($db->qn('enabled') . ' = 0')
			->where($db->qn('extension_id') . ' = ' . (int) $eid);
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * List post-processing. This is used to run the programmatic display
	 * conditions against each list item and decide if we have to show it or
	 * not.
	 *
	 * Do note that this a core method of the RAD Layer which operates directly
	 * on the list it's being fed. A little touch of modern magic.
	 *
	 * @param   array  &$resultArray  A list of items to process
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function onProcessList(&$resultArray)
	{
		$unset_keys          = array();
		$language_extensions = array();

		// Order the results DESC so the newest is on the top.
		$resultArray = array_reverse($resultArray);

		foreach ($resultArray as $key => $item)
		{
			// Filter out messages based on dynamically loaded programmatic conditions.
			if (!empty($item->condition_file) && !empty($item->condition_method))
			{
				jimport('joomla.filesystem.file');

				$file = FOFTemplateUtils::parsePath($item->condition_file, true);

				if (JFile::exists($file))
				{
					require_once $file;

					$result = call_user_func($item->condition_method);

					if ($result === false)
					{
						$unset_keys[] = $key;
					}
				}
			}

			// Load the necessary language files.
			if (!empty($item->language_extension))
			{
				$hash = $item->language_client_id . '-' . $item->language_extension;

				if (!in_array($hash, $language_extensions))
				{
					$language_extensions[] = $hash;
					JFactory::getLanguage()->load($item->language_extension, $item->language_client_id == 0 ? JPATH_SITE : JPATH_ADMINISTRATOR);
				}
			}
		}

		if (!empty($unset_keys))
		{
			foreach ($unset_keys as $key)
			{
				unset($resultArray[$key]);
			}
		}
	}

	/**
	 * Get the dropdown options for the list of component with post-installation messages
	 *
	 * @since 3.4
	 *
	 * @return  array  Compatible with JHtmlSelect::genericList
	 */
	public function getComponentOptions()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('extension_id')
			->from($db->qn('#__postinstall_messages'))
			->group(array($db->qn('extension_id')));
		$db->setQuery($query);
		$extension_ids = $db->loadColumn();

		$options = array();

		JFactory::getLanguage()->load('files_joomla.sys', JPATH_SITE, null, false, false);

		foreach ($extension_ids as $eid)
		{
			$options[] = JHtml::_('select.option', $eid, $this->getExtensionName($eid));
		}

		return $options;
	}

	/**
	 * Adds or updates a post-installation message (PIM) definition. You can use this in your post-installation script using this code:
	 *
	 * require_once JPATH_LIBRARIES . '/fof/include.php';
	 * FOFModel::getTmpInstance('Messages', 'PostinstallModel')->addPostInstallationMessage($options);
	 *
	 * The $options array contains the following mandatory keys:
	 *
	 * extension_id        The numeric ID of the extension this message is for (see the #__extensions table)
	 *
	 * type                One of message, link or action. Their meaning is:
	 *                         message  Informative message. The user can dismiss it.
	 *                         link     The action button links to a URL. The URL is defined in the action parameter.
	 *                         action   A PHP action takes place when the action button is clicked. You need to specify the action_file
	 *                                  (RAD path to the PHP file) and action (PHP function name) keys. See below for more information.
	 *
	 * title_key           The JText language key for the title of this PIM.
	 *                     Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_TITLE
	 *
	 * description_key     The JText language key for the main body (description) of this PIM
	 *                     Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_DESCRIPTION
	 *
	 * action_key          The JText language key for the action button. Ignored and not required when type=message
	 *                     Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_ACTION
	 *
	 * language_extension  The extension name which holds the language keys used above.
	 *                     For example, com_foobar, mod_something, plg_system_whatever, tpl_mytemplate
	 *
	 * language_client_id  Should we load the frontend (0) or backend (1) language keys?
	 *
	 * version_introduced  Which was the version of your extension where this message appeared for the first time?
	 *                     Example: 3.2.1
	 *
	 * enabled             Must be 1 for this message to be enabled. If you omit it, it defaults to 1.
	 *
	 * condition_file      The RAD path to a PHP file containing a PHP function which determines whether this message should be shown to
	 *                     the user. @see FOFTemplateUtils::parsePath() for RAD path format. Joomla! will include this file before calling
	 *                     the condition_method.
	 *                     Example:   admin://components/com_foobar/helpers/postinstall.php
	 *
	 * condition_method    The name of a PHP function which will be used to determine whether to show this message to the user. This must be
	 *                     a simple PHP user function (not a class method, static method etc) which returns true to show the message and false
	 *                     to hide it. This function is defined in the condition_file.
	 *                     Example: com_foobar_postinstall_messageone_condition
	 *
	 * When type=message no additional keys are required.
	 *
	 * When type=link the following additional keys are required:
	 *
	 * action  The URL which will open when the user clicks on the PIM's action button
	 *         Example:    index.php?option=com_foobar&view=tools&task=installSampleData
	 *
	 * When type=action the following additional keys are required:
	 *
	 * action_file  The RAD path to a PHP file containing a PHP function which performs the action of this PIM. @see FOFTemplateUtils::parsePath()
	 *              for RAD path format. Joomla! will include this file before calling the function defined in the action key below.
	 *              Example:   admin://components/com_foobar/helpers/postinstall.php
	 *
	 * action       The name of a PHP function which will be used to run the action of this PIM. This must be a simple PHP user function
	 *              (not a class method, static method etc) which returns no result.
	 *              Example: com_foobar_postinstall_messageone_action
	 *
	 * @param   array  $options  See description
	 *
	 * @return  $this
	 *
	 * @throws  Exception
	 */
	public function addPostInstallationMessage(array $options)
	{
		// Make sure there are options set
		if (!is_array($options))
		{
			throw new Exception('Post-installation message definitions must be of type array', 500);
		}

		// Initialise array keys
		$defaultOptions = array(
			'extension_id'       => '',
			'type'               => '',
			'title_key'          => '',
			'description_key'    => '',
			'action_key'         => '',
			'language_extension' => '',
			'language_client_id' => '',
			'action_file'        => '',
			'action'             => '',
			'condition_file'     => '',
			'condition_method'   => '',
			'version_introduced' => '',
			'enabled'            => '1',
		);

		$options = array_merge($defaultOptions, $options);

		// Array normalisation. Removes array keys not belonging to a definition.
		$defaultKeys = array_keys($defaultOptions);
		$allKeys     = array_keys($options);
		$extraKeys   = array_diff($allKeys, $defaultKeys);

		if (!empty($extraKeys))
		{
			foreach ($extraKeys as $key)
			{
				unset($options[$key]);
			}
		}

		// Normalisation of integer values
		$options['extension_id']       = (int) $options['extension_id'];
		$options['language_client_id'] = (int) $options['language_client_id'];
		$options['enabled']            = (int) $options['enabled'];

		// Normalisation of 0/1 values
		foreach (array('language_client_id', 'enabled') as $key)
		{
			$options[$key] = $options[$key] ? 1 : 0;
		}

		// Make sure there's an extension_id
		if (!(int) $options['extension_id'])
		{
			throw new Exception('Post-installation message definitions need an extension_id', 500);
		}

		// Make sure there's a valid type
		if (!in_array($options['type'], array('message', 'link', 'action')))
		{
			throw new Exception('Post-installation message definitions need to declare a type of message, link or action', 500);
		}

		// Make sure there's a title key
		if (empty($options['title_key']))
		{
			throw new Exception('Post-installation message definitions need a title key', 500);
		}

		// Make sure there's a description key
		if (empty($options['description_key']))
		{
			throw new Exception('Post-installation message definitions need a description key', 500);
		}

		// If the type is anything other than message you need an action key
		if (($options['type'] != 'message') && empty($options['action_key']))
		{
			throw new Exception('Post-installation message definitions need an action key when they are of type "' . $options['type'] . '"', 500);
		}

		// You must specify the language extension
		if (empty($options['language_extension']))
		{
			throw new Exception('Post-installation message definitions need to specify which extension contains their language keys', 500);
		}

		// The action file and method are only required for the "action" type
		if ($options['type'] == 'action')
		{
			if (empty($options['action_file']))
			{
				throw new Exception('Post-installation message definitions need an action file when they are of type "action"', 500);
			}

			$file_path = FOFTemplateUtils::parsePath($options['action_file'], true);

			if (!@is_file($file_path))
			{
				throw new Exception('The action file ' . $options['action_file'] . ' of your post-installation message definition does not exist', 500);
			}

			if (empty($options['action']))
			{
				throw new Exception('Post-installation message definitions need an action (function name) when they are of type "action"', 500);
			}
		}

		if ($options['type'] == 'link')
		{
			if (empty($options['link']))
			{
				throw new Exception('Post-installation message definitions need an action (URL) when they are of type "link"', 500);
			}
		}

		// The condition file and method are only required when the type is not "message"
		if ($options['type'] != 'message')
		{
			if (empty($options['condition_file']))
			{
				throw new Exception('Post-installation message definitions need a condition file when they are of type "' . $options['type'] . '"', 500);
			}

			$file_path = FOFTemplateUtils::parsePath($options['condition_file'], true);

			if (!@is_file($file_path))
			{
				throw new Exception('The condition file ' . $options['condition_file'] . ' of your post-installation message definition does not exist', 500);
			}

			if (empty($options['condition_method']))
			{
				throw new Exception(
					'Post-installation message definitions need a condition method (function name) when they are of type "'
					. $options['type'] . '"',
					500
				);
			}
		}

		// Check if the definition exists
		$table     = $this->getTable();
		$tableName = $table->getTableName();

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($tableName))
			->where($db->qn('extension_id') . ' = ' . (int) $options['extension_id'])
			->where($db->qn('type') . ' = ' . $db->q($options['type']))
			->where($db->qn('title_key') . ' = ' . $db->q($options['title_key']));

		$existingRow = $db->setQuery($query)->loadAssoc();

		// Is the existing definition the same as the one we're trying to save?
		if (!empty($existingRow))
		{
			$same = true;

			foreach ($options as $k => $v)
			{
				if ($existingRow[$k] != $v)
				{
					$same = false;
					break;
				}
			}

			// Trying to add the same row as the existing one; quit
			if ($same)
			{
				return $this;
			}

			// Otherwise it's not the same row. Remove the old row before insert a new one.
			$query = $db->getQuery(true)
				->delete($db->qn($tableName))
				->where($db->q('extension_id') . ' = ' . (int) $options['extension_id'])
				->where($db->q('type') . ' = ' . $db->q($options['type']))
				->where($db->q('title_key') . ' = ' . $db->q($options['title_key']));

			$db->setQuery($query)->execute();
		}

		// Insert the new row
		$options = (object) $options;
		$db->insertObject($tableName, $options);

		return $this;
	}
}
