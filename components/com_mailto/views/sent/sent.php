<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage MailTo
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

require_once( JPATH_COM_MAILTO . '/view.php' );

/**
 * @package Joomla
 * @subpackage MailTo
 */
class JViewMailToSent extends JViewMailTo {
	/** @var string The view name */
	var $_viewName = 'sent';

	/**
	 * Display the view
	 */
	function display() {
		$controller = &$this->getController();

		$tmpl = $this->createTemplate( 'sent/tmpl/sent.html' );

		// Menu Parameters
		$mParams= $controller->getVar( 'mParams' );

		//$tmpl->addObject( 'body', $mParams->toObject(), 'param_' );
		//$tmpl->addVars( 'body', $data );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>