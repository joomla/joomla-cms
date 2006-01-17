<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Full DHTML Admnistrator Menus
* 
* @package Joomla
*/
class mosFullAdminMenu 
{
	/**
	* Show the menu
	* @param string The current user type
	*/
	function show( $usertype='' ) 
	{
		global $mainframe;

		$lang 	  		=& $mainframe->getLanguage();
		$acl 	  		=& JFactory::getACL();
		$database 		=& $mainframe->getDBO();
		$enableStats 	= $mainframe->getCfg('enable_stats');
		$caching		= $mainframe->getCfg('caching');

		// cache some acl checks
		$canCheckin			= $acl->acl_check( 'com_checkin', 'manage', 'users', $usertype );
		$canConfig 			= $acl->acl_check( 'com_config', 'manage', 'users', $usertype );

		$manageTemplates 	= $acl->acl_check( 'com_templates', 'manage', 'users', $usertype );
		$manageTrash 		= $acl->acl_check( 'com_trash', 'manage', 'users', $usertype );
		$manageMenuMan 		= $acl->acl_check( 'com_menumanager', 'manage', 'users', $usertype );
		$manageLanguages 	= $acl->acl_check( 'com_languages', 'manage', 'users', $usertype );
		$installModules 	= $acl->acl_check( 'com_installer', 'module', 'users', $usertype );
		$editAllModules 	= $acl->acl_check( 'com_modules', 'manage', 'users', $usertype );
		$installPlugins 	= $acl->acl_check( 'com_installer', 'plugin', 'users', $usertype );
		$editAllPlugins 	= $acl->acl_check( 'com_plugins', 'manage', 'users', $usertype );
		$installComponents 	= $acl->acl_check( 'com_installer', 'component', 'users', $usertype );
		$editAllComponents 	= $acl->acl_check( 'com_components', 'manage', 'users', $usertype );
		$canMassMail 		= $acl->acl_check( 'com_massmail', 'manage', 'users', $usertype );
		$canManageUsers 	= $acl->acl_check( 'com_users', 'manage', 'users', $usertype );

		$query = "SELECT a.id, a.title, a.name, COUNT( DISTINCT c.id ) AS numcat, COUNT( DISTINCT b.id ) AS numarc"
		. "\n FROM #__sections AS a"
		. "\n LEFT JOIN #__categories AS c ON c.section = a.id"
		. "\n LEFT JOIN #__content AS b ON b.sectionid = a.id AND b.state = -1"
		. "\n WHERE a.scope = 'content'"
		. "\n GROUP BY a.id"
		. "\n ORDER BY a.ordering"
		;
		$database->setQuery( $query );
		$sections = $database->loadObjectList();
		$nonemptySections = 0;
		if (count($sections) > 0) {
			foreach ($sections as $section) {
				if ($section->numcat > 0) {
					$nonemptySections++;
				}
			}
		}
		$menuTypes = mosAdminMenus::menutypes();
		?>
		<div id="myMenuID"></div>
		<script language="JavaScript" type="text/javascript">
		var myMenu =
		[
		<?php
	// Home Sub-Menu
?>			[null,'<?php echo JText::_( 'Home', true ); ?>','index2.php',null,'<?php echo JText::_( 'Control Panel', true ); ?>'],
			_cmSplit,
			<?php
	// Site Sub-Menu
?>			[null,'<?php echo JText::_( 'Site', true ); ?>',null,null,'<?php echo JText::_( 'Site Management', true ); ?>',
<?php
			if ($canConfig) {
?>				['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo JText::_( 'Global Configuration', true ); ?>','index2.php?option=com_config&hidemainmenu=1',null,'<?php echo JText::_( 'Configuration', true ); ?>'],
<?php
			}
			if ($manageLanguages) {
?>				['<img src="../includes/js/ThemeOffice/language.png" />','<?php echo JText::_( 'Language Manager', true ); ?>',null,null,'<?php echo JText::_( 'Manage languages', true ); ?>',
  					['<img src="../includes/js/ThemeOffice/language.png" />','<?php echo JText::_( 'Site Languages', true ); ?>','index2.php?option=com_languages&client=site',null,'<?php echo JText::_( 'Manage site languages', true ); ?>'],
   					['<img src="../includes/js/ThemeOffice/language.png" />','<?php echo JText::_( 'Administrator Languages', true ); ?>','index2.php?option=com_languages&client=administrator',null,'<?php echo JText::_( 'Manage admin languages', true ); ?>'],
   				],
<?php
			}
?>				['<img src="../includes/js/ThemeOffice/media.png" />','<?php echo JText::_( 'Media Manager', true ); ?>','index2.php?option=com_media',null,'<?php echo JText::_( 'Manage Media Files', true ); ?>'],
					['<img src="../includes/js/ThemeOffice/preview.png" />', '<?php echo JText::_( 'Preview', true ); ?>', null, null, '<?php echo JText::_( 'Preview', true ); ?>',
					['<img src="../includes/js/ThemeOffice/preview.png" />','<?php echo JText::_( 'In New Window', true ); ?>','<?php echo JURL_SITE; ?>/index.php','_blank','<?php echo JURL_SITE; ?>'],
					['<img src="../includes/js/ThemeOffice/preview.png" />','<?php echo JText::_( 'Inline', true ); ?>','index2.php?option=com_admin&task=preview',null,'<?php echo JURL_SITE; ?>'],
					['<img src="../includes/js/ThemeOffice/preview.png" />','<?php echo JText::_( 'Inline with Positions', true ); ?>','index2.php?option=com_admin&task=preview2',null,'<?php echo JURL_SITE; ?>'],
				],
				['<img src="../includes/js/ThemeOffice/globe1.png" />', '<?php echo JText::_( 'Statistics', true ); ?>', null, null, '<?php echo JText::_( 'Site Statistics', true ); ?>',
<?php
			if ($enableStats == 1) {
?>					['<img src="../includes/js/ThemeOffice/globe4.png" />', '<?php echo JText::_( 'Browser, OS, Domain', true ); ?>', 'index2.php?option=com_statistics', null, '<?php echo JText::_( 'Browser, OS, Domain', true ); ?>'],
  					['<img src="../includes/js/ThemeOffice/globe3.png" />', '<?php echo JText::_( 'Page Impressions', true ); ?>', 'index2.php?option=com_statistics&task=pageimp', null, '<?php echo JText::_( 'Page Impressions', true ); ?>'],
<?php
			}
?>					['<img src="../includes/js/ThemeOffice/search_text.png" />', '<?php echo JText::_( 'Search Text', true ); ?>', 'index2.php?option=com_statistics&task=searches', null, '<?php echo JText::_( 'Search Text', true ); ?>']
				],
<?php
			if ($manageTemplates) {
?>				['<img src="../includes/js/ThemeOffice/template.png" />','<?php echo JText::_( 'Template Manager', true ); ?>',null,null,'<?php echo JText::_( 'Change site template', true ); ?>',
  					['<img src="../includes/js/ThemeOffice/template.png" />','<?php echo JText::_( 'Site Templates', true ); ?>','index2.php?option=com_templates',null,'<?php echo JText::_( 'Change site template', true ); ?>'],
  					['<img src="../includes/js/ThemeOffice/template.png" />','<?php echo JText::_( 'Administrator Templates', true ); ?>','index2.php?option=com_templates&client=administration',null,'<?php echo JText::_( 'Change admin template', true ); ?>'],
  					_cmSplit,
  					['<img src="../includes/js/ThemeOffice/template.png" />','<?php echo JText::_( 'Module Positions', true ); ?>','index2.php?option=com_templates&task=positions',null,'<?php echo JText::_( 'Module Positions in Template', true ); ?>']
  				],
<?php
			}
			if ($manageTrash) {
?>				['<img src="../includes/js/ThemeOffice/trash.png" />','<?php echo JText::_( 'Trash Manager', true ); ?>','index2.php?option=com_trash',null,'<?php echo JText::_( 'Manage Trash', true ); ?>'],
<?php
			}
			if ($canManageUsers || $canMassMail) {
?>				['<img src="../includes/js/ThemeOffice/users.png" />','<?php echo JText::_( 'User Manager', true ); ?>','index2.php?option=com_users&task=view',null,'<?php echo JText::_( 'Manage users', true ); ?>'],
<?php
				}
?>			],
<?php
	// Menu Sub-Menu
?>			_cmSplit,
			[null,'<?php echo JText::_( 'Menu', true ); ?>',null,null,'<?php echo JText::_( 'Menu Management', true ); ?>',
<?php
			if ($manageMenuMan) {
?>				['<img src="../includes/js/ThemeOffice/menus.png" />','<?php echo JText::_( 'Menu Manager', true ); ?>','index2.php?option=com_menumanager',null,'<?php echo JText::_( 'Manage menu', true ); ?>'],
				_cmSplit,
<?php
			}
			foreach ( $menuTypes as $menuType ) {
?>				['<img src="../includes/js/ThemeOffice/menus.png" />','<?php echo $menuType;?>','index2.php?option=com_menus&menutype=<?php echo $menuType;?>',null,''],
<?php
			}
?>			],
			_cmSplit,
<?php
	// Content Sub-Menu
?>			[null,'<?php echo JText::_( 'Content', true ); ?>',null,null,'<?php echo JText::_( 'Content Management', true ); ?>',
<?php
			if (count($sections) > 0) {
?>				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo JText::_( 'Content by Section', true ); ?>',null,null,'<?php echo JText::_( 'Content Managers', true ); ?>',
<?php
				foreach ($sections as $section) {
					$txt = addslashes( $section->title ? $section->title : $section->name );
?>					['<img src="../includes/js/ThemeOffice/document.png" />','<?php echo $txt;?>', null, null,'<?php echo $txt;?>',
<?php
					if ($section->numcat) {
?>						['<img src="../includes/js/ThemeOffice/edit.png" />', '<?php echo JText::_( 'Items', true ); ?>', 'index2.php?option=com_content&sectionid=<?php echo $section->id;?>',null,null],
<?php
					}
?>						['<img src="../includes/js/ThemeOffice/add_section.png" />', '<?php echo JText::_( 'Add/Edit', true ); ?> <?php echo JText::_( 'Categories', true ); ?>', 'index2.php?option=com_categories&section=<?php echo $section->id;?>',null, null],
<?php
					if ($section->numarc) {
?>						['<img src="../includes/js/ThemeOffice/backup.png" />', '<?php echo $txt;?> <?php echo JText::_( 'Archive', true ); ?>', 'index2.php?option=com_content&task=showarchive&sectionid=<?php echo $section->id;?>',null,null],
<?php
					}
?>					],
<?php
				} // foreach
?>				],
				_cmSplit,
<?php
			}
?>
				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo JText::_( 'All Content Items', true ); ?>','index2.php?option=com_content&sectionid=0',null,'<?php echo JText::_( 'Manage Content Items', true ); ?>'],
  				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo JText::_( 'Static Content Manager', true ); ?>','index2.php?option=com_typedcontent',null,'<?php echo JText::_( 'Manage Typed Content Items', true ); ?>'],
  				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/add_section.png" />','<?php echo JText::_( 'Section Manager', true ); ?>','index2.php?option=com_sections&scope=content',null,'<?php echo JText::_( 'Manage Content Sections', true ); ?>'],
				['<img src="../includes/js/ThemeOffice/add_section.png" />','<?php echo JText::_( 'Category Manager', true ); ?>','index2.php?option=com_categories&section=content',null,'<?php echo JText::_( 'Manage Content Categories', true ); ?>'],
				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/home.png" />','<?php echo JText::_( 'Frontpage Manager', true ); ?>','index2.php?option=com_frontpage',null,'<?php echo JText::_( 'Manage Frontpage Items', true ); ?>'],
  				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo JText::_( 'Archive Manager', true ); ?>','index2.php?option=com_content&task=showarchive&sectionid=0',null,'<?php echo JText::_( 'Manage Archive Items', true ); ?>'],
			],
<?php
	// Components Sub-Menu
	if ($installComponents) {
?>			_cmSplit,
			[null,'<?php echo JText::_( 'Components', true ); ?>',null,null,'<?php echo JText::_( 'Component Management', true ); ?>',
<?php
		$query = "SELECT *"
		. "\n FROM #__components"
		. "\n WHERE name <> 'frontpage'"
		. "\n AND name <> 'media manager'"
		. "\n ORDER BY ordering, name"
		;
		$database->setQuery( $query );
		$comps = $database->loadObjectList();	// component list
		$subs = array();	// sub menus
		// first pass to collect sub-menu items
		foreach ($comps as $row) {
			if ($row->parent) {
				if (!array_key_exists( $row->parent, $subs )) {
					$subs[$row->parent] = array();
				}
				$subs[$row->parent][] = $row;
			}
		}
		$topLevelLimit = 19; //You can get 19 top levels on a 800x600 Resolution
		$topLevelCount = 0;
		foreach ($comps as $row) {
			if ($editAllComponents | $acl->acl_check( 'administration', 'edit', 'users', $usertype, 'components', $row->option )) {
				if ($row->parent == 0 && (trim( $row->admin_menu_link ) || array_key_exists( $row->id, $subs ))) {
					$topLevelCount++;
					if ($topLevelCount > $topLevelLimit) {
						continue;
					}
					$name = JText::_( $row->name, true );
					$name = addslashes( $name );
					$alt = addslashes( $row->admin_menu_alt );
					$link = $row->admin_menu_link ? "'index2.php?$row->admin_menu_link'" : "null";
					echo "\t\t\t\t['<img src=\"../includes/$row->admin_menu_img\" />','$name',$link,null,'$alt'";
					if (array_key_exists( $row->id, $subs )) {
						foreach ($subs[$row->id] as $sub) {
							echo ",\n";
        					$name = JText::_( $sub->name, true );
							$name = addslashes( $name );
							$alt = addslashes( $sub->admin_menu_alt );
							$link = $sub->admin_menu_link ? "'index2.php?$sub->admin_menu_link'" : "null";
							echo "\t\t\t\t\t['<img src=\"../includes/$sub->admin_menu_img\" />','$name',$link,null,'$alt']";
						}
					}
					echo "\n\t\t\t\t],\n";
				}
			}
		}
		if ($topLevelLimit < $topLevelCount) {
			echo "\t\t\t\t['<img src=\"../includes/js/ThemeOffice/sections.png\" />','". JText::_( 'More Components...', true ) ."','index2.php?option=com_admin&task=listcomponents',null,'<?php echo JText::_( 'More Components...', true ); ?>'],\n";
		}
?>
			],
<?php
	// Modules Sub-Menu
		if ($installModules | $editAllModules) {
?>			_cmSplit,
			[null,'<?php echo JText::_( 'Modules', true ); ?>',null,null,'<?php echo JText::_( 'Module Management', true ); ?>',
<?php
			if ($editAllModules) {
?>				['<img src="../includes/js/ThemeOffice/module.png" />', '<?php echo JText::_( 'Site Modules', true ); ?>', "index2.php?option=com_modules", null, '<?php echo JText::_( 'Manage Site modules', true ); ?>'],
				['<img src="../includes/js/ThemeOffice/module.png" />', '<?php echo JText::_( 'Administrator Modules', true ); ?>', "index2.php?option=com_modules&client=admin", null, '<?php echo JText::_( 'Manage Administrator modules', true ); ?>'],
<?php
			}
?>			],
<?php
		} // if ($installModules | $editAllModules)
	} // if $installComponents
	// Plugins Sub-Menu
	if ($installPlugins | $editAllPlugins) {
?>			_cmSplit,
			[null,'<?php echo JText::_( 'Plugins', true ); ?>',null,null,'<?php echo JText::_( 'Plugin Management', true ); ?>',
<?php
		if ($editAllPlugins) {
?>				['<img src="../includes/js/ThemeOffice/module.png" />', '<?php echo JText::_( 'Site Plugins', true ); ?>', "index2.php?option=com_plugins", null, '<?php echo JText::_( 'Manage Site Plugins', true ); ?>'],
<?php
		}
?>			],
<?php
	}
?>
<?php
	// Extensions Sub-Menu
	if ($installModules) {
?>			_cmSplit,
			[null,'<?php echo JText::_( 'Extensions', true ); ?>',null,null,'<?php echo JText::_( 'Extensions', true ); ?>',
				['<img src="../includes/js/ThemeOffice/install.png" />','<?php echo JText::_( 'Manage Extensions', true ); ?>','index2.php?option=com_installer',null,'<?php echo JText::_( 'Manage Extensions', true ); ?>'],
			],
<?php
	}
	// Messages Sub-Menu
	if ($canConfig) {
?>			_cmSplit,
  			[null,'<?php echo JText::_( 'Messages', true ); ?>',null,null,'<?php echo JText::_( 'Messaging Management', true ); ?>',
  				['<img src="../includes/js/ThemeOffice/messaging_inbox.png" />','<?php echo JText::_( 'Inbox', true ); ?>','index2.php?option=com_messages',null,'<?php echo JText::_( 'Private Messages', true ); ?>'],
  				['<img src="../includes/js/ThemeOffice/messaging_config.png" />','<?php echo JText::_( 'Configuration', true ); ?>','index2.php?option=com_messages&task=config&hidemainmenu=1',null,'<?php echo JText::_( 'Configuration', true ); ?>']
  			],
<?php
	// System Sub-Menu
?>			_cmSplit,
  			[null,'<?php echo JText::_( 'System', true ); ?>',null,null,'<?php echo JText::_( 'System Management', true ); ?>',
  			   ['<img src="../includes/js/ThemeOffice/sysinfo.png" />', '<?php echo JText::_( 'System Info', true ); ?>', 'index2.php?option=com_admin&task=sysinfo', null,'<?php echo JText::_( 'System Information', true ); ?>'],

<?php
  		if ($canCheckin) {
?>				['<img src="../includes/js/ThemeOffice/checkin.png" />', '<?php echo JText::_( 'Global Checkin', true ); ?>', 'index2.php?option=com_checkin', null,'<?php echo JText::_( 'Check-in all checked-out items', true ); ?>'],
<?php
			if ($caching) {
?>				['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo JText::_( 'Clean Content Cache', true ); ?>','index2.php?option=com_admin&task=clean_cache',null,'<?php echo JText::_( 'Clean the content items cache', true ); ?>'],
				['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo JText::_( 'Clean All Caches', true ); ?>','index2.php?option=com_admin&task=clean_all_cache',null,'<?php echo JText::_( 'Clean all caches', true ); ?>'],
<?php
			}
		}
?>			],
<?php
			}
?>			_cmSplit,
<?php
	// Help Sub-Menu
?>			[null,'<?php echo JText::_( 'Help', true ); ?>','index2.php?option=com_admin&task=help',null,null]
		];
		cmDraw ('myMenuID', myMenu, <?php echo ($lang->isRTL()) ? "'hbl'" : "'hbr'"; ?>, cmThemeOffice, 'ThemeOffice');
		</script>
<?php
	}


	/**
	* Show an disbaled version of the menu, used in edit pages
	* 
	* @param string The current user type
	*/
	function showDisabled( $usertype='' ) 
	{
		global $mainframe;

		$lang =& $mainframe->getLanguage();
		$acl  =& JFactory::getACL();

		$canConfig 			= $acl->acl_check( 'com_config', 'manage', 'users', $usertype );
		$installModules 	= $acl->acl_check( 'com_install', 'module', 'users', $usertype );
		$editAllModules 	= $acl->acl_check( 'com_modules', 'manage', 'users', $usertype );
		$installPlugins 	= $acl->acl_check( 'com_install', 'plugin', 'users', $usertype );
		$editAllPlugins 	= $acl->acl_check( 'com_plugins', 'manage', 'users', $usertype );
		$installComponents 	= $acl->acl_check( 'com_install', 'component', 'users', $usertype );
		$editAllComponents 	= $acl->acl_check( 'com_components', 'manage', 'users', $usertype );
		$canMassMail 		= $acl->acl_check( 'com_massmail', 'manage', 'users', $usertype );
		$canManageUsers 	= $acl->acl_check( 'com_users', 'manage', 'users', $usertype );

		$text = JText::_( 'Menu inactive for this Page', true );
		?>
		<div id="myMenuID" class="inactive"></div>
		<script language="JavaScript" type="text/javascript">
		var myMenu =
		[
		<?php
	/* Home Sub-Menu */
		?>
			[null,'<?php echo JText::_( 'Home', true ); ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit,
		<?php
	/* Site Sub-Menu */
		?>
			[null,'<?php echo JText::_( 'Site', true ); ?>',null,null,'<?php echo $text; ?>'
			],
		<?php
	/* Menu Sub-Menu */
		?>
			_cmSplit,
			[null,'<?php echo JText::_( 'Menu', true ); ?>',null,null,'<?php echo $text; ?>'
			],
			_cmSplit,
		<?php
	/* Content Sub-Menu */
		?>
 			[null,'<?php echo JText::_( 'Content', true ); ?>',null,null,'<?php echo $text; ?>'
			],
		<?php
	/* Components Sub-Menu */
			if ( $installComponents) {
				?>
				_cmSplit,
				[null,'<?php echo JText::_( 'Components', true ); ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if $installComponents
			?>
		<?php
	/* Modules Sub-Menu */
			if ( $installModules | $editAllModules) {
				?>
				_cmSplit,
				[null,'<?php echo JText::_( 'Modules', true ); ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if ( $installModules | $editAllModules)
			?>
		<?php
	/* Plugins Sub-Menu */
			if ( $installPlugins | $editAllPlugins) {
				?>
				_cmSplit,
				[null,'<?php echo JText::_( 'Plugins', true ); ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if ( $installPlugins | $editAllPlugins)
			?>


			<?php
	/* Installer Sub-Menu */
			if ( $installModules) {
				?>
				_cmSplit,
				[null,'<?php echo JText::_( 'Installers', true ); ?>',null,null,'<?php echo $text; ?>'
					<?php
					?>
				],
				<?php
			} // if ( $installModules)
			?>
			<?php
	/* Messages Sub-Menu */
			if ( $canConfig) {
				?>
				_cmSplit,
	  			[null,'<?php echo JText::_( 'Messages', true ); ?>',null,null,'<?php echo $text; ?>'
	  			],
				<?php
			}
			?>

			<?php
	/* System Sub-Menu */
			if ( $canConfig) {
				?>
				_cmSplit,
	  			[null,'<?php echo JText::_( 'System', true ); ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			}
			?>
			_cmSplit,
			<?php
	/* Help Sub-Menu */
			?>
			[null,'<?php echo JText::_( 'Help', true ); ?>',null,null,'<?php echo $text; ?>']
		];
		cmDraw ('myMenuID', myMenu, <?php echo ($lang->isRTL()) ? "'hbl'" : "'hbr'"; ?>, cmThemeOffice, 'ThemeOffice');
		</script>
		<?php
	}
}
$cache =& JFactory::getCache( 'mos_fullmenu' );
$lang =& $mainframe->getLanguage();

$doc   =& $mainframe->getDocument();
$doc->addScript( '../includes/js/JSCookMenu.js');
if ( $lang->isRTL() ) {
	$doc->addScript( 'includes/js/ThemeOffice/theme_rtl.js');
} else {
	$doc->addScript( 'includes/js/ThemeOffice/theme.js');
}

$hide = mosGetParam( $_REQUEST, 'hidemainmenu', 0 );

$user = $mainframe->getUser();

if ( $hide ) {
	mosFullAdminMenu::showDisabled( $user->usertype );
} else {
	mosFullAdminMenu::show( $user->usertype );
}
?>
