<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Modules manager master display controller.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'modules';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean        $cachable   If true, the view output will be cached
	 * @param   array|boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}
	 *
	 * @return  static|boolean   This object to support chaining or false on failure.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$layout = $this->input->get('layout', 'edit');
		$id     = $this->input->getInt('id');

		// Verify client
		$clientId = $this->input->post->getInt('client_id');

		if (!is_null($clientId))
		{
			$uri = Uri::getInstance();

			if ((int) $uri->getVar('client_id') !== (int) $clientId)
			{
				$this->setRedirect(Route::_('index.php?option=com_modules&view=modules&client_id=' . $clientId, false));

				return false;
			}
		}

		// Check for edit form.
		if ($layout == 'edit' && !$this->checkEditId('com_modules.edit.module', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			if (!\count($this->app->getMessageQueue()))
			{
				$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			}

			$this->setRedirect(Route::_('index.php?option=com_modules&view=modules&client_id=' . $this->input->getInt('client_id'), false));

			return false;
		}

		// Check custom administrator menu modules
		if (ModuleHelper::isAdminMultilang())
		{
			$languages = LanguageHelper::getInstalledLanguages(1, true);
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

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName('m.language'))
				->from($db->quoteName('#__modules', 'm'))
				->where($db->quoteName('m.module') . ' = ' . $db->quote('mod_menu'))
				->where($db->quoteName('m.published') . ' = 1')
				->where($db->quoteName('m.client_id') . ' = 1')
				->group($db->quoteName('m.language'));

			$mLanguages = $db->setQuery($query)->loadColumn();

			// Check if we have a mod_menu module set to All languages or a mod_menu module for each admin language.
			if (!in_array('*', $mLanguages) && count($langMissing = array_diff(array_keys($langCodes), $mLanguages)))
			{
				$langMissing = array_intersect_key($langCodes, array_flip($langMissing));

				$this->app->enqueueMessage(Text::sprintf('JMENU_MULTILANG_WARNING_MISSING_MODULES', implode(', ', $langMissing)), 'warning');
			}
		}

		return parent::display();
	}
}
