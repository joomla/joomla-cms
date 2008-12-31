<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Template
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * JTemplate Translate function
 *
 * @package 	Joomla.Framework
 * @subpackage		Template
 * @since		1.5
 */
class patTemplate_Function_Translate extends patTemplate_Function
{
	/**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Translate';

	/**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	* Function modifed for Joomla!
	*/
	function call( $params, $content )
	{
		$escape = isset( $params['escape'] ) ? $params['escape'] : '';


		// just use the Joomla translation tool
		if( count( $params ) > 0 && key_exists( 'key', $params ) ) {
			$text = JText::_( $params['key'] );
		} else {
			$text = JText::_( $content );
		}

		if ($escape == 'yes' || $escape == 'true') {
			$text = addslashes( $text );
		}
		return $text;
	}
}