<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Login\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Helper for mod_login
 *
 * @since  1.6
 */
abstract class LoginHelper
{
	/**
	 * Get an HTML select list of the available languages.
	 *
	 * @return  string
	 */
	public static function getLanguageList()
	{
		$languages = LanguageHelper::createLanguageList(null, JPATH_ADMINISTRATOR, false, true);

		if (\count($languages) <= 1)
		{
			return '';
		}

		usort(
			$languages,
			function ($a, $b)
			{
				return strcmp($a['value'], $b['value']);
			}
		);

		// Fix wrongly set parentheses in RTL languages
		if (Factory::getApplication()->getLanguage()->isRtl())
		{
			foreach ($languages as &$language)
			{
				$language['text'] = $language['text'] . '&#x200E;';
			}
		}

		array_unshift($languages, HTMLHelper::_('select.option', '', Text::_('JDEFAULTLANGUAGE')));

		return HTMLHelper::_('select.genericlist', $languages, 'lang', 'class="form-select"', 'value', 'text', null);
	}

	/**
	 * Get the redirect URI after login.
	 *
	 * @return  string
	 */
	public static function getReturnUri()
	{
		$uri    = Uri::getInstance();
		$return = 'index.php' . $uri->toString(array('query'));

		if ($return != 'index.php?option=com_login')
		{
			return base64_encode($return);
		}
		else
		{
			return base64_encode('index.php');
		}
	}
}
