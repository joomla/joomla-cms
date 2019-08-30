<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Modules manager master display controller.
 *
 * @since  1.6
 */
class ModulesController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean        $cachable   If true, the view output will be cached
	 * @param   array|boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}
	 *
	 * @return  JController    This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$id     = $this->input->getInt('id');

		$document = JFactory::getDocument();

		// For JSON requests
		if ($document->getType() == 'json')
		{
			$view = new ModulesViewModule;

			// Get/Create the model
			if ($model = new ModulesModelModule)
			{
				// Checkin table entry
				if (!$model->checkout($id))
				{
					JFactory::getApplication()->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'), 'error');

					return false;
				}

				// Push the model into the view (as default)
				$view->setModel($model, true);
			}

			$view->document = $document;

			return $view->display();
		}

		JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php');

		$layout = $this->input->get('layout', 'edit');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($layout == 'edit' && !$this->checkEditId('com_modules.edit.module', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_modules&view=modules', false));

			return false;
		}

		// Load the submenu.
		ModulesHelper::addSubmenu($this->input->get('view', 'modules'));

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
