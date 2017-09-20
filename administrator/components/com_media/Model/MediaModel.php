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

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Media View Model
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaModel extends BaseModel
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
		PluginHelper::importPlugin('filesystem');
		$providerInfo = PluginHelper::getPlugin('filesystem');
		$adapterInfo  = \JFactory::getApplication()->triggerEvent('onFileSystemGetAdapters');
		$results      = array();

		for ($i = 0, $len = count($providerInfo); $i < $len; $i++)
		{
			$params            = new Registry($providerInfo[$i]->params);
			$info              = new \stdClass;
			$info->name        = $providerInfo[$i]->name;
			$info->displayName = $params->get('display_name');
			$adapters          = $adapterInfo[$i];

			for ($adapter = 0, $adapterCount = count($adapters); $adapter < $adapterCount; $adapter++)
			{
				$info->adapterNames[] = $adapters[$adapter]->getAdapterName();
			}

			$results[] = $info;
		}

		return $results;
	}
}
