<?php
/**
 * Document Description
 *
 * Document Long Description
 *
 * PHP4/5
 *
 * Created on Jul 21, 2008
 *
 * @package package_name
 * @author Your Name <author@toowoombarc.qld.gov.au>
 * @author Toowoomba Regional Council Information Management Branch
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2008 Toowoomba Regional Council/Developer Name
 * @version SVN: $Id$
 * @see http://joomlacode.org/gf/project/	JoomlaCode Project:
 */

class Mod_AlphaInstallerScript {

	function install($parent) {
		echo '<p>'. JText::_('1.6 Custom install script') . '</p>';
	}

	function uninstall($parent) {
		echo '<p>'. JText::_('1.6 Custom uninstall script') .'</p>';
	}

	function update($parent) {
		echo '<p>'. JText::_('1.6 Custom update script') .'</p>';
	}

	function preflight($type, $parent) {
		echo '<p>'. JText::sprintf('1.6 Preflight for %s', $type) .'</p>';
	}

	function postflight($type, $parent) {
		echo '<p>'. JText::sprintf('1.6 Postflight for %s', $type) .'</p>';
	}
}