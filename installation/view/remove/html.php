<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Version as Jversion;

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Install Remove View
 *
 * @since  3.1
 */
class InstallationViewRemoveHtml extends JViewHtml
{
	public function render()
	{
		// Sample data

		// Available languages
		$version           = new Jversion();
		$altModel          = new InstallationModelChecks;
		$langModel         = new InstallationModelLanguages();

		$this->items       = $langModel->getItems();
		$this->development = $version->isInDevelopmentState();
		$this->phpoptions  = $altModel->getPhpOptions();
		$this->phpsettings = $altModel->getPhpSettings();

		return parent::render();
	}
}
