<?PHP
/**
* @version $Id: component.php 1598 2005-12-31 14:40:48Z Jinx $
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
 * JDocument Component renderer
 *
 * @author		Johan Janssens <johan@joomla.be>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.1
 */
class patTemplate_Renderer_Component extends patTemplate_Renderer
{
   /**
	* name of the renderer
	* @access	private
	* @var		string
	*/
	var $_name	=	'Component';

   /**
	 * Renders a component script and returns the results as a string
	 *
	 * @access public
	 * @param string 	$component	The name of the component to render
	 * @param array 	$params	Associative array of values
	 * @return string	The output of the script
	 */
	function render( $component, $params = array() )
	{
		global $mainframe;
		global $Itemid, $task, $option, $id;
		
		$my 		=& $mainframe->getUser();
		$database   =& $mainframe->getDBO();
		$acl  		=& JFactory::getACL();

		//For backwards compatibility extract the config vars as globals
		$CONFIG = new JConfig();
		foreach (get_object_vars($CONFIG) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$GLOBALS[$name] = $v;
		}
		unset($CONFIG);

		$gid = $my->gid;

		$component = !isset($component) ? $option : $component;

		/*
		 * Check to see if component is enabled and get parameters
		 */
		$row = null;
		$query = 	"SELECT enabled, params" .
					"\n FROM `#__components`" .
					"\n WHERE `parent` = 0" .
					"\n AND `option` = '$component'" .
					"LIMIT 1";
		$database->setQuery($query);
		$database->loadObject($row);
		
		if (!is_object($row))
		{
			$row = new stdClass();
			$row->enabled	= false;
			$row->params	= null;
		}
		
		/*
		 * A static array of components that are always enabled
		 */
		$enabledList = array('com_content', 'com_frontpage', 'com_user', 'com_wrapper', 'com_registration');


		/*
		 * Is the component enabled?
		 */
		if ( $mainframe->isAdmin() || $row->enabled || in_array($component, $enabledList) ) {
			$file = substr( $component, 4 );
			$path = JPATH_BASE.DS.'components'.DS.$component;

			if(JFile::exists($path.DS.$file.'.php')) {
				$path = $path.DS.$file.'.php';
			} else {
				$path = $path.DS.'admin.'.$file.'.php';
			}

			$task 	= mosGetParam( $_REQUEST, 'task', '' );
			$ret 	= mosMenuCheck( $Itemid, $component, $task, $my->gid );

			/*
			 * Load the component paramters
			 */
			if ($row) {
				$params = new JParameter($row->params);
			}

			$content = '';
			ob_start();

			$msg = stripslashes(urldecode(JRequest :: getVar( 'josmsg' )));
			if (!empty($msg)) {
				echo "\n<div class=\"message\">$msg</div>";
			}

			if ($ret) {
				//load common language files
				$lang =& $mainframe->getLanguage();
				$lang->load($component);
				require_once $path;
			} else {
				mosNotAuth();
			}

			$contents = ob_get_contents();
			ob_end_clean();

			return $contents;
		} else {
			header( 'HTTP/1.0 404 Not Found' );
			/*
			 * If a template version exists load the custom 404 page, if not...
			 * load the system one.
			 */
			$custom = JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'404.php';
			$system	= JPATH_SITE . DS.'templates'.DS.'_system'.DS.'404.php';
			if (JFile::exists($custom))
			{
				require_once($custom);
			} else
			{
				require_once($system);
			}
			exit;
		}
	}
}
?>