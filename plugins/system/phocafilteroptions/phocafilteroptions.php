<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */


use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgSystemPhocaFilteroptions extends CMSPlugin
{

	public function __construct(& $subject, $config) {

		$this->loadLanguage('plg_system_phocafilteroptions');
		parent::__construct($subject, $config);
	}

	public function isActive() {

		$app 					= Factory::getApplication();
		$option 				= $app->input->get('option', '', 'string');
		$view 					= $app->input->get('view', '', 'string');
		$layout 				= $app->input->get('layout', '', 'string');
		$component_edit_view 	= $this->params->get('component_edit_view', array());

		if ($app->getName() != 'administrator') {
			return false;
		}

		if ($option == 'com_config') {
			return true;
		}

		if ($layout == 'edit' && in_array($option, $component_edit_view)) {
			return true;
		}

		return false;

	}


	function onBeforeRender() {

		$active = $this->isActive();

		if (!$active) {
			return '';
		}

		$app 	= Factory::getApplication();
		$option = $app->input->get('option', '', 'string');
		$doc 	= Factory::getDocument();

        $oParams['option'] = $option;

        $doc->addScriptOptions('phParamsPFO', $oParams);

		//HTMLHelper::_('script', 'media/plg_system_phocafilteroptions/js/config-filter-options.es6.js', array('version' => 'auto'));
		HTMLHelper::_('script', 'media/plg_system_phocafilteroptions/js/config-filter-options.es6.min.js', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/plg_system_phocafilteroptions/css/filter-options.css', array('version' => 'auto'));

		return true;
	}

	function onAfterRender() {

		$active = $this->isActive();

		if (!$active) {
			return '';
		}

		$app 	= Factory::getApplication();
		$option = $app->input->get('option', '', 'string');

		// Add HTML
		$buffer = $app->getBody();

		if ($option == 'com_config') {
			$addHtml = '<div class="ph-filter-options config">'
					  .'<form class="form-inline input-append input-group"><input class="form-control" type="text" id="filterOptionsInput" placeholder="'.JText::_('PLG_SYSTEM_PHOCAFILTEROPTIONS_TYPE_FILTER_OPTIONS').'" /> <button type="button" id="filterOptionsClear" class="btn btn-primary" title="'. JText::_('JSEARCH_FILTER_CLEAR').'">'.JText::_('JSEARCH_FILTER_CLEAR').'</button></form>'
				  .'</div>';
		} else {
			$addHtml = '<div class="ph-filter-options component '.$option.'">'
					  .'<form class="form-inline input-append input-group"><input class="form-control" type="text" id="filterOptionsInput" placeholder="'.JText::_('PLG_SYSTEM_PHOCAFILTEROPTIONS_TYPE_FILTER_OPTIONS').'" /> <button type="button" id="filterOptionsClear" class="btn btn-primary" title="'. JText::_('JSEARCH_FILTER_CLEAR').'">'.JText::_('JSEARCH_FILTER_CLEAR').'</button></form>'
				  .'</div>';
		}

		// Use the easiest and quickest replace method
		$buffer	= str_replace("<form action=", $addHtml . "<form action=", $buffer);
		$app->setBody($buffer);

		return true;
	}
}
?>
