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

class Com_BetaInstallerScript {

	function install($parent) {
		echo '<p>'. JText::_('COM_BETA_16_CUSTOM_INSTALL_SCRIPT') . '</p>';
	}

	function uninstall($parent) {
		echo '<p>'. JText::_('COM_BETA_16_CUSTOM_UNINSTALL_SCRIPT') .'</p>';
	}

	function update($parent) {
		echo '<p>'. JText::_('COM_BETA_16_CUSTOM_UPDATE_SCRIPT') .'</p>';
	}

	function preflight($type, $parent) {
		JError::raiseWarning(100, JText::_('COM_BETA_RANDOM_MESSAGE'));
		return false;
		echo '<p>'. JText::sprintf('COM_BETA_16_CUSTOM_PREFLIGHT', $type) .'</p>';
	}

	function postflight($type, $parent) {
		echo '<p>'. JText::sprintf('COM_BETA_16_CUSTOM_POSTFLIGHT', $type) .'</p>';
	}
}
