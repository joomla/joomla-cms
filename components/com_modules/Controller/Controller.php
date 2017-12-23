<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Modules\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Controller as BaseController;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;

/**
 * Modules manager master display controller.
 *
 * @since  3.5
 */
class Controller extends BaseController
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
	 * @since   3.0
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null, $app = null, $input = null)
	{
		$this->input = \JFactory::getApplication()->input;

		// Modules frontpage Editor Module proxying:
		if ($this->input->get('view') === 'modules' && $this->input->get('layout') === 'modal')
		{
			\JHtml::_('stylesheet', 'system/adminlist.css', array('version' => 'auto', 'relative' => true));
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config, $factory, $app, $input);
	}
}
