<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Contenthistory\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Controller;

/**
 * Contenthistory list controller class.
 *
 * @since  3.2
 */
class Preview extends Controller
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the model
	 * @param   array   $config  An additional array of parameters
	 *
	 * @return  \Joomla\CMS\Model\Model  The model
	 *
	 * @since   3.2
	 */
	public function getModel($name = 'Preview', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
