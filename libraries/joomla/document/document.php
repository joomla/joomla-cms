<?php
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

jimport('joomla.system.object');

/**
 * Document class, provides an easy interface to parse and display a document
 *
 * The class is closely coupled with the JTemplate placeholder function.
 *
 * @author Johan Janssens <johan@joomla.be>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */

class JDocument extends JObject
{
	/**
     * The patTemplate object
     *
     * @var       object
     * @access    private
     */
	var $_tmpl		   = null;

	/**
	 * Constructor
	 * 
	 * @access protected
	 */
	function __construct()
	{

	}

	/**
	 * Returns a reference to the global JDocument object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $document = &JDocument::getInstance();</pre>
	 *
	 * @param type $type The document type to instantiate
	 * @access public
	 * @return jdocument  The document object.
	 */
	function &getInstance($type = 'html')
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		$signature = serialize(array($type));

		if (empty($instances[$signature])) {
			jimport('joomla.document.adapters.'.$type);
			$adapter = 'JDocument'.$type;
			$instances[$signature] = new $adapter();
		}

		return $instances[$signature];
	}

	/**
	 * Parse a file and create an internal patTemplate object
	 *
	 * @access public
	 * @param string 	$directory	The directory to look for the file
	 * @param string 	$filename	The actual filename
	 */
	function parse($directory, $filename = 'index.php')
	{
		$this->_tmpl =& $this->_load($directory, $filename);
	}

	/**
	 * Execute and display a layout script.
	 *
	 * @access public
	 * @param string 	$name		The name of the template
	 * @param boolean 	$compress	If true, compress the output using Zlib compression
	 */
	function display($name, $compress = true)
	{
		$this->_tmpl->display( $name, $compress );
	}

	/**
	 * Create a patTemplate object
	 *
	 * @param string 	$template	The name of the template 
	 * @param string 	$filename	The actual filename
	 * @return patTemplate
	 */
	function &_load($template, $filename) 
	{
		global $mainframe, $my, $acl, $database;
		global $Itemid, $task;
		
		$tmpl = null;
		if ( file_exists( 'templates'.DS.$directory.DS.$file ) ) {

			jimport('joomla.template.template');

			$tmpl =& JTemplate::getInstance();
			$tmpl->setNamespace( 'jdoc' );
			
			$tmpl->addGlobalVar( 'template', $template);

			ob_start();
			?><jdoc:tmpl name="<?php echo $filename ?>" autoclear="yes"><?php
				require_once( 'templates'.DS.$template.DS.$filename );
			?></jdoc:tmpl><?php
			$contents = ob_get_contents();
			ob_end_clean();

			$tmpl->readTemplatesFromInput( $contents, 'String' );
		}

		return $tmpl;
	}
}
?>