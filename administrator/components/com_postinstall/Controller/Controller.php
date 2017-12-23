<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Postinstall\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Controller as BaseController;

/**
 * Postinstall display controller
 *
 * @since  3.6
 */

class Controller extends BaseController
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'messages';
}
