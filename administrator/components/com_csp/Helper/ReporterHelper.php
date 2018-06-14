<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Csp\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;

/**
 * Reporter component helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class ReporterHelper
{
	/**
	 * Gets the httpheaders system plugin extension id.
	 *
	 * @return  integer  The httpheaders system plugin extension id.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getHttpHeadersPluginId()
	{
		return PluginHelper::getPlugin('system', 'httpheaders')->id;
	}
}
