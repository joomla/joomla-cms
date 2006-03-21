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

jimport('pattemplate.patTemplate');

/**
 * Template class, provides an easy interface to parse and display a template file
 *
 * @author 		Johan Janssens <johan@joomla.be>
 * @package 	Joomla.Framework
 * @subpackage 	Template
 * @since		1.1
 * @see			patTemplate
 */

class JTemplate extends patTemplate
{
	/**
     * The path of the template file
     *
     * @var       string
     * @access    private
     */
	var $_file = '';
	
	
	/**
	 * A hack to support __construct() on PHP 4
	 * Hint: descendant classes have no PHP4 class_name() constructors,
	 * so this constructor gets called first and calls the top-layer __construct()
	 * which (if present) should call parent::__construct()
	 *
	 * @return Object
	 */
	function JTemplate()
	{
		$args = func_get_args();
		register_shutdown_function(array(&$this, '__destruct'));
		call_user_func_array(array(&$this, '__construct'), $args);
	}
	
	/**
	* Class constructor
	*
	* The constructor accepts the type of the templates as sole parameter.
	* You may choose one of:
	* - html (default)
	* - tex
	*
	* The type influences the tags you are using in your templates.
	*
	* @access	protected
	* @param	string	$type (either html or tex)
	*/
	function __construct($type = 'html') 
	{
		parent::patTemplate($type);
		
		//set the namespace
		$this->setNamespace( 'jtmpl' );

		//add module directories
		$this->addModuleDir('Function',		dirname(__FILE__). DS. 'module'. DS .'function');
		$this->addModuleDir('Modifier', 	dirname(__FILE__). DS. 'module'. DS .'modifier');

		//set root template directory
		$this->setRoot( dirname(__FILE__). DS. 'tmpl' );
	}

	/**
	 * Returns a reference to a global Template object, only creating it
	 * if it doesn't already exist.
	 *
	* @param	string	$type (either html or tex)
	* @return jtemplate A template object
	* @since 1.1
	*/
	function &getInstance( $type = 'html' ) {
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		$signature = serialize(array($type));

		if (empty($instances[$signature])) {
			$instances[$signature] = new JTemplate($type);
		}

		return $instances[$signature];
	}

	/**
	 * Parse a file
	 *
	 * @access public
	 * @param string 	$file	The filename
	 */
	function parse( $file )
	{	
		$this->_file = $file; //store the file for later usage
		$this->readTemplatesFromInput( $file );
	}

	/**
	 * Execute and display a the template
	 *
	 * @access public
	 * @param string 	$name		The name of the template
	 */
	function display( $name )
	{
		$this->displayParsedTemplate( $name );
	}
	
	/**
	 * Returns a parsed template
	 *
	 * @access public
	 * @param string 	$name		The name of the template
	 */
	function fetch( $name ) 
	{
		$result = $this->getParsedTemplate($name, true);

		/**
		 * error happened
		 */
		if (patErrorManager::isError($result)) {
			return $result;
		}
		
		return $result;
	}
	
   /**
	* enable a template cache
	*
	* A template cache will improve performace, as the templates
	* do not have to be read on each request.
	*
	* @access	public
	* @param	string		name of the template cache
	* @param	string		folder to store the cached files
	* @return	boolean		true on success, patError otherwise
	*/
	function enableTemplateCache( $handler, $folder )
	{
		$info = array(
			'cacheFolder' 	=> $folder,
			'lifetime' 		=> 'auto',
			'prefix'		=> 'global__',
			'filemode' 		=> 0755
		);
		$result = $this->useTemplateCache( 'File', $info );
		
		return $result;
	}

	/**
	 * Set the prefix of the template cache
	 *
	 * @access	public
	 * @param	string		the prefix of the template cache
	 * @return	boolean		true on success, patError otherwise
	 */
	function setTemplateCachePrefix( $prefix ) 
	{
		if (!$this->_tmplCache) {
			return false;
		}

		$this->_tmplCache->_params['prefix'] = $prefix;
		return true;
	}
}
?>