<?php
/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The article controller
 *
 * @since  __DEPLOY_VERSION__
 */
class ArticlesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $contentType = 'articles';
}
