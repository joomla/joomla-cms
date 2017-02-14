<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.Blog
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Sampledata - Blog Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSampledataBlog extends JPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Holds the menuitem model
	 *
	 * @var    MenusModelItem
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $menuItemModel;

	/**
	 * Get an overview of the proposed sampledata.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onSampledataGetOverview()
	{
		$data = new stdClass;
		$data->name        = $this->_name;
		$data->title       = JText::_('PLG_SAMPLEDATA_BLOG_OVERVIEW_TITLE');
		$data->description = JText::_('PLG_SAMPLEDATA_BLOG_OVERVIEW_DESC');
		$data->icon        = 'broadcast';
		$data->steps       = 3;

		return $data;
	}

	/**
	 * First step to enter the sampledata.
	 *
	 * @return  array or void  Will be converted into the JSON response to the module.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onAjaxSampledataApplyStep1()
	{
		if ($this->app->input->get('type') != $this->_name)
		{
			return;
		};

		if (!JComponentHelper::isEnabled('com_content'))
		{
			$response            = array();
			$response['success'] = true;
			$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_SKIPPED', 1, 'com_content');

			return $response;
		}

		// Get some metadata.
		$access = (int) $this->app->get('access', 1);
		$user   = JFactory::getUser();

		// Add Include Paths.
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_content/models/', 'ContentModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_content/tables/');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/models/', 'CategoriesModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables/');

		// Create "blog" category.
		$categoryModel = JModelLegacy::getInstance('Category', 'CategoriesModel');
		$catIds        = array();
		$categoryTitle = JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_CATEGORY_0_TITLE');
		$category      = array(
			'title'           => $categoryTitle,
			'parent_id'       => 0,
			'id'              => 0,
			'published'       => 1,
			'access'          => $access,
			'created_user_id' => $user->id,
			'extension'       => 'com_content',
			'level'           => 1,
			'alias'           => JApplicationHelper::stringURLSafe($categoryTitle),
			'associations'    => array(),
			'description'     => '',
			'language'        => '*',
		);

		if (!$categoryModel->save($category))
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, $categoryModel->getError());

			return $response;
		}

		// Get ID from category we just added
		$catIds[] = $categoryModel->getItem()->id;

		// Create "help" category.
		$categoryTitle = JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_CATEGORY_1_TITLE');
		$category      = array(
			'title'           => $categoryTitle,
			'parent_id'       => 0,
			'id'              => 0,
			'published'       => 1,
			'access'          => $access,
			'created_user_id' => $user->id,
			'extension'       => 'com_content',
			'level'           => 1,
			'alias'           => JApplicationHelper::stringURLSafe($categoryTitle),
			'associations'    => array(),
			'description'     => '',
			'language'        => '*',
		);

		if (!$categoryModel->save($category))
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, $categoryModel->getError());

			return $response;
		}

		// Get ID from category we just added
		$catIds[] = $categoryModel->getItem()->id;

		// Create Articles.
		$articleModel  = JModelLegacy::getInstance('Article', 'ContentModel');
		$articles = array(
			array(
				'catid'    => $catIds[1],
				'ordering' => 2,
			),
			array(
				'catid'    => $catIds[1],
				'ordering' => 1,
				'access'   => 3,
			),
			array(
				'catid'    => $catIds[0],
				'ordering' => 2,
			),
			array(
				'catid'    => $catIds[0],
				'ordering' => 1,
			),
			array(
				'catid'    => $catIds[0],
				'ordering' => 0,
			),
			array(
				'catid'    => $catIds[0],
				'ordering' => 0,
			),
		);

		foreach ($articles as $i => $article)
		{
			// Set values from language strings.
			$article['title']     = JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_' . $i . '_TITLE');
			$article['introtext'] = JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_' . $i . '_INTROTEXT');
			$article['fulltext']  = JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_' . $i . '_FULLTEXT');

			// Set values which are always the same.
			$article['id']              = 0;
			$article['created_user_id'] = $user->id;
			$article['alias']           = JApplicationHelper::stringURLSafe($article['title']);
			$article['language']        = '*';
			$article['associations']    = array();
			$article['state']           = 1;
			$article['featured']        = 0;
			$article['images']          = '';

			if (!isset($article['access']))
			{
				$article['access'] = $access;
			}

			if (!$articleModel->save($article))
			{
				JFactory::getLanguage()->load('com_content');
				$response            = array();
				$response['success'] = false;
				$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, JText::_($articleModel->getError()));

				return $response;
			}

			// Get ID from category we just added
			$ids[] = $articleModel->getItem()->id;
		}

		$this->app->setUserState('sampledata.blog.articles', $ids);
		$this->app->setUserState('sampledata.blog.articles.catids', $catIds);

		$response = new stdClass;
		$response->success = true;
		$response->message = JText::_('PLG_SAMPLEDATA_BLOG_STEP1_SUCCESS');

		return $response;
	}

	/**
	 * Second step to enter the sampledata. Menus.
	 *
	 * @return  array or void  Will be converted into the JSON response to the module.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onAjaxSampledataApplyStep2()
	{
		if ($this->app->input->get('type') != $this->_name)
		{
			return;
		}

		if (!JComponentHelper::isEnabled('com_menus'))
		{
			$response = array();
			$response['success'] = true;
			$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_SKIPPED', 2, 'com_menus');

			return $response;
		}

		// Create the menu types.
		$menuTable = JTable::getInstance('Type', 'JTableMenu');
		$menuTypes = array();

		for ($i = 0; $i <= 2; $i++)
		{
			$menu = array(
				'id'          => 0,
				'title'       => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_MENU_' . $i . '_TITLE'),
				'description' => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_MENU_' . $i . '_DESCRIPTION'),
			);

			// Calculate menutype.
			$menu['menutype'] = JApplicationHelper::stringURLSafe($menu['title']);

			$menuTable->load();
			$menuTable->bind($menu);

			if (!$menuTable->store())
			{
				JFactory::getLanguage()->load('com_menus');
				$response = array();
				$response['success'] = false;
				$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, JText::_($menuTable->getError()));

				return $response;
			}

			$menuTypes[] = $menuTable->menutype;
		}

		// Storing IDs in UserState for later useage.
		$this->app->setUserState('sampledata.blog.menutypes', $menuTypes);

		// Get previously entered Data from UserStates.
		$articleIds = $this->app->getUserState('sampledata.blog.articles');

		// Get MenuItemModel.
		JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/models/', 'MenusModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables/');
		$this->menuItemModel = JModelLegacy::getInstance('Item', 'MenusModel');

		// Unset current "Home" menuitem since we set a new one.
		$menuItemTable = JTable::getInstance('Menu', 'MenusTable');
		$menuItemTable->load(
			array(
				'home' => 1,
				'language' => '*',
			)
		);
		$menuItemTable->home = 0;
		$menuItemTable->store();

		// Insert menuitems level 1.
		$menuItems = array(
			array(
				'menutype'     => $menuTypes[0],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_0_TITLE'),
				'link'         => 'index.php?option=com_content&view=category&layout=blog&id=9',
				'component_id' => 22,
				'home'         => 1,
				'params'       => array(
					'layout_type'             => 'blog',
					'show_category_title'     => 0,
					'num_leading_articles'    => 4,
					'num_intro_articles'      => 0,
					'num_columns'             => 1,
					'num_links'               => 2,
					'multi_column_order'      => 1,
					'orderby_sec'             => 'rdate',
					'order_date'              => 'published',
					'show_pagination'         => 2,
					'show_pagination_results' => 1,
					'show_category'           => 0,
					'info_bloc_position'      => 0,
					'show_publish_date'       => 0,
					'show_hits'               => 0,
					'show_feed_link'          => 1,
					'menu_text'               => 1,
					'show_page_heading'       => 0,
					'secure'                  => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[0],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_1_TITLE'),
				'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[0],
				'component_id' => 22,
				'params'       => array(
					'info_block_position' => 0,
					'show_category'       => 0,
					'link_category'       => 0,
					'show_author'         => 0,
					'show_create_date'    => 0,
					'show_publish_date'   => 0,
					'show_hits'           => 0,
					'menu_text'           => 1,
					'show_page_heading'   => 0,
					'secure'              => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[0],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_2_TITLE'),
				'link'         => 'index.php?option=com_users&view=login',
				'component_id' => 25,
				'params'       => array(
					'logindescription_show'  => 1,
					'logoutdescription_show' => 1,
					'menu_text'              => 1,
					'show_page_heading'      => 0,
					'secure'                 => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_3_TITLE'),
				'link'         => 'index.php?option=com_content&view=form&layout=edit',
				'component_id' => 22,
				'access'       => 3,
				'params'       => array(
					'enable_category'   => 1,
					'catid'             => 9,
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_4_TITLE'),
				'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[1],
				'component_id' => 22,
				'params'       => array(
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_5_TITLE'),
				'link'         => 'administrator',
				'type'         => 'url',
				'component_id' => 0,
				'browserNav'   => 1,
				'access'       => 3,
				'params'       => array(
					'menu_text' => 1,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_6_TITLE'),
				'link'         => 'index.php?option=com_users&view=profile&layout=edit',
				'component_id' => 25,
				'access'       => 2,
				'params'       => array(
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_7_TITLE'),
				'link'         => 'index.php?option=com_users&view=login',
				'component_id' => 25,
				'params'       => array(
					'logindescription_show'  => 1,
					'logoutdescription_show' => 1,
					'menu_text'              => 1,
					'show_page_heading'      => 0,
					'secure'                 => 0,
				),
			),
		);

		try
		{
			$menuIdsLevel1 = $this->addMenuItems($menuItems, 1);
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, $e->getMessage());

			return $response;
		}

		// Insert another level 1.
		$menuItems = array(
			array(
				'menutype'     => $menuTypes[2],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_8_TITLE'),
				'link'         => 'index.php?option=com_users&view=login',
				'component_id' => 25,
				'params'       => array(
					'login_redirect_url'     => 'index.php?Itemid=' . $menuIdsLevel1[0],
					'logindescription_show'  => 1,
					'logoutdescription_show' => 1,
					'menu_text'              => 1,
					'show_page_heading'      => 0,
					'secure'                 => 0,
				),
			),
		);

		try
		{
			$menuIdsLevel1 = array_merge($menuIdsLevel1, $this->addMenuItems($menuItems, 1));
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, $e->getMessage());

			return $response;
		}

		// Insert menuitems level 2.
		$menuItems = array(
			array(
				'menutype'     => $menuTypes[1],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_19_TITLE'),
				'link'         => 'index.php?option=com_config&view=config&controller=config.display.config',
				'parent_id'    => $menuIdsLevel1[4],
				'component_id' => 23,
				'access'       => 6,
				'params'       => array(
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[0],
				'title'        => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_10_TITLE'),
				'link'         => 'index.php?option=com_config&view=templates&controller=config.display.templates',
				'parent_id'    => $menuIdsLevel1[4],
				'component_id' => 23,
				'params'       => array(
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
		);

		try
		{
			$this->addMenuItems($menuItems, 2);
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, $e->getMessage());

			return $response;
		}

		$response            = array();
		$response['success'] = true;
		$response['message'] = JText::_('PLG_SAMPLEDATA_BLOG_STEP2_SUCCESS');

		return $response;
	}

	/**
	 * Third step to enter the sampledata. Modules.
	 *
	 * @return  array or void  Will be converted into the JSON response to the module.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onAjaxSampledataApplyStep3()
	{
		if ($this->app->input->get('type') != $this->_name)
		{
			return;
		}

		if (!JComponentHelper::isEnabled('com_modules'))
		{
			$response            = array();
			$response['success'] = true;
			$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_SKIPPED', 3, 'com_modules');

			return $response;
		}

		// Add Include Paths.
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_modules/models/', 'ModulesModelModule');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_modules/tables/');
		$model  = JModelLegacy::getInstance('Module', 'ModulesModel');
		$access = (int) $this->app->get('access', 1);

		// Get previously entered Data from UserStates
		$menuTypes = $this->app->getUserState('sampledata.blog.menutypes');

		$modules = array(
			array(
				'title'     => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_0_TITLE'),
				'ordering'  => 1,
				'position'  => 'position-1',
				'module'    => 'mod_menu',
				'access'    => 3,
				'showtitle' => 0,
				'params'    => array(
					'menutype'        => $menuTypes[1],
					'startLevel'      => 1,
					'endLevel'        => 0,
					'showAllChildren' => 1,
					'class_sfx'       => ' nav-pills',
					'layout'          => '_:default',
					'cache'           => 1,
					'cache_time'      => 900,
					'cachemode'       => 'itemid',
					'module_tag'      => 'div',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
			array(
				'title'     => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_1_TITLE'),
				'ordering'  => 6,
				'position'  => 'position-7',
				'module'    => 'mod_syndicate',
				'showtitle' => 0,
				'params'    => array(
					'display_text' => 1,
					'text'         => 'My Blog',
					'format'       => 'rss',
					'layout'       => '_:default',
					'cache'        => 0,
				),
			),
			array(
				'title'    => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_2_TITLE'),
				'ordering' => 4,
				'position' => 'position-7',
				'module'   => 'mod_articles_archive',
				'params'   => array(
					'count'      => 10,
					'layout'     => '_:default',
					'cache'      => 1,
					'cache_time' => 900,
					'cachemode'  => 'static',
				),
			),
			array(
				'title'    => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_3_TITLE'),
				'ordering' => 5,
				'position' => 'position-7',
				'module'   => 'mod_articles_popular',
				'params'   => array(
					'catid'      => ['9'],
					'count'      => 5,
					'show_front' => 1,
					'layout'     => '_:default',
					'cache'      => 1,
					'cache_time' => 900,
					'cachemode'  => 'static',
				),
			),
			array(
				'title'    => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_4_TITLE'),
				'ordering' => 2,
				'position' => 'position-7',
				'module'   => 'mod_articles_category',
				'params'   => array(
					'mode'                         => 'normal',
					'show_on_article_page'         => 0,
					'show_front'                   => 'show',
					'count'                        => 6,
					'category_filtering_type'      => 1,
					'catid'                        => ['9'],
					'show_child_category_articles' => 0,
					'levels'                       => 1,
					'author_filtering_type'        => 1,
					'author_alias_filtering_type'  => 1,
					'date_filtering'               => 'off',
					'date_field'                   => 'a.created',
					'relative_date'                => 30,
					'article_ordering'             => 'a.created',
					'article_ordering_direction'   => 'DESC',
					'article_grouping'             => 'none',
					'article_grouping_direction'   => 'krsort',
					'month_year_format'            => 'F Y',
					'item_heading'                 => 5,
					'link_titles'                  => 1,
					'show_date'                    => 0,
					'show_date_field'              => 'created',
					'show_date_format'             => JText::_('DATE_FORMAT_LC5'),
					'show_category'                => 0,
					'show_hits'                    => 0,
					'show_author'                  => 0,
					'show_introtext'               => 0,
					'introtext_limit'              => 100,
					'show_readmore'                => 0,
					'show_readmore_title'          => 1,
					'readmore_limit'               => 15,
					'layout'                       => '_:default',
					'owncache'                     => 1,
					'cache_time'                   => 900,
					'module_tag'                   => 'div',
					'bootstrap_size'               => 0,
					'header_tag'                   => 'h3',
					'style'                        => 0,
				),
			),
			array(
				'title'     => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_5_TITLE'),
				'ordering'  => 1,
				'position'  => 'footer',
				'module'    => 'mod_menu',
				'showtitle' => 0,
				'params'    => array(
					'menutype'        => $menuTypes[2],
					'startLevel'      => 1,
					'endLevel'        => 0,
					'showAllChildren' => 0,
					'layout'          => '_:default',
					'cache'           => 1,
					'cache_time'      => 900,
					'cachemode'       => 'itemid',
					'module_tag'      => 'div',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
			array(
				'title'    => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_6_TITLE'),
				'ordering' => 1,
				'position' => 'position-0',
				'module'   => 'mod_search',
				'params'   => array(
					'width'      => 20,
					'button_pos' => 'right',
					'opensearch' => 1,
					'layout'     => '_:default',
					'cache'      => 1,
					'cache_time' => 900,
					'cachemode'  => 'itemid',
				),
			),
			array(
				'title'     => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_7_TITLE'),
				'content'   => '<p><img src="images/headers/raindrops.jpg" alt="" /></p>',
				'ordering'  => 1,
				'position'  => 'position-3',
				'module'    => 'mod_custom',
				'showtitle' => 0,
				'params'    => array(
					'prepare_content' => 1,
					'layout'          => '_:default',
					'cache'           => 1,
					'cache_time'      => 900,
					'cachemode'       => 'static',
					'module_tag'      => 'div',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
			array(
				'title'    => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_8_TITLE'),
				'ordering' => 1,
				'position' => 'position-7',
				'module'   => 'mod_tags_popular',
				'params'   => array(
					'maximum'         => 8,
					'timeframe'       => 'alltime',
					'order_value'     => 'count',
					'order_direction' => 1,
					'display_count'   => 0,
					'no_results_text' => 0,
					'minsize'         => 1,
					'maxsize'         => 2,
					'layout'          => '_:default',
					'owncache'        => 1,
					'module_tag'      => 'div',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
			array(
				'title'    => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_9_TITLE'),
				'ordering' => 0,
				'position' => '',
				'module'   => 'mod_tags_similar',
				'params'   => array(
					'maximum'        => 5,
					'matchtype'      => 'any',
					'layout'         => '_:default',
					'owncache'       => 1,
					'module_tag'     => 'div',
					'bootstrap_size' => 0,
					'header_tag'     => 'h3',
					'style'          => 0,
				),
			),
			array(
				'title'     => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_10_TITLE'),
				'ordering'  => 4,
				'position'  => 'cpanel',
				'module'    => 'mod_stats_admin',
				'access'    => 6,
				'client_id' => 1,
				'params'    => array(
					'serverinfo'     => 1,
					'siteinfo'       => 1,
					'counter'        => 0,
					'increase'       => 0,
					'layout'         => '_:default',
					'cache'          => 1,
					'cache_time'     => 900,
					'cachemode'      => 'static',
					'module_tag'     => 'div',
					'bootstrap_size' => 6,
					'header_tag'     => 'h3',
					'style'          => 0,
				),
			),
			array(
				'title'     => JText::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_11_TITLE'),
				'ordering'  => 1,
				'position'  => 'postinstall',
				'module'    => 'mod_feed',
				'client_id' => 1,
				'params'    => array(
					'rssurl'          => 'https://www.joomla.org/announcements/release-news.feed',
					'rssrtl'          => 0,
					'rsstitle'        => 1,
					'rssdesc'         => 1,
					'rssimage'        => 1,
					'rssitems'        => 3,
					'rssitemdesc'     => 1,
					'word_count'      => 0,
					'layout'          => '_:default',
					'cache'           => 1,
					'cache_time'      => 900,
					'module_tag'      => 'div',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
		);

		foreach ($modules as $module)
		{
			// Set values which are always the same.
			$module['id']              = 0;
			$module['asset_id']        = 0;
			$module['language']        = '*';
			$module['note'] = '';
			$module['published'] = 1;
			$module['assignment'] = 0;

			if (!isset($module['content']))
			{
				$module['content'] = '';
			}

			if (!isset($module['access']))
			{
				$module['access'] = $access;
			}

			if (!isset($module['showtitle']))
			{
				$module['showtitle'] = 1;
			}

			if (!isset($module['client_id']))
			{
				$module['client_id'] = 0;
			}

			if (!$model->save($module))
			{
				JFactory::getLanguage()->load('com_modules');
				$response            = array();
				$response['success'] = false;
				$response['message'] = JText::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 3, JText::_($model->getError()));

				return $response;
			}
		}

		$response            = array();
		$response['success'] = true;
		$response['message'] = JText::_('PLG_SAMPLEDATA_BLOG_STEP3_SUCCESS');

		return $response;
	}

	/**
	 * Adds menuitems.
	 *
	 * @param   array    $menuItems  Array holding the menuitems arrays.
	 * @param   integer  $level      Level in the category tree.
	 *
	 * @return  array  IDs of the inserted menuitems.
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @throws  Exception
	 */
	private function addMenuItems(array $menuItems, $level)
	{
		$itemIds = array();
		$access  = (int) $this->app->get('access', 1);
		$user    = JFactory::getUser();

		foreach ($menuItems as $menuItem)
		{
			// Reset item.id in model state.
			$this->menuItemModel->setState('item.id', 0);

			// Set values which are always the same.
			$menuItem['id']              = 0;
			$menuItem['created_user_id'] = $user->id;
			$menuItem['alias']           = JApplicationHelper::stringURLSafe($menuItem['title']);
			$menuItem['published']       = 1;
			$menuItem['language']        = '*';
			$menuItem['note']            = '';
			$menuItem['img']             = '';
			$menuItem['associations']    = array();
			$menuItem['client_id']       = 0;
			$menuItem['level']           = $level;

			// Set browsernav to default if not set
			if (!isset($menuItem['browsernav']))
			{
				$menuItem['browsernav'] = 0;
			}

			// Set access to default if not set
			if (!isset($menuItem['access']))
			{
				$menuItem['access'] = $access;
			}

			// Set type to 'component' if not set
			if (!isset($menuItem['type']))
			{
				$menuItem['type'] = 'component';
			}

			// Set template_style to global if not set
			if (!isset($menuItem['template_style']))
			{
				$menuItem['template_style'] = 0;
			}

			// Set home if not set
			if (!isset($menuItem['home']))
			{
				$menuItem['home'] = 0;
			}

			// Set parent_id to root (1) if not set
			if (!isset($menuItem['parent_id']))
			{
				$menuItem['parent_id'] = 1;
			}

			if (!$this->menuItemModel->save($menuItem))
			{
				// Try two times with another alias (-1 and -2).
				$menuItem['alias'] .= '-1';

				if (!$this->menuItemModel->save($menuItem))
				{
					$menuItem['alias'] = substr_replace($menuItem['alias'], '2', -1);

					if (!$this->menuItemModel->save($menuItem))
					{
						throw new Exception($menuItem['title'] . ' => ' . $menuItem['alias'] . ' : ' . $this->menuItemModel->getError());
					}
				}
			}

			// Get ID from menuitem we just added
			$itemIds[] = $this->menuItemModel->getstate('item.id');
		}

		return $itemIds;
	}
}
