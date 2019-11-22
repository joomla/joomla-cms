<?php
/**
 * @package     Joomla.API
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The actionlogs controller
 *
 * @since  4.0.0
 */
class ActionlogsController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'actionlogs';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $default_view = 'actionlogs';
}
