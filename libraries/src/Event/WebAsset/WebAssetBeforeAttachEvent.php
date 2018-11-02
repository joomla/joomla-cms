<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\WebAsset;

defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use Joomla\CMS\Document\Document;

/**
 * Event class for WebAsset events
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAssetBeforeAttachEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($name, array $arguments = array())
	{
		// Check for required arguments
		if (!array_key_exists('document', $arguments) || !($arguments['document'] instanceof Document))
		{
			throw new BadMethodCallException("Argument 'document' of event $name is not of the expected type");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Return target Document
	 *
	 * @return  Document
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAsset(): Document
	{
		return $this->arguments['document'];
	}
}
