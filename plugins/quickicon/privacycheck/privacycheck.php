<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.privacycheck
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * Plugin to check privacy requests older than 14 days
 *
 * @since  3.9.0
 */
class PlgQuickiconPrivacyCheck extends JPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Check privacy requests older than 14 days.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   3.9.0
	 */
	public function onGetIcons($context)
	{
		if ($context !== $this->params->get('context', 'mod_quickicon') || !Factory::getUser()->authorise('core.admin'))
		{
			return;
		}

		JHtml::_('jquery.framework');

		$token    = Session::getFormToken() . '=' . 1;
		$privacy  = 'index.php?option=com_privacy';

		$options  = array(
			'plg_quickicon_privacycheck_url'      => Uri::base() . $privacy . '&view=requests&filter[status]=1&list[fullordering]=a.requested_at ASC',
			'plg_quickicon_privacycheck_ajax_url' => Uri::base() . $privacy . '&task=getNumberUrgentRequests&' . $token,
			'plg_quickicon_privacycheck_text'     => array(
				"NOREQUEST"            => Text::_('PLG_QUICKICON_PRIVACYCHECK_NOREQUEST'),
				"REQUESTFOUND"         => Text::_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND'),
				"REQUESTFOUND_MESSAGE" => Text::_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND_MESSAGE'),
				"REQUESTFOUND_BUTTON"  => Text::_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND_BUTTON'),
				"ERROR"                => Text::_('PLG_QUICKICON_PRIVACYCHECK_ERROR'),
			)
		);

		Factory::getDocument()->addScriptOptions('js-privacy-check', $options);

		JHtml::_('script', 'plg_quickicon_privacycheck/privacycheck.js', array('version' => 'auto', 'relative' => true));

		return array(
			array(
				'link'  => $privacy . '&view=requests&filter[status]=1&list[fullordering]=a.requested_at ASC',
				'image' => 'users',
				'icon'  => 'header/icon-48-user.png',
				'text'  => Text::_('PLG_QUICKICON_PRIVACYCHECK_CHECKING'),
				'id'    => 'plg_quickicon_privacycheck',
				'group' => 'MOD_QUICKICON_USERS'
			)
		);
	}
}
