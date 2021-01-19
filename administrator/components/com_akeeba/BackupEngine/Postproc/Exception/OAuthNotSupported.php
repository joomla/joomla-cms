<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Postproc\Exception;

defined('AKEEBAENGINE') || die();

/**
 * Indicates that the post-processing engine does not support OAuth2 or similar redirection-based authentication with
 * the remote storage provider.
 */
class OAuthNotSupported extends EngineException
{
	protected $messagePrototype = 'The %s post-processing engine does not support opening an authentication window to the remote storage provider.';
}
