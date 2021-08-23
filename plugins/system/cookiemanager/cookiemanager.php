<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\CMS\Plugin\PluginHelper;

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
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Template contents for cookie banners
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $bannerContent;

	/**
	 * Database object
	 *
	 * @var    \Joomla\Database\DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	 protected $db;

	 /**
	  * Cookie settings scripts
	  *
	  * @var    object
	  * @since  __DEPLOY_VERSION__
	  */
	 protected $cookieScripts;

	 /**
	  * Cookie categories
	  *
	  * @var    object
	  * @since  __DEPLOY_VERSION__
	  */
	  protected $cookieCategories;

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

		HTMLHelper::_('bootstrap.collapse');

		ob_start();
		ob_implicit_flush(false);

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

		$params = ComponentHelper::getParams('com_cookiemanager');

		Text::script('COM_COOKIEMANAGER_PREFERENCES_LESS_BUTTON_TEXT');
		Text::script('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT');

		$cookieManagerConfig = [];
		$cookieManagerConfig['expiration'] = $params->get('consent_expiration', 30);
		$cookieManagerConfig['position'] = $params->get('modal_position', null);
		$this->app->getDocument()->addScriptOptions('config', $cookieManagerConfig);

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(['c.id','c.alias','a.cookie_name','a.cookie_desc','a.exp_period','a.exp_value']))
			->from($db->quoteName('#__categories', 'c'))
			->join(
				'RIGHT',
				$db->quoteName('#__cookiemanager_cookies', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid') . 'AND' . $db->quoteName('a.published') . ' =  1'
			)
			->order($db->quoteName('lft'));

		$cookies = $db->setQuery($query)->loadObjectList();

		$query = $db->getQuery(true)
			->select($db->quoteName(['id','title','alias','description']))
			->from($db->quoteName('#__categories'))
			->where([
				$db->quoteName('extension') . ' = ' . $db->quote('com_cookiemanager'),
				$db->quoteName('published') . ' =  1',
				]
			)
			->order($db->quoteName('lft'));

		$this->cookieCategories = $db->setQuery($query)->loadObjectList();

		$query = $db->getQuery(true)
			->select($db->quoteName(['a.type','a.position','a.code','a.catid']))
			->from($db->quoteName('#__cookiemanager_scripts', 'a'))
			->where($db->quoteName('a.published') . ' =  1')
			->join(
				'LEFT',
				$db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid')
			);

		$this->cookieScripts = $db->setQuery($query)->loadObjectList();

		$cookieCodes = [];

		foreach ($this->cookieCategories as $catKey => $catValue)
		{
			if (!isset($_COOKIE['cookie_category_' . $catValue->alias]) || $_COOKIE['cookie_category_' . $catValue->alias] === 'false')
			{
				$cookieCodes[$catValue->alias] = [];

				foreach ($this->cookieScripts as $key => $value)
				{
					if ($catValue->id == $value->catid)
					{
						array_push($cookieCodes[$catValue->alias], $value);
					}
				}
			}
		}

		$this->app->getDocument()->addScriptOptions('code', $cookieCodes);

		if (!$this->app->input->cookie->get('uuid'))
		{
			$uuid = bin2hex(random_bytes(16));
			$cookieLifetime = $params->get('consent_expiration', 30) * 24 * 60 * 60;
			$this->app->input->cookie->set('uuid', $uuid, time() + $cookieLifetime, '/');
		}

		ob_start();
		include PluginHelper::getLayoutPath('system', 'cookiemanager');
		$this->bannerContent = ob_get_clean();

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

		echo $this->bannerContent;

		// Return early in case of AJAX request
		if ($this->app->input->get('format') === 'json')
		{
			return;
		}

		echo '<button class="preview btn btn-info" data-bs-toggle="modal" data-bs-target="#consentBanner">' . Text::_('COM_COOKIEMANAGER_PREVIEW_BUTTON_TEXT') . '</button>';

		foreach ($this->cookieCategories as $catKey => $catValue)
		{
			if (isset($_COOKIE['cookie_category_' . $catValue->alias]) && $_COOKIE['cookie_category_' . $catValue->alias] === 'true')
			{
				foreach ($this->cookieScripts as $key => $value)
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
