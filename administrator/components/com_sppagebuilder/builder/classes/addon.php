<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');
require_once __DIR__ .'/base.php';
require_once __DIR__ .'/config.php';

$isSite = JFactory::getApplication()->isSite();
if ($isSite) {
	if(!class_exists('SppagebuilderHelper')) {
		require_once JPATH_ROOT . '/components/com_sppagebuilder/helpers/helper.php';
	}
	require_once JPATH_ROOT . '/components/com_sppagebuilder/parser/addon-parser.php';
}

class SpPageBuilderAddonHelper {

	public static function __( $json = '[]', $frontend = false ) {
		$datas  = json_decode($json);
		if (!count((array) $datas)) return $json;

		$uniqueId 	= strtotime('now');
		$first_row 	= $datas[0];

		if (!isset($first_row->id)) {
			foreach ($datas as &$row){
				self::rowFallback($row, $uniqueId);
				foreach ($row->columns as &$column){
					self::columnFallback($column, $uniqueId);
					foreach ($column->addons as &$addon){
						// Inner Row data regenerate
						if (isset($addon->type) && $addon->type == 'sp_row') {
							self::rowFallback($addon, $uniqueId, true);
							foreach ($addon->columns as &$column) {
								self::columnFallback($column, $uniqueId);
								foreach ($column->addons as &$addon) {
									self::addonFallback($addon, $uniqueId);
								}
							}
						} else {
							self::addonFallback($addon, $uniqueId);
						}
					}

				}
			}
		}

		// Frontend editing
		if($frontend) {
			return self::getFontendEditingPage(json_encode($datas));
		}

		return json_encode($datas);
	}

	// Row data regenerate for version < 2.0
	public static function rowFallback( &$row, &$id, $inner = false ) {
		$row->id = $id;
		$row->visibility = (isset($row->disable) && $row->disable)?'':1;

		if ($row->layout != '12'){
			$chars = str_split($row->layout);
			$row->layout = join(',',$chars);
		}
		$row->columns =  $row->attr;

		if (!$inner) {
			$row->collapse = '';
			$row->title = 'Row';
			unset($row->type);
			unset($row->disable);
		} else {
			$row->type = 'inner_row';
		}
		$id = $id + 1;
		unset($row->attr);
	}

	// Column data regenerate for version < 2.0
	public static function columnFallback( &$column, &$id ) {
		$column->id = $id;
		$column->addons = $column->attr;
		$column->visibility = 1;
		$column->class_name = str_replace('column-parent ','', $column->class_name);
		$id = $id + 1;

		unset($column->settings->sortableitem);
		unset($column->attr);
		unset($column->type);
	}

	// Addon data regenerate for version < 2.0
	public static function addonFallback( &$addon, &$id ) {
		$addon->id = $id;
		$addon->settings = $addon->atts;
		$addon->visibility = 1;

		if(count((array) $addon->scontent)) {

			$settings = array();
			foreach ( $addon->scontent as $ops ) {
				$settings[] = $ops->atts;
			}

			if(isset($form_fields[$addon->name]['attr']['repetable_item']['addon_name'])) {
				$addon->settings->{$form_fields[$addon->name]['attr']['repetable_item']['addon_name']} = $settings;
			} else if(isset($addon->scontent[0]->name) && $addon->scontent[0]->name) {
				$addon->settings->{$addon->scontent[0]->name} = $settings;
			}
		}

		$id = $id + 1;

		unset($addon->atts);
		unset($addon->scontent);
	}

	public static function getFontendEditingPage($page = []) {
		$datas  = json_decode($page);
		if ( !count((array) $datas) ) return $page;

		foreach ( $datas as &$row ){
			foreach ( $row->columns as &$column) {
				foreach ( $column->addons as &$addon ) {
					if (isset( $addon->type ) && ( $addon->type === 'sp_row' || $addon->type === 'inner_row' ) ) {
						foreach ( $addon->columns as &$column ) {
							foreach ( $column->addons as &$addon ) {
								$addon_data = self::getAddonContent($addon);
								if( !isset( $addon_data['jsTemplate'] ) ) {
									$addon->htmlContent = $addon_data['html'];
									$addon->assets = $addon_data['assets'];
								}
							}
						}
					} else {
						$addon_data = self::getAddonContent($addon);
						if( !isset( $addon_data['jsTemplate'])){
							$addon->htmlContent = $addon_data['html'];
							$addon->assets = $addon_data['assets'];
						}
					}
				}
			}
		}

		return json_encode( $datas );
	}

	public static function getAddonContent( $addon ) {

		$addon_name = $addon->name;
		$class_name = 'SppagebuilderAddon' . ucfirst( $addon_name );
		$addon_path = AddonParser::getAddonPath( $addon_name );

		$addonFullPath = $addon_path.'/site.php';

		if(!file_exists($addonFullPath)){
			return array(
				'html' => '<div class="remove-addon-notice"><h4>Addon files maybe remove or exists.</h4></div>',
				'assets' => ''
			);
		}

		require_once $addonFullPath;

		if ( class_exists( $class_name ) ) {
			if ( method_exists( $class_name, 'getTemplate' ) ) {
				return array( 'jsTemplate' => true );
			}
		}

		$addon_options = array();
		if((!isset($addon->type) || $addon->type !== 'inner_row') && isset($addon_list[$addon->name]['attr']) && $addon_list[$addon->name]['attr']) {
			$addon_groups = $addon_list[$addon->name]['attr'];
			if (is_array($addon_groups)) {
				foreach ($addon_groups as $addon_group) {
					$addon_options += $addon_group;
				}
			}
		}

		foreach ($addon->settings as $key => $setting) {
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
				foreach ($setting as $options) {
				foreach ($options as $key2 => $opt) {

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
		$output .= JLayoutHelper::render( 'addon.start', array( 'addon' => $addon ) ); // start addon

		$assets = array();
		$css = JLayoutHelper::render( 'addon.css', array( 'addon' => $addon ) );

		if ( class_exists( $class_name ) ) {
			$addon_obj  = new $class_name($addon);  // initialize addon class
			$output .= $addon_obj->render();

			if (method_exists($class_name, 'css')) {
				$css .= $addon_obj->css();
			}

			if (method_exists($class_name, 'js')) {
				$assets['js'] = $addon_obj->js();
			}
		} else {
			$output .= AddonParser::spDoAddon( AddonParser::generateShortcode($addon, 0, 0));
		}

		$output .= JLayoutHelper::render('addon.end'); // end addon

		if($css) {
			$assets['css'] = $css;
		}

		return array('html'=>$output, 'assets'=>$assets);
	}
}
