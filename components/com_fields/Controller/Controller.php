<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Fields\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;

/**
 * Base controller class for Fields Component.
 *
 * @since  3.7.0
 */
class Controller extends \Joomla\CMS\Controller\Controller
{

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MvcFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.7.0
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null, $app = null, $input = null)
	{
		// Frontpage Editor Fields Button proxying:
		if ($input->get('view') === 'fields' && $input->get('layout') === 'modal')
		{
			// Load the backend language file.
			$app->getLanguage()->load('com_fields', JPATH_ADMINISTRATOR);

			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config, $factory, $app, $input);
	}
}
