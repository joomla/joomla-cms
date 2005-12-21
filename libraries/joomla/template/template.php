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
 * @author Johan Janssens <johan@joomla.be>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 * @see patTemplate
 */

class JTemplate extends patTemplate
{
	/**
	* Create a new JTemplate instance.
	*
	* The constructor accepts the type of the templates as sole parameter.
	* You may choose one of:
	* - html (default)
	* - tex
	*
	* The type influences the tags you are using in your templates.
	*
	* @access	public
	* @param	string	type (either html or tex)
	*/
	function JTemplate($type = 'html') 
	{
		parent::patTemplate($type);
		
		//add module directories
		$this->addModuleDir('Function', dirname(__FILE__). DS. 'functions');
		$this->addModuleDir('Modifier', dirname(__FILE__). DS. 'modifiers');
		$this->addModuleDir('OutputFilter', dirname(__FILE__). DS. 'filters');
		
		//set root template directory
		$this->setRoot( dirname(__FILE__). DS. 'tmpl' );
	}
	
	/**
	 * Set the prefix of the template cache
	 *
	 * @access	public
	 * @param	string		the prefix of the template cache
	 * @return	boolean		true on success, patError otherwise
	 */
	function setTemplateCachePrefix( $prefix ) {
		if (!$this->_tmplCache) {
			return false;
		}

		$this->_tmplCache->_params['prefix'] = $prefix;
		return true;
	}
}
?>