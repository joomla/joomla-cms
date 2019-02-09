<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\Exception;

defined('JPATH_PLATFORM') or die;

/**
 * Exception class defining an Unknown Asset
 *
 * @since  __DEPLOY_VERSION__
 */
class UnknownAssetException extends \RuntimeException implements WebAssetExceptionInterface
{
	/**
	 * UnknownAssetException constructor.
	 *
	 * @param   string      $assetName  The asset name.
	 * @param   int         $code       The Exception code.
	 * @param   \Throwable  $previous   The previous throwable used for the exception chaining.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(string $assetName, int $code = 0, \Throwable $previous = null)
	{
		$message = 'Unknown asset "' . $assetName . '"';

		parent::__construct($message, $code, $previous);
	}
}
