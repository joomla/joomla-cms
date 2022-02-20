<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
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
		if (JModuleHelper::isAdminMultilang())
		{
			$languages = JLanguageHelper::getInstalledLanguages(1, true);
			$langCodes = array();

			foreach ($languages as $language)
			{
				if (isset($language->metadata['nativeName']))
				{
					$languageName = $language->metadata['nativeName'];
				}
				else
				{
					$languageName = $language->metadata['name'];
				}

				$langCodes[$language->metadata['tag']] = $languageName;
			}

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->qn('m.language'))
				->from($db->qn('#__modules', 'm'))
				->where($db->qn('m.module') . ' = ' . $db->quote('mod_menu'))
				->where($db->qn('m.published') . ' = 1')
				->where($db->qn('m.client_id') . ' = 1')
				->group($db->qn('m.language'));

			$mLanguages = $db->setQuery($query)->loadColumn();

			// Check if we have a mod_menu module set to All languages or a mod_menu module for each admin language.
			if (!in_array('*', $mLanguages) && count($langMissing = array_diff(array_keys($langCodes), $mLanguages)))
			{
				$app         = JFactory::getApplication();
				$langMissing = array_intersect_key($langCodes, array_flip($langMissing));

				$app->enqueueMessage(JText::sprintf('JMENU_MULTILANG_WARNING_MISSING_MODULES', implode(', ', $langMissing)), 'warning');
			}
		}

		return parent::display();
	}
}
