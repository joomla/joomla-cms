<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Admin;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;

/**
 * Installer Update Sites Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       3.4
 */
class Updatesites extends Admin
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MvcFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since  1.6
	 * @see    \JControllerLegacy
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('rebuild',   'rebuild');
	}

	/**
	 * Rebuild update sites tables.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public function rebuild()
	{
		// Check for request forgeries.
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		// Rebuild the update sites.
		$this->getModel('updatesites')->rebuild();

		$this->setRedirect(\JRoute::_('index.php?option=com_installer&view=updatesites', false));
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\Model\Model  The model.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Updatesite', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
