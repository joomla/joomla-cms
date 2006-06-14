<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.view');

/**
 * @pacakge Joomla
 * @subpackage Contacts
 */
class JContactView_Reply extends JView
{
	/**
	 * Name of the view.
	 * @access	protected
	 * @var		string
	 */
	var $_viewName = '_Reply';

	/**
	 * Display the document
	 */
	function display()
	{
		$cParams = &JComponentHelper::getControlParams();
		$template = JRequest::getVar( 'tpl', $cParams->get( 'template_name', 'default' ) );
		$template = preg_replace( '#\W#', '', $template );
		$tmplPath = dirname( __FILE__ ) . '/tmpl/' . $template . '.php';
		
		if (!file_exists( $tmplPath ))
		{
			$tmplPath = dirname( __FILE__ ) . '/tmpl/default.php';
		}

		require($tmplPath);
	}
}
?>