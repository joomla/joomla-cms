<?php
/**
* @version $Id: Exists.php 2181 2006-02-01 22:18:02Z webImagery $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JDocument Placeholder function
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class patTemplate_Function_Exists extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'exists';

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
		$type = isset($params['type']) ? strtolower( $params['type'] ) : null;

		$result = '';
		switch($type)
		{
			case 'modules'  		:
			{
				$words = explode(' ', $params['condition']);
				for($i=0; $i < count($words); $i++)
				{
					if($i % 2 == 0)
					{
						//odd parts (modules)
						$name = strtolower($words[$i]);
						$words[$i] = count(JModuleHelper::getModules($name));
					}
				}

				$str = 'return '.implode(' ', $words).';';
				if(eval($str)) {
					$result = $content;
				}

			} break;
		}

		return $result;
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