<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Clients list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersControllerClients extends JControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_BANNERS_CLIENTS';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	 * @since  3.1
	 */
	protected $redirectUrl = 'index.php?option=com_banners&view=clients';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_banners';

	/*
	 * @var  string  Model name
	* @since  3.1
	*/
	protected $name = 'Client';

	/*
	 * @var  string   Model prefix
	* @since  3.1
	*/
	protected $prefix = 'BannersModel';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 * @deprecated  3.5
	 */
	public function getModel($name = 'Client', $prefix = 'BannersModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
