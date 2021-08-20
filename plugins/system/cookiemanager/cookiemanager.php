<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetManager;

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
	 * @var    ApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Cookie consent banner
	 *
	 * @var    ConsentBanner
	 * @since  __DEPLOY_VERSION__
	 */
	protected $consentBanner;

	/**
	 * Cookie settings banner
	 *
	 * @var    SettingsBanner
	 * @since  __DEPLOY_VERSION__
	 */
	protected $settingsBanner;

	/**
	 * Database object
	 *
	 * @var    DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	 protected $db;

	 /**
	  * For script in DOM
	  *
	  * @var    Script
	  * @since  __DEPLOY_VERSION__
	  */
	 protected $script = [];

	 /**
	  * For scripts in DOM
	  *
	  * @var    Scripts
	  * @since  __DEPLOY_VERSION__
	  */
	 protected $scripts;

	 /**
	  * For cookie category
	  *
	  * @var    Category
	  * @since  __DEPLOY_VERSION__
	  */
	 protected $category;

	/**
	 * Add assets for the cookie banners.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		ob_start();
		ob_implicit_flush(false);

		HTMLHelper::_('bootstrap.collapse');

		// Load required assets
		$assets = $this->app->getDocument()->getWebAssetManager();
		$assets->registerAndUseScript(
			'plg_system_cookiemanager.script',
			'plg_system_cookiemanager/cookiemanager.min.js',
			[],
			['defer' => true],
			['core']
		);
		$assets->registerAndUseStyle(
			'plg_system_cookiemanager.style',
			'plg_system_cookiemanager/cookiemanager.min.css'
		);

		// Load cookiemanager component language file
		$this->app->getLanguage()->load('com_cookiemanager', JPATH_ADMINISTRATOR);

		Text::script('COM_COOKIEMANAGER_PREFERENCES_LESS_BUTTON_TEXT');
		Text::script('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT');

		$params = ComponentHelper::getParams('com_cookiemanager');
		$sitemenu = $this->app->getMenu();
		$menuitem = $sitemenu->getItem($params->get('policylink', '101'));

		$cookieManagerConfig = [];
		$cookieManagerConfig['expiration'] = $params->get('consent_expiration', 30);
		$cookieManagerConfig['position'] = $params->get('modal_position', null);
		$this->app->getDocument()->addScriptOptions('config', $cookieManagerConfig);

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(['id','title','alias','description']))
			->from($db->quoteName('#__categories'))
			->where([
				$db->quoteName('extension') . ' = ' . $db->quote('com_cookiemanager'),
				$db->quoteName('published') . ' =  1',
				]
			)
			->order($db->quoteName('lft'));

		$this->category = $db->setQuery($query)->loadObjectList();

		$consentBannerBody = '<p>' . Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_DESCRIPTION') . '</p><p><a '
			. ' href="' . $menuitem->link . '">' . Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY') . '</a></p>'
			. '<h5>' . Text::_('COM_COOKIEMANAGER_MANAGE_CONSENT_PREFERENCES') . '</h5><ul>';

		foreach ($this->category as $key => $value)
		{
			$consentBannerBody .= '<li class="cookie-cat form-check form-check-inline"><label>' . $value->title . '<span class="ms-4 form-check-inline form-switch"><input class="form-check-input" data-cookiecategory="'
			. $value->alias . '" type=checkbox></span></label></li>';
		}

		$consentBannerBody .= '</ul>';

		$this->consentBanner = HTMLHelper::_(
			'bootstrap.renderModal',
			'consentBanner',
			[
					'title' => Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_TITLE'),
					'footer' => '<button type="button" id="consentConfirmChoice" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_CONFIRM_MY_CHOICES_BUTTON_TEXT') . '</button>'
					. '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-dismiss="modal" data-bs-target="#settingsBanner">'
					. Text::_('COM_COOKIEMANAGER_MORE_DETAILS') . '</button>'
					. '<button type="button" data-button="acceptAllCookies" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_ACCEPT_ALL_COOKIES_BUTTON_TEXT') . '</button>',

				],
			$consentBannerBody
		);

		$query = $db->getQuery(true)
			->select($db->quoteName(['c.id','c.alias','a.cookie_name','a.cookie_desc','a.exp_period','a.exp_value']))
			->from($db->quoteName('#__categories', 'c'))
			->join(
				'RIGHT',
				$db->quoteName('#__cookiemanager_cookies', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid') . 'AND' . $db->quoteName('a.published') . ' =  1'
			)
			->order($db->quoteName('lft'));

		$cookies = $db->setQuery($query)->loadObjectList();

		$settingsBannerBody = '<p>' . Text::_('COM_COOKIEMANAGER_PREFERENCES_DESCRIPTION') . '</p>'
		 . '<p><a  href="' . $menuitem->link . '">' . Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY') . '</a></p>'
		 . '<p> Consent: <span id="consent-opt-in"></span></p><p> Consent ID: <span id="ccuuid"></span></p>'
		 . '<p> Consent Date: <span id="consent-date"></span></p>';

		foreach ($this->category as $catKey => $catValue)
		{
			$settingsBannerBody .= '<h4>' . $catValue->title . '<span class="form-check-inline form-switch float-end">' .
			'<input class="form-check-input " type="checkbox" data-cookie-category="' . $catValue->alias . '"></span></h4>' . $catValue->description;

			$settingsBannerBody .= '<a class="text-decoration-none" data-bs-toggle="collapse" href="#' . $catValue->alias . '" role="button" aria-expanded="false" '
			. 'aria-controls="' . $catValue->alias . '">' . Text::_('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT') . '</a><div class="collapse" id="' . $catValue->alias . '">';
			$table = '<table class="table"><thead><tr><th scope="col">' . Text::_('COM_COOKIEMANAGER_TABLE_HEAD_COOKIENAME') . '</th><th scope="col">' . Text::_('COM_COOKIEMANAGER_TABLE_HEAD_DESCRIPTION') . '</th><th scope="col">' . Text::_('COM_COOKIEMANAGER_TABLE_HEAD_EXPIRATION') . '</th></tr></thead><tbody>';

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
			$settingsBannerBody .= $table . '</div>';
		}

		$this->settingsBanner = HTMLHelper::_(
			'bootstrap.renderModal',
			'settingsBanner',
			[
				'title' => Text::_('COM_COOKIEMANAGER_PREFERENCES_TITLE'),
				'footer' => '<button type="button" id="settingsConfirmChoice" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_CONFIRM_MY_CHOICES_BUTTON_TEXT') . '</button>'
					. '<button type="button" data-button="acceptAllCookies" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_ACCEPT_ALL_COOKIES_BUTTON_TEXT') . '</button>'
			],
			$settingsBannerBody
		);

		$query = $db->getQuery(true)
			->select($db->quoteName(['a.type','a.position','a.code','a.catid']))
			->from($db->quoteName('#__categories', 'c'))
			->join(
				'RIGHT',
				$db->quoteName('#__cookiemanager_scripts', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid') . 'AND' . $db->quoteName('a.published') . ' =  1'
			);

			$this->scripts = $db->setQuery($query)->loadObjectList();

		foreach ($this->category as $catKey => $catValue)
		{
			if (!isset($_COOKIE['cookie_category_' . $catValue->alias]) || $_COOKIE['cookie_category_' . $catValue->alias] === 'false')
			{
				$this->script[$catValue->alias] = [];

				foreach ($this->scripts as $key => $value)
				{
					if ($catValue->id == $value->catid)
					{
						array_push($this->script[$catValue->alias], $value);
					}
				}
			}
		}

				$this->app->getDocument()->addScriptOptions('code', $this->script);

		if (!$this->app->input->cookie->get('uuid'))
		{
			$uuid = bin2hex(random_bytes(16));
			$cookieLifetime = $params->get('consent_expiration', 30) * 24 * 60 * 60;
			$this->app->input->cookie->set('uuid', $uuid, time() + $cookieLifetime, '/');
		}
	}

	/**
	 * Echo the cookie banners, button and scripts.
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

		echo $this->consentBanner;
		echo $this->settingsBanner;

		// Return early in case of AJAX request
		if ($this->app->input->get('format') === 'json')
		{
			return;
		}

		echo '<button class="preview btn btn-info" data-bs-toggle="modal" data-bs-target="#consentBanner">' . Text::_('COM_COOKIEMANAGER_PREVIEW_BUTTON_TEXT') . '</button>';

		foreach ($this->category as $catKey => $catValue)
		{
			if (isset($_COOKIE['cookie_category_' . $catValue->alias]) && $_COOKIE['cookie_category_' . $catValue->alias] === 'true')
			{
				$this->script[$catValue->alias] = [];

				foreach ($this->scripts as $key => $value)
				{
					if ($catValue->id == $value->catid)
					{
						if ($value->type == 1 || $value->type == 2)
						{
							if ($value->position == 1)
							{
								$html = ob_get_contents();

								if ($html)
								{
									ob_end_clean();
								}

								echo str_replace('<head>', '<head>' . $value->code, $html);
							}
							elseif ($value->position == 2)
							{
								$html = ob_get_contents();

								if ($html)
								{
									ob_end_clean();
								}

								echo str_replace('</head>', $value->code . '</head>', $html);
							}
							elseif ($value->position == 3)
							{
								$html = ob_get_contents();

								if ($html)
								{
									ob_end_clean();
								}

								echo preg_replace('/<body[^>]+>\K/i', $value->code, $html);
							}

							else
							{
								$html = ob_get_contents();

								if ($html)
								{
									ob_end_clean();
								}

								echo str_replace('</body>', $value->code . '</body>', $html);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * AJAX Handler
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAjaxCookiemanager()
	{
		$cookieConsentsData = $this->app->input->get('data', '', 'STRING');

		$cookieConsentsData = json_decode($cookieConsentsData);
		$ccuuid = bin2hex(random_bytes(32));
		$cookieConsentsData->ccuuid = $ccuuid;
		$cookieConsentsData->user_agent = $_SERVER['HTTP_USER_AGENT'];

		$this->db->insertObject('#__cookiemanager_consents', $cookieConsentsData);

		return $ccuuid;
	}
}
