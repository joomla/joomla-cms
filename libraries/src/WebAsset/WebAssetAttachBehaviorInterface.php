<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;

/**
 * Web Asset Behavior interface
 *
 * @since  4.0.0
 */
interface WebAssetAttachBehaviorInterface
{
	/**
	 * Method called when asset attached to the Document.
	 * Useful for Asset to add a Script options.
	 *
	 * @param   Document  $doc  Active document
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	public function onAttachCallback(Document $doc);
}
