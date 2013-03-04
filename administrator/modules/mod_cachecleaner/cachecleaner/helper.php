<?php
/**
 * Module Helper File
 *
 * @package			Cache Cleaner
 * @version			2.2.0
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2013 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

class modCacheCleaner
{
	function modCacheCleaner()
	{
		// Load plugin parameters
		require_once JPATH_PLUGINS.'/system/nnframework/helpers/parameters.php';
		$parameters = NNParameters::getInstance();
		$this->params = $parameters->getPluginParams('cachecleaner');
	}

	function render()
	{
		if (!isset($this->params->display_link)) {
			return;
		}

		$app = JFactory::getApplication();

		// load the admin language file
		$lang = JFactory::getLanguage();
		if ($lang->getTag() != 'en-GB') {
			// Loads English language file as fallback (for undefined stuff in other language file)
			$lang->load('mod_cachecleaner', JPATH_ADMINISTRATOR, 'en-GB');
		}
		$lang->load('mod_cachecleaner', JPATH_ADMINISTRATOR, null, 1);

		JHtml::_('behavior.mootools');

		require_once JPATH_PLUGINS.'/system/nnframework/helpers/versions.php';
		$version = NoNumberVersions::getXMLVersion('cachecleaner', 'module', 1, 1);
		$nn_version = NoNumberVersions::getXMLVersion(null, null, null, 1);

		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true).'/plugins/system/nnframework/js/script.js'.$nn_version);
		$document->addStyleSheet(JURI::root(true).'/plugins/system/nnframework/css/status.css'.$nn_version);
		$script = "
			var cachecleaner_root = '".JURI::base(true)."';
			var cachecleaner_msg_clean = '".addslashes(html_entity_decode(JText::_('CC_CLEANING_CACHE')))."';
			var cachecleaner_msg_purge = '".addslashes(html_entity_decode(JText::_('CC_PURGING_CACHE')))."';
			var cachecleaner_msg_inactive = '".addslashes(html_entity_decode(JText::_('CC_SYSTEM_PLUGIN_NOT_ENABLED')))."';
			var cachecleaner_msg_success = '".addslashes(html_entity_decode(JText::_('CC_CACHE_CLEANED')))."';
			var cachecleaner_msg_failure = '".addslashes(html_entity_decode(JText::_('CC_CACHE_COULD_NOT_BE_CLEANED')))."';";
		$document->addScriptDeclaration($script);
		$document->addScript(JURI::base(true).'/modules/mod_cachecleaner/cachecleaner/js/script.js'.$version);
		$document->addStyleSheet(JURI::base(true).'/modules/mod_cachecleaner/cachecleaner/css/style.css'.$version);

		$text_ini = strtoupper(str_replace(' ', '_', $this->params->icon_text));
		$text = JText::_($text_ini);
		if ($text == $text_ini) {
			$text = JText::_($this->params->icon_text);
		}

		$class = 'cachecleaner_status nn_status';
		$ul_class = '';
		$template = $app->getTemplate();
		if (!(strpos($template, 'missioncontrol') === false)) {
			$class .= ' dropdown';
			$ul_class = 'mc-dropdown';
		}
		if ($this->params->display_link == 'text') {
			$class .= ' no_icon';
		} else if ($this->params->display_link == 'icon') {
			$class .= ' no_text';
		}
		$html = array();
		$html[] = '<span class="'.$class.'">';

		$hastip = $this->params->display_tooltip;
		if ($hastip) {
			JHtml::_('behavior.tooltip');
		}
		$name = ($this->params->display_link == 'icon') ? '&nbsp;' : $text;
		$html[] = modCacheCleaner::createLink('cleancache nn_status_link', $name, JText::_('CC_CLEAN_CACHE_DESC'), $hastip, JText::_('CACHE_CLEANER'), $ul_class);

		$links = array();
		$links[] = modCacheCleaner::createLink('cleancache', JText::_('CLEAN_CACHE'), JText::_('CC_CLEAN_CACHE_DESC'), $hastip);
		if ($this->params->show_purge) {
			$links[] = modCacheCleaner::createLink('purgecache', JText::_('CC_PURGE_CACHE'), JText::_('CC_PURGE_CACHE_DESC'), $hastip);
		}

		if (count($links) > 1) {
			if ($ul_class) {
				$html[] = '<ul class="'.$ul_class.'"><li>';
			} else {
				$html[] = '<div style="display: none;" class="nn_status_submenu"><ul class="'.$ul_class.'"><li>';
			}
			$html[] = implode('</li><li>', $links);
			if ($ul_class) {
				$html[] = '</li></ul>';
			} else {
				$html[] = '</li></ul></div>';
			}
		}
		$html[] = '</span>';

		echo implode('', $html);
	}

	function createLink($id, $name, $title, $tooltip = 1, $tooltip_name = '', $dropdown = 0)
	{
		if (!$tooltip_name) {
			$tooltip_name = $name;
		}

		$class = trim('nn_status_text'.($tooltip ? ' hasTip' : '').($dropdown ? ' select-active' : ''));
		$link = '<a href="javascript://" onclick="return false;" class="cachecleaner_'.$id.'">'
			.'<span class="'.$class.'" title="'.($tooltip ? $tooltip_name.'::' : '').$title.'">'.$name.'</span>';
		if ($dropdown) {
			$link .= '<span class="select-arrow">▾</span>';
		}
		$link .= '</a>';
		return $link;
	}
}