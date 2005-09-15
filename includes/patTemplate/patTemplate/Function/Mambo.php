<?php
/**
* @version $Id: Mambo.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class patTemplate_Function_Joomla extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Mambo';

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
		if( !isset( $params['macro'] ) ) {
            return false;
		}

        $macro = strtolower( $params['macro'] );
		//$page =& $GLOBALS['mainframe']->getPage();	// Joomla! 5.0 only

        switch ($macro) {

			case 'addtohead':
				$GLOBALS['mainframe']->addCustomHeadTag( $content );
				break;

        	case 'initeditor':	// Joomla! 5.0 only
        		return initEditor( true );
        		break;

        	case 'mainbody':	// Joomla! 5.0 only
        		return $page->showMainBody();
        		break;

        	case 'loadcomponent':	// Joomla! 5.0 only
        		// deprecated ??
				if( !isset( $params['component'] ) ) {
		            return false;
				} else {
					return $page->showComponent( $params['component'] );
				}
        		break;

			case 'hasmodules':	// Joomla! 5.0 only
				$position = mosGetParam( $params, 'position', '' );

				if ($page->countModules( $position ) > 0) {
					return $content;
				} else {
					return false;
				}
        		break;

         	case 'loadmodule':	// Joomla! 5.0 only
				$name = mosGetParam( $params, 'name', '' );
				$style = mosGetParam( $params, 'style', 0 );
				ob_start();
				$page->showModule( $name, $style );
				$html = ob_get_contents();
				ob_end_clean();
				return $html;
        		break;

        	case 'loadmodules':	// Joomla! 5.0 only
				$position = mosGetParam( $params, 'position', '' );
				$style = mosGetParam( $params, 'style', 0 );
				ob_start();
				$page->showModules( $position, $style );
				$html = ob_get_contents();
				ob_end_clean();
				return $html;
        		break;

        	case 'loadadminmodule':
				$position = mosGetParam( $params, 'position', '' );
				$style = mosGetParam( $params, 'style', 0 );
				ob_start();
				mosLoadAdminModule( $position, $style );
				$html = ob_get_contents();
				ob_end_clean();
				return $html;
        		break;

	       	case 'showhead':	// Joomla! 5.0 only
        		return $page->showHead();
        		break;

        	case 'pathway':	// Joomla! 5.0 only
				$Itemid = mosGetParam( $_REQUEST, 'Itemid', '' );
				ob_start();
				require $GLOBALS['_CONFIG']->SITEPATH . '/includes/pathway.php';
				$html = ob_get_contents();
				ob_end_clean();
				return $html;
        		break;
		}

		return false;
	}
}
?>