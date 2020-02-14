<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');

jimport('joomla.form.formfield');

class JFormFieldPagebuilder extends JFormField
{
	protected	$type = 'Pagebuilder';

	protected function getInput() {

		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/classes/base.php';
		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/classes/config.php';

		$this->loadPageBuilderLanguage();

		JHtml::_('jquery.framework');
		JHtml::_('jquery.ui', array('core', 'sortable'));
		$doc = JFactory::getDocument();

		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/pbfont.css' );
		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/react-select.css' );
		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css' );
		$doc->addScript( JURI::root(true) . '/media/editors/tinymce/tinymce.min.js' );
		$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/script.js' );
		$doc->addScriptdeclaration('var pagebuilder_base="' . JURI::root() . '";');

		// Addon List Initialize
		SpPgaeBuilderBase::loadAddons();
		$fa_icon_list     = SpPgaeBuilderBase::getIconList(); // Icon List
		$animateNames     = SpPgaeBuilderBase::getAnimationsList(); // Animation Names
		$accessLevels     = SpPgaeBuilderBase::getAccessLevelList(); // Access Levels
		$article_cats     = SpPgaeBuilderBase::getArticleCategories(); // Article Categories
		$moduleAttr       = SpPgaeBuilderBase::getModuleAttributes(); // Module Postions and Module Lits
		$rowSettings      = SpPgaeBuilderBase::getRowGlobalSettings(); // Row Settings Attributes
		$columnSettings   = SpPgaeBuilderBase::getColumnGlobalSettings(); // Column Settings Attributes
  
		// Addon List
		$addons_list    = SpAddonsConfig::$addons;
  
		foreach ( $addons_list as $key => &$addon ) {
		  $default_value = SpPgaeBuilderBase::getSettingsDefaultValue($addon['attr']);
		  $addon['default'] = $default_value;
		}
  
		$row_default_value = SpPgaeBuilderBase::getSettingsDefaultValue($rowSettings['attr']);
		$rowSettings['default'] = $row_default_value;
  
		$column_default_value = SpPgaeBuilderBase::getSettingsDefaultValue($columnSettings['attr']);
		$columnSettings['default'] = $column_default_value;
  
		$global_attributes = SpPgaeBuilderBase::addonOptions();
		$doc->addScriptdeclaration('var addonsJSON=' . json_encode($addons_list) . ';');
  
		// Addon Categories
		$addon_cats = SpPgaeBuilderBase::getAddonCategories($addons_list);
		$doc->addScriptdeclaration('var addonCats=' . json_encode($addon_cats) . ';');
  
		// Global Attributes
		$doc->addScriptdeclaration('var globalAttr=' . json_encode( $global_attributes ) . ';');
		$doc->addScriptdeclaration('var faIconList=' . json_encode( $fa_icon_list ) . ';');
		$doc->addScriptdeclaration('var animateNames=' . json_encode( $animateNames ) . ';');
		$doc->addScriptdeclaration('var accessLevels=' . json_encode( $accessLevels ) . ';');
		$doc->addScriptdeclaration('var articleCats=' . json_encode( $article_cats ) . ';');
		$doc->addScriptdeclaration('var moduleAttr=' . json_encode( $moduleAttr ) . ';');
		$doc->addScriptdeclaration('var rowSettings=' . json_encode( $rowSettings ) . ';');
		$doc->addScriptdeclaration('var colSettings=' . json_encode( $columnSettings ) . ';');
		$doc->addScriptdeclaration('var sppbMediaPath=\'/images\';');

		$initialState = '[]';

		if(($this->value != '') && ($this->value != '[]')) {
			$initialState = $this->value;
		}

		$doc->addScriptdeclaration('var initialState='. $initialState .';');
		$doc->addScriptdeclaration('var boxLayout=1;');

		$conf   = JFactory::getConfig();
		$editor   = $conf->get('editor');
		if ($editor == 'jce') {
			require_once(JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php');
			wfimport('admin.models.editor');
		  	$editor = new WFModelEditor();
			$app = JFactory::getApplication();
		  	$settings = $editor->getEditorSettings();
		  	$app->triggerEvent('onBeforeWfEditorRender', array(&$settings));
			echo $editor->render($settings);
		}

		$output = '<div class="sp-pagebuilder-admin pagebuilder-module"><div id="sp-pagebuilder-page-tools" class="clearfix sp-pagebuilder-page-tools"></div><div class="sp-pagebuilder-sidebar-and-builder"><div id="sp-pagebuilder-section-lib" class="clearfix sp-pagebuilder-section-lib"></div><div id="container"></div></div></div>';

		$output .= '<input type="hidden" name="'. $this->name .'" id="'. $this->id .'" value="">';
		$output .= '<script type="text/javascript" src="' . JURI::base(true) . '/components/com_sppagebuilder/assets/js/engine.js"></script>';

		return $output;
	}

	private function loadPageBuilderLanguage() {
    $lang = JFactory::getLanguage();
    $lang->load('com_sppagebuilder', JPATH_ADMINISTRATOR, $lang->getName(), true);
    $lang->load('tpl_' . $this->getTemplate(), JPATH_SITE, $lang->getName(), true);
    require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/helpers/language.php';
  }

	private function getTemplate() {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('template')));
    $query->from($db->quoteName('#__template_styles'));
    $query->where($db->quoteName('client_id') . ' = '. $db->quote(0));
    $query->where($db->quoteName('home') . ' = '. $db->quote(1));
    $db->setQuery($query);
    return $db->loadResult();
  }
}
