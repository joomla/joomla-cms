<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Articles list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 * @since       1.6
 */
class ContactControllerContacts extends JControllerAdmincontent
{
	/*
	 * @var  string Model
	 */
	protected $name = 'Contact';

	/*
	 * @var  string   Model prefix
	 */
	protected $prefix = 'ContactModel';
	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $option = 'com_contact';

	/*
	 * @var  string  Dot separateed context without the key for featuring (generally option.model)
	*/
	protected $contextPrefix = 'com_contact.contact.';

	/*
	 * @var  string   Redirection url used after featuring items
	*/
	protected $redirectUrl = 'index.php?option=com_contact&view=contacts';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   string  $config  Array of configuration options
	 *
	 * @return  JModel
	 * @since   1.6
	 */
	public function getModel($name = 'Contact', $prefix = 'ContactModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

}
