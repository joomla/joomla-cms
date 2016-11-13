<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Cms\Error\AbstractRenderer;

/**
 * JSON error page renderer for the installation application
 *
 * @since  4.0
 */
class InstallationErrorJson extends AbstractRenderer
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
	 * @param   \Throwable|\Exception  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	protected function doRender($error)
	{
		return json_encode(new InstallationResponseJson($error));
	}
}
