<?PHP
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Document head Renderer
*
* @author Johan Janssens <johan@joomla.be>
* @subpackage JDocument
* @since 1.1
*/

class patTemplate_Renderer_Head extends patTemplate_Renderer
{
   /**
	* name of the renderer
	* @access	private
	* @var		string
	*/
	var $_name	=	'Head';
	
   /**
	 * Renders the document head and returns the results as a string
	 *
	 * @access public
	 * @param string 	$name		(unused)
	 * @param array 	$params		Associative array of values
	 * @return string	The output of the script
	 */
	function render( $head = null, $params = array() )
	{
		ob_start();
		
		echo $this->_tmpl->fetchHead();

		//load editor
		initEditor();

		$contents = ob_get_contents();
		ob_end_clean();

        return $contents;
	}
}
?>