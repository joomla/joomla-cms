<?php
/**
 * Document Description
 *
 * Document Long Description
 *
 * PHP4/5
 *
 * Created on Jul 7, 2008
 *
 * @package package_name
 * @author Your Name <author@example.com>
 * @author Author Name
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2009 Developer Name
 */

class Com_AlphaInstallerScript {

	function install($parent) {
		echo '<p>'. JText::_('COM_ALPHA_16_CUSTOM_INSTALL_SCRIPT') . '</p>';
	}

	function uninstall($parent) {
		echo '<p>'. JText::_('COM_ALPHA_16_CUSTOM_UNINSTALL_SCRIPT') .'</p>';
	}

	function update($parent) {
		echo '<p>'. JText::_('COM_ALPHA_16_CUSTOM_UPDATE_SCRIPT') .'</p>';
	}

	function preflight($type, $parent) {
		echo '<p>'. JText::sprintf('COM_ALPHA_16_CUSTOM_PREFLIGHT', $type) .'</p>';
	}

	function postflight($type, $parent) {
		echo '<p>'. JText::sprintf('COM_ALPHA_16_CUSTOM_POSTFLIGHT', $type) .'</p>';
		// An example of setting a redirect to a new location after the install is completed
		//$parent->getParent()->set('redirect_url', 'http://www.google.com');
	}
}
