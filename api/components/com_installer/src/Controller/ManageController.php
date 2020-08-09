<?php
/**
 * @package     Joomla.API
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The manage controller
 *
 * @since  4.0.0
 */
class ManageController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'manage';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $default_view = 'manage';
}
