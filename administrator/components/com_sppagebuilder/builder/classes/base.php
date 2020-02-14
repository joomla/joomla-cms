<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );

class SpPgaeBuilderBase {

	private static function str_replace_first($from, $to, $subject) {
		$from = '/'.preg_quote($from, '/').'/';
		return preg_replace($from, $to, $subject, 1);
	}

	public static function loadInputTypes() {
		$types = JFolder::files( JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/types', '\.php$', false, true);
		foreach ($types as $type) {
			include_once $type;
		}
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

	// Load addons list
	public static function loadAddons() {

		require_once JPATH_ROOT . '/components/com_sppagebuilder/addons/module/admin.php';

		$template_path = JPATH_ROOT . '/templates/' . self::getTemplateName(); // current template path
		$tmpl_folders = array();

		if ( file_exists($template_path . '/sppagebuilder/addons') ) {
			$tmpl_folders = JFolder::folders($template_path . '/sppagebuilder/addons');
		}

		$folders = JFolder::folders(JPATH_ROOT .'/components/com_sppagebuilder/addons');

		if($tmpl_folders) {
			$merge_folders = array_merge( $folders, $tmpl_folders );
			$folders = array_unique( $merge_folders );
		}

		if ( count( (array)  $folders ) ) {
			foreach ( $folders as $folder ) {
				$tmpl_file_path = $template_path . '/sppagebuilder/addons/'.$folder.'/admin.php';
				$com_file_path = JPATH_ROOT . '/components/com_sppagebuilder/addons/'.$folder.'/admin.php';

				if($folder!='module') {
					if(file_exists( $tmpl_file_path )) {
						require_once $tmpl_file_path;
					} else if( file_exists( $com_file_path )) {
						require_once $com_file_path;
					}
				}
			}
		}

		self::loadPluginsAddons();
	}

	public static function loadSingleAddon( $name = '' ) {
		if (!$name) return;

		$name = self::str_replace_first('sp_', '', $name);
		$template_path = JPATH_ROOT . '/templates/' . self::getTemplateName(); // current template path
		$tmpl_addon_path = $template_path . '/sppagebuilder/addons/'. $name .'/admin.php';
		$com_addon_path = JPATH_ROOT . '/components/com_sppagebuilder/addons/'. $name .'/admin.php';

		$plugins = self::getPluginsAddons();

		if(file_exists( $tmpl_addon_path )) {
			require_once $tmpl_addon_path;
		} else if( file_exists( $com_addon_path )) {
			require_once $com_addon_path;
		} else {
			// Load from plugin
			if(isset($plugins[$name]) && $plugins[$name]) {
				require_once $plugins[$name];
			}
		}
	}

	// Load addons from plugins
	private static function loadPluginsAddons() {
		$path = JPATH_PLUGINS . '/sppagebuilder';
		if(!JFolder::exists($path)) return;

		$plugins = JFolder::folders($path);
		if(!count((array) $plugins)) return;

		foreach ($plugins as $plugin) {
			if(JPluginHelper::isEnabled('sppagebuilder', $plugin)) {
				$addons_path = $path . '/' . $plugin . '/addons';
				if(JFolder::exists($addons_path)) {
					$addons = JFolder::folders($addons_path);
					foreach ($addons as $addon) {
						$admin_file = $addons_path . '/' . $addon . '/admin.php';
						if(JFile::exists($admin_file)) {
							require_once $admin_file;
						}
					}
				}
			}
		}
	}

	// Get list of plugin addons
	private static function getPluginsAddons() {
		$path = JPATH_PLUGINS . '/sppagebuilder';
		if(!JFolder::exists($path)) return;

		$plugins = JFolder::folders($path);
		if(!count((array) $plugins)) return;

		$elements = array();
		foreach ($plugins as $plugin) {
			if(JPluginHelper::isEnabled('sppagebuilder', $plugin)) {
				$addons_path = $path . '/' . $plugin . '/addons';
				if(JFolder::exists($addons_path)) {
					$addons = JFolder::folders($addons_path);
					foreach ($addons as $addon) {
						$admin_file = $addons_path . '/' . $addon . '/admin.php';
						if(JFile::exists($admin_file)) {
							$elements[$addon] = $admin_file;
						}
					}
				}
			}
		}

		return $elements;
	}

	public static function addonOptions(){
		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/settings/addon.php';
		return $addon_global_settings;
	}

	public static function getAddonCategories($addons){
		$categories = array();
		foreach ($addons as $addon) {
			if (isset($addon['category'])) {
				$categories[] = $addon['category'];
			}
		}

		$new_array = array_count_values($categories);

		$result[0]['name'] = 'All';
		$result[0]['count'] = count((array) $addons);
		if (count((array) $new_array)) {
			$i = 1;
			foreach ($new_array as $key => $row) {
				$result[$i]['name'] = $key;
				$result[$i]['count'] = $row;
				$i = $i + 1;
			}
		}

		return $result;
	}

	// Load CSS and JS files for all addons
	public static function loadAssets($addons){
		foreach ($addons as $key => $addon) {
			$class_name = 'SppagebuilderAddon' . ucfirst($key);
			$addon_path = AddonParser::getAddonPath( $key );

			if(class_exists($class_name)) {
				$obj = new $class_name($addon);

				// Scripts
				if ( method_exists( $class_name, 'scripts' ) ) {
					$scripts = $obj->scripts();
					if(count((array) $scripts)) {
						$doc = JFactory::getDocument();
						foreach ($scripts as $key => $script) {
							$doc->addScript($script);
						}
					}
				}

				// Stylesheets
				if ( method_exists( $class_name, 'stylesheets' ) ) {
					$stylesheets = $obj->stylesheets();
					if(count((array) $stylesheets)) {
						$doc = JFactory::getDocument();
						foreach ($stylesheets as $key => $stylesheet) {
							$doc->addStyleSheet($stylesheet);
						}
					}
				}

			}
		}
	}

	public static function getAddonPath( $addon_name = '') {
        $app = JFactory::getApplication();
        $template = $app->getTemplate();
        $template_path = JPATH_ROOT . '/templates/' . $template;
        $plugins = self::getPluginsAddons();

        if ( file_exists( $template_path . '/sppagebuilder/addons/' . $addon_name . '/site.php' ) ) {
            return $template_path . '/sppagebuilder/addons/' . $addon_name;
        } elseif ( file_exists( JPATH_ROOT . '/components/com_sppagebuilder/addons/'. $addon_name . '/site.php' ) ) {
            return JPATH_ROOT . '/components/com_sppagebuilder/addons/'. $addon_name;
        } else {
            // Load from plugin
            if(isset($plugins[$addon_name]) && $plugins[$addon_name]) {
                return $plugins[$addon_name];
            }
        }
    }

	public static function getIconList(){
		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/settings/icon-font-awesome.php';
		return $icon_list;
	}

	public static function getAnimationsList(){
		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/settings/animation.php';
		return $animation_names;
	}

	public static function getAccessLevelList() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('a.id', 'value') . ', ' . $db->quoteName('a.title', 'label'))
			->from($db->quoteName('#__viewlevels', 'a'))
			->group($db->quoteName(array('a.id', 'a.title', 'a.ordering')))
			->order($db->quoteName('a.ordering') . ' ASC')
			->order($db->quoteName('title') . ' ASC');

		// Get the options.
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public static function getArticleCategories() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT a.id, a.title, a.level, a.published, a.lft');
		$subQuery = $db->getQuery(true)
			->select('id,title,level,published,parent_id,extension,lft,rgt')
			->from('#__categories')
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where( $db->quoteName('extension') . ' = ' . $db->quote('com_content') );

		$query->from('(' . $subQuery->__toString() . ') AS a')
			->join('LEFT', $db->quoteName('#__categories') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
		$query->order('a.lft ASC');

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$article_cats = array( 0 => array('value' => '', 'label' => JText::_('COM_SPPAGEBUILDER_ADDON_ARTICLE_ALL_CAT') ) );

		$j = 1;
		if(count((array) $categories)) {
			foreach ( $categories as $category ) {
				$article_cats[$j]['value'] = $category->id;
				$article_cats[$j]['label'] = str_repeat('- ', ($category->level -1) ) . $category->title;

				$j = $j + 1;
			}
		}

		return $article_cats;
	}

	public static function getModuleAttributes(){
		$moduleAttr = array();

		// Module Name and ID
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->select('id, title');
		$query->from('#__modules');
		$query->where('client_id = 0');
		$query->where('published = 1');
		$query->order('ordering, title');
		$db->setQuery($query);
		$modules = $db->loadObjectList();

		if (count((array) $modules)) {
			$moduleName = array();
			foreach ( $modules as  $key => $module ) {
				$moduleName[$key]['value'] = $module->id;
				$moduleName[$key]['label'] = $module->title;
			}
			$moduleAttr['moduleName'] = $moduleName;
		}

		// Module positions
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->select(array('position'))
			->from('#__modules')
			->where('client_id = 0')
			->where('published = 1')
			->group('position')
			->order('position ASC');
		$db->setQuery($query);
		$positions = $db->loadColumn();

		$template = self::getTemplateName();
		$templateXML = JPATH_SITE.'/templates/'.$template.'/templateDetails.xml';
		$template = simplexml_load_file( $templateXML );

		foreach($template->positions[0] as $position)  {
			$positions[] =  (string) $position;
		}

		$positions = array_unique($positions);

		if (count((array) $positions)) {
			$modulePoss = array();
			foreach ( $positions as $key => $position ) {
				$posArray['value'] = $position;
				$posArray['label'] = $position;
				array_push($modulePoss,$posArray);
			}
			$moduleAttr['modulePosition'] = $modulePoss;
		}

		return $moduleAttr;
	}

	public static function getRowGlobalSettings() {
		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/settings/row.php';
		return $row_settings;
	}

	public static function getColumnGlobalSettings() {
		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/settings/column.php';
		return $column_settings;
	}

	public static function getSettingsDefaultValue( $addon_attr = array() ) {
		$default = array();

		if ( !is_array($addon_attr) ){
			return array( 'default' => $default );
		}

		$sppbOneAddon = false;

		foreach ( $addon_attr as $key => $options ) {
			if ( isset( $options['type'] ) && !is_array($options['type']) ) {
				$sppbOneAddon = true;
				if($options['type'] == 'repeatable'){
					$default[$key] = self::repeatableFieldVal($options['attr']);
				} else if ( isset( $options['std'] ) ) {
					$default[$key] = $options['std'];
				}
			} else {
				foreach ( $options as $key => $option ) {
					if ( isset( $option['attr'] ) ) {
						$default[$key] = self::repeatableFieldVal($option['attr']);
					} else {
						if ( isset( $option['std'] ) ) {
							$default[$key] = $option['std'];
						}
					}
				}
			}
		}

		$newAddonAttr = array();

		$newAddonAttr['default'] = $default;
		if($sppbOneAddon){
			$newAddonAttr['attr'] = array('general'=> $addon_attr);
		}

		return $newAddonAttr;
	}

	public static function repeatableFieldVal( $option = array() ){
		$redefault = array();
		foreach ( $option as $rkey => $reOption) {
			if ( isset( $reOption['std'] ) ) {
				$redefault[0][$rkey] = $reOption['std'];
			}
		}
		return $redefault;
	}
}
