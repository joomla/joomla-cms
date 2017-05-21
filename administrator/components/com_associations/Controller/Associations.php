<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Associations\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Admin;

/**
 * Associations controller class.
 *
 * @since  3.7.0
 */
class Associations extends Admin
{
	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 *
	 * @since  3.7.0
	 */
	protected $view_list = 'associations';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\Model\Model|bool
	 *
	 * @since  3.7.0
	 */
	public function getModel($name = 'Associations', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to purge the associations table.
	 *
	 * @return  void
	 *
	 * @since  3.7.0
	 */
	public function purge()
	{
		$this->getModel('associations')->purge();
		$this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	/**
	 * Method to delete the orphans from the associations table.
	 *
	 * @return  void
	 *
	 * @since  3.7.0
	 */
	public function clean()
	{
		$this->getModel('associations')->clean();
		$this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
}
