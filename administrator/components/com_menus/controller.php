<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base controller class for Menu Manager.
 *
 * @since  1.6
 */
class MenusController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean        $cachable   If true, the view output will be cached
	 * @param   array|boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController    This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

		// Check custom administrator menu modules
		if (JLanguageMultilang::isAdminEnabled())
		{
			// Check if we have any mod_menu module set to All languages
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__modules'))
				->where($db->qn('module') . ' = ' . $db->quote('mod_menu'))
				->where($db->qn('published') . ' = 1')
				->where($db->qn('client_id') . ' = 1')
				->where($db->qn('language') . ' = ' . $db->quote('*'));
			$db->setQuery($query);

			$modulesAll = (int) $db->loadResult();

			// If none, check that we have a mod_menu module for each admin language
			if ($modulesAll == 0)
			{
				$adminLanguages = count(JLanguageHelper::getInstalledLanguages(1));

				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__modules'))
					->where($db->qn('module') . ' = ' . $db->quote('mod_menu'))
					->where($db->qn('published') . ' = 1')
					->where($db->qn('client_id') . ' = 1')
					->where($db->qn('language') . ' != ' . $db->quote('*'));
				$db->setQuery($query);

				$totalModules = (int) $db->loadResult();

				if ($totalModules != $adminLanguages)
				{
					$msg = JText::_('JMENU_MULTILANG_WARNING');
					JFactory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

		return parent::display();
	}
}
