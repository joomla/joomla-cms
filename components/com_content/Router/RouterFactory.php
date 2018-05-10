<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Component\Content\Site\Router;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Component\Content\Site\Service\Category;
use Joomla\Database\DatabaseInterface;

/**
 * Content router factory.
 *
 * @since  __DEPLOY_VERSION__
 */
class RouterFactory implements RouterFactoryInterface
{
	/**
	 * The category
	 *
	 * @var Category
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $category;

	/**
	 * The db
	 *
	 * @var DatabaseInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $db;

	/**
	 * Content Component router factory constructor
	 *
	 * @param   Category           $category  The category object
	 * @param   DatabaseInterface  $db        The database object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Category $category, DatabaseInterface $db)
	{
		$this->category = $category;
		$this->db       = $db;
	}

	/**
	 * Creates a router.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  RouterInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface
	{
		return new ContentRouter($application, $menu, $this->category, $this->db);
	}
}
