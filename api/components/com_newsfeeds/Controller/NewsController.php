<?php
/**
 * @package     Joomla.API
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Newsfeeds\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\ApiController;
/**
 * The newsfeeds controller
 *
 * @since  __DEPLOY_VERSION__
 */
class NewsfeedsController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $contentType = 'newsfeeds';
}
