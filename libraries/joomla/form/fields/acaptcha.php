<?php
/**
 * @version		$Id: acaptcha.php 06/01/2012 11.06
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldAcaptcha extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Acaptcha';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		
		// Initialize some field attributes.
	 JPluginHelper::importPlugin( 'alikonweb' );
		$dispatcher =& JDispatcher::getInstance();

	if ((string) $this->element['size'] == 'mod') {
			$results = $dispatcher->trigger( 'onView2',array('com' ,'aa4j','.validate2','submitmodlogin','spanlogin') ); 
		} else {
	  $results = $dispatcher->trigger( 'onView2',array('com' ,'aa4j','.validate','pswregister','spanregister') ); 
	  }
		echo $results[0] ;
  
		return; 
	}
}
