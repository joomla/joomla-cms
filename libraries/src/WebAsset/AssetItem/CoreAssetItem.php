<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\AssetItem;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetItem;

/**
 * Web Asset Item class for Core asset
 *
 * @since  __DEPLOY_VERSION__
 */
class CoreAssetItem extends WebAssetItem
{
	/**
	 * Method called when asset attached to the Document
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAttachCallback(): void
	{
		// Add core and base uri paths so javascript scripts can use them.
		Factory::getApplication()->getDocument()->addScriptOptions(
			'system.paths',
			[
				'root' => Uri::root(true),
				'rootFull' => Uri::root(),
				'base' => Uri::base(true),
			]
		);

		HTMLHelper::_('form.csrf');
	}
}
