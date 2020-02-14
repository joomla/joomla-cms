<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

require_once JPATH_COMPONENT_ADMINISTRATOR .'/builder/classes/ajax.php';
if(!class_exists('SppagebuilderHelperSite')) {
	require_once JPATH_ROOT . '/components/com_sppagebuilder/helpers/helper.php';
}

$user = JFactory::getUser();
$app  = JFactory::getApplication();

$authorised = $user->authorise('core.edit', 'com_sppagebuilder') || ($user->authorise('core.edit.own', 'com_sppagebuilder') && ($this->item->created_by == $user->id));
if ($authorised !== true)
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
	$app->setHeader('status', 403, true);

	return false;
}

SppagebuilderHelperSite::loadLanguage();

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );
$input = JFactory::getApplication()->input;
$action = $input->get('callback', '', 'STRING');

// all settings loading
if ( $action === 'addon' ) {
	require_once JPATH_COMPONENT . '/parser/addon-parser.php';

	$post_data = $_POST['addon'];
	$addon = json_decode(json_encode($post_data));

	$addon_name = $addon->name;
	$class_name = 'SppagebuilderAddon' . ucfirst($addon_name);
	$addon_path = AddonParser::getAddonPath( $addon_name );

	$addon_options = array();
	if((!isset($addon->type) || $addon->type !== 'inner_row') && isset($addon_list[$addon->name]['attr']) && $addon_list[$addon->name]['attr']) {
		$addon_groups = $addon_list[$addon->name]['attr'];
		if (is_array($addon_groups)) {
		foreach ($addon_groups as $addon_group) {
			$addon_options += $addon_group;
		}
		}
	}

	foreach ($addon->settings as $key => &$setting) {
		if (isset($setting->md)) {
			$md = isset($setting->md) ? $setting->md : "";
			$sm = isset($setting->sm) ? $setting->sm : "";
			$xs = isset($setting->xs) ? $setting->xs : "";
			$setting = $md;
			$addon->settings->{$key . '_sm'} = $sm;
			$addon->settings->{$key . '_xs'} = $xs;
		}

		if(isset($addon_options[$key]['selector'])) {
			$addon_selector = $addon_options[$key]['selector'];
			if(isset($addon->settings->{$key}) && !empty($addon->settings->{$key})) {
				$selector_value = $addon->settings->{$key};
				$addon->settings->{$key . '_selector'} = str_replace('{{ VALUE }}', $selector_value, $addon_selector);
			}
		}

		// Repeatable
		if( (!isset($addon->type) || $addon->type !== 'inner_row') &&  (($key == 'sp_'. $addon->name .'_item') || ($key == $addon->name .'_item')) ) {
			if(count((array) $setting)) {
				foreach ($setting as &$options) {
				foreach ($options as $key2 => &$opt) {

					if (isset($opt->md)) {
						$md = isset($opt->md) ? $opt->md : "";
						$sm = isset($opt->sm) ? $opt->sm : "";
						$xs = isset($opt->xs) ? $opt->xs : "";
						$opt = $md;
						$options->{$key2 . '_sm'} = $sm;
						$options->{$key2 . '_xs'} = $xs;
					}

					if(isset($addon_options[$key]['attr'][$key2]['selector'])) {
						$addon_selector = $addon_options[$key]['attr'][$key2]['selector'];
						if(isset($options->{$key2}) && !empty($options->{$key2})) {
							$selector_value = $options->{$key2};
							$options->{$key2 . '_selector'} = str_replace('{{ VALUE }}', $selector_value, $addon_selector);
						}
					}

				}
				}
			}
		}
	}

	$output = '';

	require_once $addon_path.'/site.php';

	$assets = array();
	$css = JLayoutHelper::render('addon.css', array('addon' => $addon));

	if ( class_exists( $class_name ) ) {
			$addon_obj = new $class_name($addon);  // initialize addon class
			$addon_output = $addon_obj->render();

			// css
			if (method_exists($class_name, 'css')) {
				$css .= $addon_obj->css();
			}

			// js
			if (method_exists($class_name, 'js')) {
					$assets['js'] = $addon_obj->js();
			}

	} else {
		$addon_output = AddonParser::spDoAddon( AddonParser::generateShortcode($addon, 0, 0));
	}

	if($css) {
		$assets['css'] = $css;
	}
	
	if(!empty($addon_output)){
		$output .= JLayoutHelper::render('addon.start', array('addon' => $addon)); // start addon
		$output .= $addon_output;
		$output .= JLayoutHelper::render('addon.end'); // end addon
	}

	echo json_encode(array('html' => htmlspecialchars_decode($output), 'status' => 'true', 'assets' => $assets )); die;
}

if ( $action === 'get-page-data' ) {
	$page_path = $_POST['pagepath'];
	if ( JFile::exists( $page_path ) ) {
		$content = file_get_contents( $page_path );
		if (is_array(json_decode($content))) {
			require_once JPATH_COMPONENT_ADMINISTRATOR . '/builder/classes/addon.php';
			$content = SpPageBuilderAddonHelper::__($content, true);
			//$content = SpPageBuilderAddonHelper::getFontendEditingPage($content);

			echo json_encode(array('status'=>true, 'data'=>$content)); die;
		}
	}

	echo json_encode(array('status'=>false, 'data'=>'Something worng there.')); die;
}

// all settings loading
if ( $action === 'setting_value' ) {
	require_once JPATH_COMPONENT_ADMINISTRATOR .'/builder/classes/base.php';
	require_once JPATH_COMPONENT_ADMINISTRATOR .'/builder/classes/config.php';

	$addon_name = $_POST['name'];
	$addon_id = $_POST['id'];
	SpPgaeBuilderBase::loadSingleAddon( $addon_name );
	$form_fields = SpAddonsConfig::$addons;

	$first_attr = current($form_fields[$addon_name]['attr']);
	$options = SpPgaeBuilderBase::addonOptions();

	if(isset($first_attr['type']) && !is_array($first_attr['type'])){
		$newArry['general'] = $form_fields[$addon_name]['attr'];
		$form_fields[$addon_name]['attr'] = $newArry;
	}

	// Merge style
	if(isset($form_fields[$addon_name]['attr']['style'])) {
		$options['style'] = array_merge($form_fields[$addon_name]['attr']['style'], $options['style']);
	}

	foreach ($options as $key => $option) {
		$form_fields[$addon_name]['attr'][$key] = $option;
	}

	$atts = $form_fields[$addon_name]['attr'];

	$settings = array();

	$atts_access = array();
	if(isset($atts['access']) && !empty($atts['access'])){
		$atts_access = $atts['access'];

		foreach($atts_access as $attr => $attr_values){
			if(isset($attr_values['std'])){
				$settings[$attr] = $attr_values['std'];
			}
		}
	}

	$atts_general = array();
	if(isset($atts['general']) && !empty($atts['general'])){
		$atts_general = $atts['general'];

		foreach($atts_general as $attr => $attr_values){
			if(isset($attr_values['std'])){
				$settings[$attr] = $attr_values['std'];
			}
		}
	}

	$atts_responsive = array();
	if(isset($atts['responsive']) && !empty($atts['responsive'])){
		$atts_responsive = $atts['responsive'];

		foreach($atts_responsive as $attr => $attr_values){
			if(isset($attr_values['std'])){
				$settings[$attr] = $attr_values['std'];
			}
		}
	}

	$atts_style = array();
	if(isset($atts['style']) && !empty($atts['style'])){
		$atts_style = $atts['style'];

		foreach($atts_style as $attr => $attr_values){
			if(isset($attr_values['std'])){
				$settings[$attr] = $attr_values['std'];
			}
		}
	}

	
	require_once JPATH_COMPONENT . '/parser/addon-parser.php';


	$addon = json_decode(json_encode(array('id' => $addon_id, 'name' => $addon_name, 'settings' => $settings)));


	$class_name = 'SppagebuilderAddon' . ucfirst($addon_name);
	$addon_path = AddonParser::getAddonPath( $addon_name );

	$output = '';
	
	require_once $addon_path.'/site.php';

	$assets = array();
	$css = JLayoutHelper::render('addon.css', array('addon' => $addon));

	if ( class_exists( $class_name ) ) {
			$addon_obj = new $class_name($addon);  // initialize addon class
			$addon_output = $addon_obj->render();

			// css
			if (method_exists($class_name, 'css')) {
					$css .= $addon_obj->css();
			}

			// js
			if (method_exists($class_name, 'js')) {
					$assets['js'] = $addon_obj->js();
			}

	} else {
		$addon_output = AddonParser::spDoAddon( AddonParser::generateShortcode($addon, 0, 0));
	}

	if($css) {
		$assets['css'] = $css;
	}
	
	if(!empty($addon_output)){
		$output .= JLayoutHelper::render('addon.start', array('addon' => $addon)); // start addon
		$output .= $addon_output;
		$output .= JLayoutHelper::render('addon.end'); // end addon
	}

	echo json_encode(array('formData' => json_encode($settings), 'html' => htmlspecialchars_decode($output), 'status' => 'true', 'assets' => $assets )); die;
}

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php';
