<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Model\Model;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Media View Model
 *
 * @since  __DEPLOY_VERSION__
 */
class Media extends Model
{
	/**
	 * Obtain list of supported providers
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getProviders()
	{
		$providerInfo = PluginHelper::getPlugin('filesystem');
		$results      = array();

		foreach ($providerInfo as $provider)
		{
			$params            = new Registry($provider->params);
			$info              = new \stdClass;
			$info->name        = $provider->name;
			$info->displayName = $params->get('display_name');
			$results[]         = $info;
		}

		return $results;
	}
}
