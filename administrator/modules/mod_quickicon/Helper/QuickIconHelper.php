<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Quickicon\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Categories\Administrator\Model\CategoriesModel;
use Joomla\Component\Checkin\Administrator\Model\CheckinModel;
use Joomla\Component\Content\Administrator\Model\ArticlesModel;
use Joomla\Component\Content\Administrator\Model\ModulesModel;
use Joomla\Component\Installer\Administrator\Model\ManageModel;
use Joomla\Component\Menus\Administrator\Model\ItemsModel;
use Joomla\Component\Plugins\Administrator\Model\PluginsModel;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Helper for mod_quickicon
 *
 * @since  1.6
 */
abstract class QuickIconHelper
{
	/**
	 * Stack to hold buttons
	 *
	 * @var     array[]
	 * @since   1.6
	 */
	protected static $buttons = array();
	
	/**
	 * Helper method to return button list.
	 *
	 * This method returns the array by reference so it can be
	 * used to add custom buttons or remove default ones.
	 *
	 * @param   Registry        $params       The module parameters
	 * @param   CMSApplication  $application  The application
	 *
	 * @return  array  An array of buttons
	 *
	 * @since   1.6
	 */
	public static function &getButtons(Registry $params, CMSApplication $application = null)
	{
		if ($application == null)
		{
			$application = Factory::getApplication();
		}

		$key = (string) $params;

		if (!isset(self::$buttons[$key]))
		{
			// Load mod_quickicon language file in case this method is called before rendering the module
			$application->getLanguage()->load('mod_quickicon');

			// Update Panel, icons come from plugins quickicons
			if ($params->get('icon_type', 'site') === 'update')
			{
				// Update Panel, icons come from plugins quickicons
				$context = $params->get('context', 'mod_quickicon');

				// Include buttons defined by published quickicon plugins
				PluginHelper::importPlugin('quickicon');

				$arrays = (array) $application->triggerEvent(
					'onGetIcons',
					new QuickIconsEvent('onGetIcons', ['context' => $context])
				);

				foreach ($arrays as $response)
				{
					foreach ($response as $icon)
					{
						$default = array(
							'link'   => null,
							'image'  => null,
							'text'   => null,
							'access' => true,
							'class' => true,
							'group'  => 'MOD_QUICKICON_EXTENSIONS',
						);
						$icon = array_merge($default, $icon);

						if (!is_null($icon['link']) && !is_null($icon['text']))
						{
							self::$buttons[$key][] = $icon;
						}
					}
				}
			}
			elseif ($params->get('icon_type', 'site') === 'system')
			{
				// Load mod_quickicon language file in case this method is called before rendering the module
				$application->getLanguage()->load('mod_quickicon');
				
				if ($params->get('show_checkin', '1'))
				{
					self::$buttons[$key][] = [
						'amount' => self::countCheckin(),
						'link'   => Route::_('index.php?option=com_checkin'),
						'image'  => 'fa fa-unlock',
						'text'   => Text::_('MOD_QUICKICON_CHECKINS'),
						'access' => array('core.admin', 'com_checkin'),
						'group'  => 'MOD_QUICKICON_SYSTEM'
					];
				}
				if ($params->get('show_cache', '1'))
				{
					self::$buttons[$key][] = [
						'amount' => '123kB',
						'link'   => Route::_('index.php?option=com_chache'),
						'image'  => 'fa fa-cloud',
						'text'   => Text::_('MOD_QUICKICON_CACHE'),
						'access' => array('core.admin', 'com_cache'),
						'group'  => 'MOD_QUICKICON_SYTEM'
					];
				}
				if ($params->get('show_global', '1'))
				{				
					self::$buttons[$key][] = [
						'link'   => Route::_('index.php?option=com_config'),
						'image'  => 'fa fa-cog',
						'text'   => Text::_('MOD_QUICKICON_GLOBAL_CONFIGURATION'),
						'access' => array('core.manage', 'com_config', 'core.admin', 'com_config'),
						'group'  => 'MOD_QUICKICON_SYSTEM',
					];
				}
			}
			elseif ($params->get('icon_type', 'site') === 'site')
			{
				if ($params->get('show_users', '1'))
				{
					$amount = self::countUsers();
					
					self::$buttons[$key][] = [
						'amount' => $amount,
						'link'   => Route::_('index.php?option=com_users'),
						'image'  => 'fa fa-users',
						'linkadd'   => Route::_('index.php?option=com_users&task=user.add'),
						'addwhat' => Text::plural('MOD_QUICKICON_USER_MANAGER', 1),
						'name'   => Text::plural('MOD_QUICKICON_USER_MANAGER', $amount),
						'access' => array('core.manage', 'com_users', 'core.create', 'com_users'),
						'group'  => 'MOD_QUICKICON_SITE',
					];
				}

				if ($params->get('show_menuItems', '1'))
				{
					$amount = self::countMenuItems();
					
					self::$buttons[$key][] = [
						'amount' => $amount,
						'link'   => Route::_('index.php?option=com_menus'),						
						'image'  => 'fa fa-list',
						'linkadd'   => Route::_('index.php?option=com_menus&task=item.add'),
						'addwhat' => Text::plural('MOD_QUICKICON_MENUITEMS_MANAGER', 1),
						'name'   => Text::plural('MOD_QUICKICON_MENUITEMS_MANAGER', $amount),
						'access' => array('core.manage', 'com_menus', 'core.create', 'com_menus'),
						'group'  => 'MOD_QUICKICON_STRUCTURE',
					];
				}

				if ($params->get('show_articles', '1'))
				{
					$amount = self::countArticles();
					
					self::$buttons[$key][] = [
						'amount' => $amount,
						'link'   => Route::_('index.php?option=com_content'),
						'image'  => 'fa fa-files',
						'linkadd'   => Route::_('index.php?option=com_content&task=article.add'),
						'addwhat' => Text::plural('MOD_QUICKICON_ARTICLE_MANAGER', 1),
						'name'   => Text::plural('MOD_QUICKICON_ARTICLE_MANAGER', $amount),
						'access' => array('core.manage', 'com_content', 'core.create', 'com_content'),
						'group'  => 'MOD_QUICKICON_SITE',
					];
				}

				if ($params->get('show_categories', '1'))
				{
					$amount = self::countArticleCategories();
					
					self::$buttons[$key][] = [
						'amount' => $amount,
						'link'   => Route::_('index.php?option=com_categories'),
						'image'  => 'fa fa-folders',
						'addwhat' => Text::plural('MOD_QUICKICON_CATEGORY_MANAGER', 1),
						'linkadd'   => Route::_('index.php?option=com_categories&task=category.add'),
						'name'   => Text::plural('MOD_QUICKICON_CATEGORY_MANAGER', $amount),
						'access' => array('core.manage', 'com_categories', 'core.create', 'com_categories'),
						'group'  => 'MOD_QUICKICON_SITE',
					];
				}

				if ($params->get('show_media', '1'))
				{
					self::$buttons[$key][] = [
						'image'  => 'fa fa-image',
						'link'   => Route::_('index.php?option=com_media'),
						'text'   => Text::_('MOD_QUICKICON_MEDIA_MANAGER'),
						'access' => array('core.manage', 'com_media'),
						'group'  => 'MOD_QUICKICON_SITE',
					];
				}

				if ($params->get('show_modules', '1'))
				{
					$amount = self::countModules();
					
					self::$buttons[$key][] = [
						'amount' => $amount,
						'link'   => Route::_('index.php?option=com_modules'),
						'image'  => 'fa fa-grid',
						'text'   => Text::plural('MOD_QUICKICON_MODULE_MANAGER', $amount),
						'access' => array('core.manage', 'com_modules'),
						'group'  => 'MOD_QUICKICON_SITE'
					];
				}

				if ($params->get('show_plugins', '1'))
				{
					$amount = self::countPlugins();
					
					self::$buttons[$key][] = [
						'amount' => $amount,
						'link'   => Route::_('index.php?option=com_plugins'),
						'image'  => 'fa fa-plug',
						'text'   => Text::plural('MOD_QUICKICON_PLUGIN_MANAGER', $amount),
						'access' => array('core.manage', 'com_plugins'),
						'group'  => 'MOD_QUICKICON_SITE'
					];
				}

				if ($params->get('show_templates', '1'))
				{
					self::$buttons[$key][] = [
						'amount' => self::countTemplates(),
						'image'  => 'fa fa-edit',
						'link'   => Route::_('index.php?option=com_templates&client_id=0'),
						'text'   => Text::_('MOD_QUICKICON_TEMPLATES'),
						'access' => array('core.admin', 'com_templates'),
						'group'  => 'MOD_QUICKICON_SITE'
					];
				}
			}
		}

		return self::$buttons[$key];
	}

	/**
	 * Method to get the number of published modules in frontend.
	 * 
	 * @return  integer  The amount of published modules in frontend
	 *
	 * @since   4.0
	 */
	private static function countModules()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_modules')->getMVCFactory()
			->createModel('Modules', 'Administrator', ['ignore_request' => true]);

		$model->setState('list.select', '*');

		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);
		$model->setState('filter.client_id', 0);

		return  count($model->getItems());
	}
	/**
	 * Method to get the number of published articles.
	 * 
	 * @return  integer  The amount of published articles
	 *
	 * @since   4.0
	 */
	private static function countArticles()
	{
		$app = Factory::getApplication();
		
		// Get an instance of the generic articles model (administrator)
		$model = $app->bootComponent('com_content')->getMVCFactory()
			->createModel('Articles', 'Administrator', ['ignore_request' => true]);

		// Count IDs
		$model->setState('list.select', 'a.id');

		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);

		return count($model->getItems());
	}
	
	/**
	 * Method to get the number of published menu tems.
	 * 
	 * @return  integer  The amount of active menu Items
	 *
	 * @since   4.0
	 */
	private static function countMenuItems()
	{
		$app = Factory::getApplication();
		
		// Get an instance of the menuitems model (administrator)
		$model = $app->bootComponent('com_menus')->getMVCFactory()->createModel('Items', 'Administrator', ['ignore_request' => true]);

		// Count IDs
		$model->setState('list.select', 'a.id');

		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);
		$model->setState('filter.client_id', 0);

		return count($model->getItems());
	}
	
	/**
	 * Method to get the number of users
	 * 
	 * @return  integer  The amount of active users
	 *
	 * @since   4.0
	 */
	private static function countUsers()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_users')->getMVCFactory()->createModel('Users', 'Administrator', ['ignore_request' => true]);

		$model->setState('list.select', '*');

		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.state', 0);

		return count($model->getItems());
	}

	/**
	 * Method to get the number of enabled Plugins
	 * 
	 * @return  integer  The amount of enabled plugins
	 *
	 * @since   4.0
	 */
	private static function countPlugins()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_plugins')->getMVCFactory()->createModel('Plugins', 'Administrator', ['ignore_request' => true]);

		$model->setState('list.select', '*');

		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.enabled', 1);

		return count($model->getItems());
	}	
	
	/**
	 * Method to get the number of content categories
	 * 
	 * @return  integer  The amount of published content categories
	 *
	 * @since   4.0
	 */
	private static function countArticleCategories()
	{
		$app = Factory::getApplication();
		
		$model = $app->bootComponent('com_categories')->getMVCFactory()->createModel('Categories', 'Administrator', ['ignore_request' => true]);

		$model->setState('list.select', 'a.id');

		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);
		$model->setState('filter.extension', 'com_content');

		return count($model->getItems());
	}

	/**
	 * Method to get checkin
	 * 
	 * @return  integer  The amount of checkins
	 *
	 * @since   4.0
	 */
	private static function countCheckin()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_checkin')->getMVCFactory()->createModel('Checkin', 'Administrator', ['ignore_request' => true]);

		return $model->getTotal();
	}

	/**
	 * Method to get Templates
	 * 
	 * @return  integer  The amount of Templates
	 *
	 * @since   4.0
	 */
	private static function countTemplates()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_templates')->getMVCFactory()->createModel('Templates', 'Administrator', ['ignore_request' => true]);
		
		return count($model->getItems());
	}
}
