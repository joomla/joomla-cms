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
	 * @return   void
	 *
	 * @since		 __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{

		if (!$this->app->isClient('site'))
		{
			return;
		}

		$this->app->getDocument()->getWebAssetManager()
			->registerAndUseScript('cookiemanager.script', 'plg_system_cookiemanager/cookiemanager.min.js', [], ['defer' => true], ['core'])
			->registerAndUseStyle('cookiemanager.style', 'plg_system_cookiemanager/cookiemanager.min.css');

		$lang = Factory::getLanguage();
		$lang->load('com_cookiemanager', JPATH_ADMINISTRATOR);

		$params = ComponentHelper::getParams('com_cookiemanager');
		$sitemenu = $this->app->getMenu();
		$menuitem = $sitemenu->getItem($params->get('policylink'));

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(['id','title','alias','description']))
				->from($db->quoteName('#__categories'))
				->where($db->quoteName('extension') . ' = ' . $db->quote('com_cookiemanager'));

			$db->setQuery($query);
			$category = $db->loadObjectList();

			$body = Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_DESCRIPTION') . '<br><br><a '
							. ' href="' . $menuitem->link . '">' . Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY') . '</a>';

		foreach ($category as $key => $value)
		{
			$body .= '<br><label class="m-2" for="' . $value->alias . '"><input class="form-check-input" id="' . $value->alias . '" type=checkbox>' . $value->title . '</label>';
		}

		$this->cookieBanner = HTMLHelper::_(
			'bootstrap.renderModal',
			'cookieBanner',
			[
					'title' => Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_TITLE'),
					'footer' => '<button type="button" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_CONFIRM_CHOICE_BUTTON_TEXT') . '</button>'
					. '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#preferences">'
					. Text::_('COM_COOKIEMANAGER_PREFERENCE_BUTTON_TEXT') . '</button>'
					. '<button type="button" class="btn btn-info" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_ACCEPT_BUTTON_TEXT') . '</button>',

				],
			$body
		);

			$db = $this->db;
			$query = $db->getQuery(true)
				->select($db->quoteName(['c.id','c.alias','a.cookie_name','a.cookie_desc','a.exp_period','a.exp_value']))
				->from($db->quoteName('#__categories', 'c'))
				->join(
					'RIGHT',
					$db->quoteName('#__cookiemanager_cookies', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid')
				);

			$db->setQuery($query);
			$cookies = $db->loadObjectList();

			$body = '';

		foreach ($category as $key1 => $value1)
		{
			$body .= '<a data-bs-toggle="collapse" href="#' . $value1->alias . '" >' . $value1->title . '</a><br>';
			$body .= '<div class="collapse" id="' . $value1->alias . '">' . $value1->description . '<br>';
			$table = '<table class="table"><th>Cookie Name</th><th>Description</th><th>Expiration</th>';

			foreach ($cookies as $key => $value)
			{
				if (!empty($value))
				{
					if ($value1->id == $value->id)
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

			$table .= '</table>';
			$body .= $table . '</div>';
		}

			$this->preferences = HTMLHelper::_(
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
		echo $this->cookieBanner;
		echo $this->preferences;
		echo '<button class="preview btn btn-info" data-bs-toggle="modal" data-bs-target="#cookieBanner">' . Text::_('COM_COOKIEMANAGER_PREVIEW_BUTTON_TEXT') . '</button>';

	}

}
