<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Menus manager master display controller.
 *
 * @since  3.7.0
 */
class DisplayController extends BaseController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.7.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		$this->input = Factory::getApplication()->input;

		// Menus frontpage Editor Menu proxying:
		if ($this->input->get('view') === 'items' && $this->input->get('layout') === 'modal')
		{
			HTMLHelper::_('stylesheet', 'system/adminlist.css', array(), true);
		}

		parent::__construct($config, $factory, $app, $input);
	}
}
