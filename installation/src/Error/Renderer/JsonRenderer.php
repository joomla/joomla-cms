<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Error\Renderer;

defined('_JEXEC') or die;

use Joomla\CMS\Error\AbstractRenderer;
use Joomla\CMS\Installation\Response\JsonResponse;

/**
 * JSON error page renderer for the installation application
 *
 * @since  4.0
 */
class JsonRenderer extends AbstractRenderer
{
	/**
	 * The format (type) of the error page
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $type = 'json';

	/**
	 * Render the error page for the given object
	 *
	 * @param   \Throwable  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	public function render(\Throwable $error): string
	{
		return json_encode(new JsonResponse($error));
	}
}
