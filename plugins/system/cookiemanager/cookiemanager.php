<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\CMS\Component\ComponentHelper;

/**
 * System plugin to manage cookies.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemCookiemanager extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	 protected $db;

	/**
	 * Before Render Event.
	 *
	 * @return   void
	 *
	 * @since		 __DEPLOY_VERSION__
	 */
	public function onBeforeRender()
	{

		if (!$this->app->isClient('site'))
		{
			return;
		}

		$lang = Factory::getLanguage();
		$lang->load('com_cookiemanager', JPATH_ADMINISTRATOR);

		$params = ComponentHelper::getParams('com_cookiemanager');
		$sitemenu = $this->app->getMenu();
		$menuitem = $sitemenu->getItem($params->get('policylink'));

		$this->app->getDocument()->getWebAssetManager()
			->registerAndUseScript('cookiemanager.script', 'plg_system_cookiemanager/cookiemanager.min.js', [], ['defer' => true], ['core'])
			->registerAndUseStyle('cookiemanager.style', 'plg_system_cookiemanager/cookiemanager.min.css');

		$cookieBanner = HTMLHelper::_(
			'bootstrap.renderModal',
			'cookieBanner',
			[
					'title' => Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_TITLE'),
					'footer' => '<button type="button" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_REVOKE_BUTTON_TEXT') . '</button>'
					. '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#preferences">'
					. Text::_('COM_COOKIEMANAGER_PREFERENCE_BUTTON_TEXT') . '</button>'
					. '<button type="button" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_ACCEPT_BUTTON_TEXT') . '</button>',

				],
			$body = Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_DESCRIPTION') . '<br><br><a '
							. ' href="' . $menuitem->link . '">' . Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY') . '</a>'
		);

			echo $cookieBanner;

			$db = $this->db;
			$query = $db->getQuery(true)
				->select($db->quoteName(['c.id','a.cookie_name','a.cookie_desc','a.exp_period','a.exp_value']))
				->from($db->quoteName('#__categories', 'c'))
				->join(
					'RIGHT',
					$db->quoteName('#__cookiemanager_cookies', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid')
				);

			$db->setQuery($query);
			$cookies = $db->loadObjectList();

		$body = '<table class="table"><th>Cookie Name</th><th>Description</th><th>Expiration</th>';

		foreach ($cookies as $key => $value)
		{
			$body .= '<tr>'
			. '<td>' . $value->cookie_name . '</td>'
			. '<td>' . $value->cookie_desc . '</td>'
			. '<td>' . $value->exp_value . ' ' . $value->exp_period . '</td>'
			. '</tr>';
		}

		$body .= '</table>';

			$preferences = HTMLHelper::_(
				'bootstrap.renderModal',
				'preferences',
				[
						'title' => Text::_('COM_COOKIEMANAGER_PREFERENCE_BUTTON_TEXT'),
						'footer' => '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#preferences">'
						. Text::_('COM_COOKIEMANAGER_CONFIRM_CHOICE_BUTTON_TEXT') . '</button>'
						. '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#preferences">'
						. Text::_('COM_COOKIEMANAGER_ACCEPT_BUTTON_TEXT') . '</button>'

					],
				$body
			);

				echo $preferences;

				echo '<button class="preview btn btn-info" data-bs-toggle="modal" data-bs-target="#cookieBanner">' . Text::_('COM_COOKIEMANAGER_PREVIEW_BUTTON_TEXT') . '</button>';

	}
}
