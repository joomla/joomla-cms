<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

\JLoader::register('TemplatesHelper', __DIR__ . '/helpers/templates.php');

use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_templates
 *
 * @since  __DEPLOY_VERSION__
 */
class TemplatesDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $namespace = 'Joomla\\Component\\Templates';
}
