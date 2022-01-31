<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Base controller class for Menu Manager.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $default_view = 'menus';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean        $cachable   If true, the view output will be cached
	 * @param   array|boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  static    This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Verify menu
		$menuType = $this->input->post->getCmd('menutype', '');

		if ($menuType !== '')
		{
			$uri = Uri::getInstance();

			if ($uri->getVar('menutype') !== $menuType)
			{
				$uri->setVar('menutype', $menuType);

				if ($forcedLanguage = $this->input->post->get('forcedLanguage'))
				{
					$uri->setVar('forcedLanguage', $forcedLanguage);
				}

				$this->setRedirect(Route::_('index.php' . $uri->toString(['query']), false));

				return parent::display();
			}
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
				->where(
					[
						$db->quoteName('m.module') . ' = ' . $db->quote('mod_menu'),
						$db->quoteName('m.published') . ' = 1',
						$db->quoteName('m.client_id') . ' = 1',
					]
				)
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
