<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonModule extends SppagebuilderAddons{

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		//Options
		$id = (isset($this->addon->settings->id) && $this->addon->settings->id) ? $this->addon->settings->id : 0;
		$module_type = (isset($this->addon->settings->module_type) && $this->addon->settings->module_type) ? $this->addon->settings->module_type : 'module';
		$position = (isset($this->addon->settings->position) && $this->addon->settings->position) ? $this->addon->settings->position : '';

		if((($module_type == 'position') && !$position) || (($module_type == 'module') && !$id)) {
			return;
		}

		$modules = self::getModules($module_type, $id, $position);

		if(count((array) $modules)) {

			$output = '<div class="sppb-addon sppb-addon-module ' . $class . '">';
			$output .= '<div class="sppb-addon-content">';
			$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';

			foreach ($modules as $module) {
				$file				= $module->module;
				$custom				= substr($file, 0, 4) == 'mod_' ?  0 : 1;
				$module->user		= $custom;
				$module->name		= $custom ? $module->title : substr($file, 4);
				$module->style		= null;
				$module->position	= strtolower($module->position);
				$clean[$module->id]	= $module;

				if($module_type == 'position') {
					$output .= JModuleHelper::renderModule($module, array('style' => 'sp_xhtml'));
				} else {
					$output .= JModuleHelper::renderModule($module, array('style' => 'none'));
				}

			}

			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return null;
	}

	// Get all modules
	private static function getModules($module_type = 'module', $id = 0, $position = '') {
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$groups		= implode(',', $user->getAuthorisedViewLevels());
		$lang 		= JFactory::getLanguage()->getTag();
		$clientId 	= (int) $app->getClientId();

		$db	= JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.id, m.title, m.module, m.position, m.ordering, m.content, m.showtitle, m.params');
		$query->from('#__modules AS m');
		$query->where('m.published = 1');

		if($module_type == 'position') {
			$query->where($db->quoteName('m.position') . ' = ' . $db->quote($position));
			$query->order('m.ordering ASC');
		} else {
			$query->where('m.id = ' . $id);
		}

		$date = JFactory::getDate();
		$now = $date->toSql();
		$nullDate = $db->getNullDate();
		$query->where('(m.publish_up = '.$db->Quote($nullDate).' OR m.publish_up <= '.$db->Quote($now).')');
		$query->where('(m.publish_down = '.$db->Quote($nullDate).' OR m.publish_down >= '.$db->Quote($now).')');

		$query->where('m.access IN ('.$groups.')');
		$query->where('m.client_id = '. $clientId);

		// Filter by language
		if ($app->isSite() && $app->getLanguageFilter()) {
			$query->where('m.language IN (' . $db->Quote($lang) . ',' . $db->Quote('*') . ')');
		}

		// Set the query
		$db->setQuery($query);
		return $db->loadObjectList();
	}

}
