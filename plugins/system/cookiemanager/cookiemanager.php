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
	 * For cookie banner
	 *
	 * @var    CookieBanner
	 * @since  __DEPLOY_VERSION__
	 */
	protected $cookieBanner;

	/**
	 * For preferences banner
	 *
	 * @var    Preferences
	 * @since  __DEPLOY_VERSION__
	 */
	protected $preferences;

	/**
	 * Database object
	 *
	 * @var    DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	 protected $db;

	/**
	 * Add assets for the modal.
	 *
	 * @return	void
	 *
	 * @since	__DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{

		if (!$this->app->isClient('site'))
		{
			return;
		}

		$this->app->getDocument()->getWebAssetManager()
			->registerAndUseScript('plg_system_cookiemanager.script', 'plg_system_cookiemanager/cookiemanager.min.js', [], ['defer' => true], ['core'])
			->registerAndUseStyle('plg_system_cookiemanager.style', 'plg_system_cookiemanager/cookiemanager.min.css');

		$lang = Factory::getLanguage();
		$lang->load('com_cookiemanager', JPATH_ADMINISTRATOR);

		Text::script('COM_COOKIEMANAGER_PREFERENCES_LESS_BUTTON_TEXT');
		Text::script('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT');

		$params = ComponentHelper::getParams('com_cookiemanager');
		$sitemenu = $this->app->getMenu();
		$menuitem = $sitemenu->getItem($params->get('policylink'));

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(['id','title','alias','description']))
				->from($db->quoteName('#__categories'))
				->where([
					$db->quoteName('extension') . ' = ' . $db->quote('com_cookiemanager'),
					$db->quoteName('published') . ' =  1',
					]
				)
				->order($db->quoteName('lft'));

			$db->setQuery($query);
			$category = $db->loadObjectList();

			$bannerBody = '<p>' . Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_DESCRIPTION') . '</p><p><a '
			. ' href="' . $menuitem->link . '">' . Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY') . '</a></p>'
			. '<h5>' . Text::_('COM_COOKIEMANAGER_MANAGE_CONSENT_PREFERENCES') . '</h5><ul>';

		foreach ($category as $key => $value)
		{
			$bannerBody .= '<li class="cookie-cat form-check form-check-inline"><label for="banner_cat_' . $value->alias . '">' . $value->title . '<span class="ms-4 form-check-inline form-switch"><input class="form-check-input" id="banner_cat_'
			. $value->alias . '" type=checkbox></span></label></li>';
		}

		$bannerBody .= '</ul>';

		$this->cookieBanner = HTMLHelper::_(
			'bootstrap.renderModal',
			'cookieBanner',
			[
					'title' => Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_TITLE'),
					'footer' => '<button type="button" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_CONFIRM_CHOICE_BUTTON_TEXT') . '</button>'
					. '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-dismiss="modal" data-bs-target="#preferences">'
					. Text::_('COM_COOKIEMANAGER_MORE_DETAILS') . '</button>'
					. '<button type="button" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_ACCEPT_BUTTON_TEXT') . '</button>',

				],
			$bannerBody
		);

		HTMLHelper::_('bootstrap.collapse');

			$db = $this->db;
			$query = $db->getQuery(true)
				->select($db->quoteName(['c.id','c.alias','a.cookie_name','a.cookie_desc','a.exp_period','a.exp_value']))
				->from($db->quoteName('#__categories', 'c'))
				->join(
					'RIGHT',
					$db->quoteName('#__cookiemanager_cookies', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid') . 'AND' . $db->quoteName('a.published') . ' =  1'
				)
				->order($db->quoteName('lft'));

			$db->setQuery($query);
			$cookies = $db->loadObjectList();

			$prefBody = '<p>' . Text::_('COM_COOKIEMANAGER_PREFERENCES_DESCRIPTION') . '</p>';
			$prefBody .= '<p><a  href="' . $menuitem->link . '">' . Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY') . '</a></p>';

		foreach ($category as $catKey => $catValue)
		{
			$prefBody .= '<h4>' . $catValue->title . '<span class="form-check-inline form-switch float-end">' .
			'<input class="form-check-input " type="checkbox" id="cat_' . $catValue->alias . '"></span></h4>' . $catValue->description;

			$prefBody .= '<a class="text-decoration-none" data-bs-toggle="collapse" href="#' . $catValue->alias . '" >' . Text::_('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT') . '</a><div class="collapse" id="' . $catValue->alias . '">';
			$table = '<table class="table"><thead><tr><th scope="col">'. Text::_('COM_COOKIEMANAGER_TABLE_HEAD_COOKIENAME') . '</th><th scope="col">'. Text::_('COM_COOKIEMANAGER_TABLE_HEAD_DESCRIPTION') . '</th><th scope="col">'. Text::_('COM_COOKIEMANAGER_TABLE_HEAD_EXPIRATION') . '</th></tr></thead><tbody>';

			foreach ($cookies as $key => $value)
			{
				if (!empty($value))
				{
					if ($catValue->id == $value->id)
					{
						if ($value->exp_period == -1)
						{
							$value->exp_period = "Forever";
							$value->exp_value = "";
						}
						elseif ($value->exp_period == 0)
						{
							$value->exp_period = "Session";
							$value->exp_value = "";
						}

						$table .= '<tr>'
						. '<td>' . $value->cookie_name . '</td>'
						. '<td>' . $value->cookie_desc . '</td>'
						. '<td>' . $value->exp_value . ' ' . $value->exp_period . '</td>'
						. '</tr>';
					}
				}
			}

			$table .= '</tbody></table>';
			$prefBody .= $table . '</div>';
		}

			$this->preferences = HTMLHelper::_(
				'bootstrap.renderModal',
				'preferences',
				[
						'title' => Text::_('COM_COOKIEMANAGER_PREFERENCES_TITLE'),
						'footer' => '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#preferences">'
						. Text::_('COM_COOKIEMANAGER_CONFIRM_CHOICE_BUTTON_TEXT') . '</button>'
						. '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#preferences">'
						. Text::_('COM_COOKIEMANAGER_ACCEPT_BUTTON_TEXT') . '</button>'

					],
				$prefBody
			);

	}

	/**
	 * Echo the modal and button.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRespond()
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		echo $this->cookieBanner;
		echo $this->preferences;
		echo '<button class="preview btn btn-info" data-bs-toggle="modal" data-bs-target="#cookieBanner">' . Text::_('COM_COOKIEMANAGER_PREVIEW_BUTTON_TEXT') . '</button>';

	}

}
