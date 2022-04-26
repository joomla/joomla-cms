<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */


use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgSystemHeaderHierarchyCorrector extends CMSPlugin
{

	public function __construct(& $subject, $config) {

		$this->loadLanguage('plg_system_headerhierarchycorrector');
		parent::__construct($subject, $config);
	}

	public function isActive() {

		$app = Factory::getApplication();

		if ($app->getName() === 'site') {
			return true;
		}

		return false;
	}

	function onBeforeRender() {

		$active = $this->isActive();

		if (!$active) {
			return '';
		}

		HTMLHelper::_('script', 'media/plg_system_headerhierarchycorrector/js/config-header-corrector.es6.js', array('version' => 'auto'));

		return true;
	}

}
