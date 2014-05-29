<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model class to manage postinstall messages
 *
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 * @since       3.2
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
		$query->where($db->qn('enabled') . ' = ' . $db->q($published));

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
			->where($db->qn('extension_id') . ' = ' . $db->q((int) $eid));

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
		return JText::_($extension->name);
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
			->set($db->qn('enabled') . ' = ' . $db->q(1))
			->where($db->qn('extension_id') . ' = ' . $db->q($eid));
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
	 * @param   array  $resultArray  A list of items to process
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function onProcessList(&$resultArray)
	{
		$unset_keys = array();
		$language_extensions = array();

		foreach ($resultArray as $key => $item)
		{
			// Filter out messages based on dynamically loaded programmatic conditions
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

			// Load the necessary language files
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
}
