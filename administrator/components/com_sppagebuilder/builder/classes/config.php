<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SpAddonsConfig {

	public static $addons = array();

	private static function str_replace_first($from, $to, $subject) {
		$from = '/'.preg_quote($from, '/').'/';
		return preg_replace($from, $to, $subject, 1);
	}

	public static function addonConfig( $attributes ) {
		if (empty($attributes['addon_name']) || empty($attributes)) {
			return false;
		} else {
			$addon = self::str_replace_first('sp_', '', $attributes['addon_name']);

			$app = JFactory::getApplication();
			$com_option = $app->input->get('option','','STR');
			$com_view = $app->input->get('view','','STR');
			$com_id = $app->input->get('id',0,'INT');
			if($app->isAdmin() || ( $com_option == 'com_sppagebuilder' && $com_view == 'form' && $com_id)){
				if (!isset($attributes['icon']) || !$attributes['icon']) {
					$attributes['icon'] = self::getIcon($addon);
				}
			}

			if(is_array($attributes['attr'])) {
				if(!isset($attributes['attr']['general'])) {
					foreach ($attributes['attr'] as $key => $attr) {
						if(isset($attributes['attr'][$key]) && $attributes['attr'][$key]) {
							unset($attributes['attr'][$key]);
						}
						$attributes['attr']['general'][$key] = $attr;
					}
				}
			}

			self::$addons[$addon] = $attributes;
		}
	}

	public static function getIcon( $addon ) {

		$template_name = self::getTemplateName();
		$template_path = JPATH_ROOT . '/templates/' . $template_name . '/sppagebuilder/addons/' . $addon . '/assets/images/icon.png';
		$com_file_path = JPATH_ROOT . '/components/com_sppagebuilder/addons/' . $addon . '/assets/images/icon.png';

		if ( file_exists($template_path) ) {
			$icon = JURI::root(true) . '/templates/' . $template_name . '/sppagebuilder/addons/' . $addon . '/assets/images/icon.png';
		} else if ( file_exists($com_file_path) ) {
			$icon = JURI::root(true) . '/components/com_sppagebuilder/addons/' . $addon . '/assets/images/icon.png';
		} else {
			$icon = JURI::root(true) . '/administrator/components/com_sppagebuilder/assets/img/addon-default.png';
		}

		return $icon;

	}

	private static function getTemplateName() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('template')));
		$query->from($db->quoteName('#__template_styles'));
		$query->where($db->quoteName('client_id') . ' = ' . $db->quote('0'));
		$query->where($db->quoteName('home') . ' = ' . $db->quote('1'));
		$db->setQuery($query);

		return $db->loadObject()->template;
	}
}
