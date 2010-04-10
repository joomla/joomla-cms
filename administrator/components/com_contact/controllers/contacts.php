<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContactControllerContacts extends JControllerAdmin
{
	protected $_context = 'com_contact';
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask('archive',		'publish');
		$this->registerTask('unpublish',	'publish');
		$this->registerTask('trash',		'publish');
		//$this->registerTask('report',		'publish');
		$this->registerTask('orderup',		'reorder');
		$this->registerTask('orderdown',	'reorder');
		$this->setURL('index.php?option=com_contact&view=contacts');
	}
	
	/**
	 * Proxy for getModel
	 */
	function &getModel($name = 'Contact', $prefix = 'ContactModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}