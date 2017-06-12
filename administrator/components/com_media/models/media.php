<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Model\Model;

/**
 * Media View Model
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaModelMedia extends Model
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
		$providerInfo = Joomla\CMS\Plugin\PluginHelper::getPlugin('filesystem');
		$results      = array();

		foreach ($providerInfo as $provider)
		{
			$params            = new Joomla\Registry\Registry($provider->params);
			$info              = new stdClass;
			$info->name        = $provider->name;
			$info->displayName = $params->get('display_name');
			$results[]         = $info;
		}

		return $results;
	}
}
