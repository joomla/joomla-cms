<?php
/**
* @version $Id: Placeholder.php 1563 2005-12-27 20:09:40Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class patTemplate_Function_Placeholder extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'placeholder';
	
	/**
	* reference to the JDocument object that instantiated the module
	*
	* @access	protected
	* @var	object
	*/
	var	$_tmpl;

  
   /**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	*/
	function call( $params, $content )
	{
		$result = null;
		if( method_exists( $this->_tmpl, '_moduleCallback' ) )
		{
			$result = $this->_tmpl->_moduleCallback($this->_name, $params);
		}
		
		return $result;
		
		//$type = strtolower( $params['type'] );
		//unset($params['type']);
				
		//$result = $document->addPlaceholder($type, $params);
		//return $result;
	}
	
	 /**
	* set a reference to the JDocument object that instantiated the function
	*
	* @access	public
	* @param	object		JDocument object
	*/
	function setTemplateReference( &$tmpl )
	{
		$this->_tmpl = &$tmpl;
	}
}
?>