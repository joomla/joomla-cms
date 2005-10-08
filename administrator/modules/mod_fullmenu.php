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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* Full DHTML Admnistrator Menus
* @package Joomla
*/
class mosFullAdminMenu {
	/**
	* Show the menu
	* @param string The current user type
	*/
	function show( $usertype='' ) {
		global $acl, $database;
		global $mosConfig_live_site, $mosConfig_enable_stats, $mosConfig_caching;

		// cache some acl checks
		$canConfig 			= $acl->acl_check( 'administration', 'config', 'users', $usertype );

		$manageTemplates 	= $acl->acl_check( 'administration', 'manage', 'users', $usertype, 'components', 'com_templates' );
		$manageTrash 		= $acl->acl_check( 'administration', 'manage', 'users', $usertype, 'components', 'com_trash' );
		$manageMenuMan 		= $acl->acl_check( 'administration', 'manage', 'users', $usertype, 'components', 'com_menumanager' );
		$manageLanguages 	= $acl->acl_check( 'administration', 'manage', 'users', $usertype, 'components', 'com_languages' );
		$installModules 	= $acl->acl_check( 'administration', 'install', 'users', $usertype, 'modules', 'all' );
		$editAllModules 	= $acl->acl_check( 'administration', 'edit', 'users', $usertype, 'modules', 'all' );
		$installMambots 	= $acl->acl_check( 'administration', 'install', 'users', $usertype, 'mambots', 'all' );
		$editAllMambots 	= $acl->acl_check( 'administration', 'edit', 'users', $usertype, 'mambots', 'all' );
		$installComponents 	= $acl->acl_check( 'administration', 'install', 'users', $usertype, 'components', 'all' );
		$editAllComponents 	= $acl->acl_check( 'administration', 'edit', 'users', $usertype, 'components', 'all' );
		$canMassMail 		= $acl->acl_check( 'administration', 'manage', 'users', $usertype, 'components', 'com_massmail' );
		$canManageUsers 	= $acl->acl_check( 'administration', 'manage', 'users', $usertype, 'components', 'com_users' );

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
		foreach ($sections as $section)
			if ($section->numcat > 0)
				$nonemptySections++;
		$menuTypes = mosAdminMenus::menutypes();
		?>
		<div id="myMenuID"></div>
		<script language="JavaScript" type="text/javascript">
		var myMenu =
		[
		<?php
	// Home Sub-Menu
?>			[null,'Home','index2.php',null,'Control Panel'],
			_cmSplit,
			<?php
	// Site Sub-Menu
?>			[null,'Site',null,null,'Site Management',
<?php
			if ($canConfig) {
?>				['<img src="../includes/js/ThemeOffice/config.png" />','Global Configuration','index2.php?option=com_config&hidemainmenu=1',null,'Configuration'],
<?php
			}
			if ($manageLanguages) {
?>				['<img src="../includes/js/ThemeOffice/language.png" />','Language Manager',null,null,'Manage languages',
  					['<img src="../includes/js/ThemeOffice/language.png" />','Site Languages','index2.php?option=com_languages',null,'Manage Languages'],
   				],
<?php
			}
?>				['<img src="../includes/js/ThemeOffice/media.png" />','Media Manager','index2.php?option=com_media',null,'Manage Media Files'],
					['<img src="../includes/js/ThemeOffice/preview.png" />', 'Preview', null, null, 'Preview',
					['<img src="../includes/js/ThemeOffice/preview.png" />','In New Window','<?php echo $mosConfig_live_site; ?>/index.php','_blank','<?php echo $mosConfig_live_site; ?>'],
					['<img src="../includes/js/ThemeOffice/preview.png" />','Inline','index2.php?option=com_admin&task=preview',null,'<?php echo $mosConfig_live_site; ?>'],
					['<img src="../includes/js/ThemeOffice/preview.png" />','Inline with Positions','index2.php?option=com_admin&task=preview2',null,'<?php echo $mosConfig_live_site; ?>'],
				],
				['<img src="../includes/js/ThemeOffice/globe1.png" />', 'Statistics', null, null, 'Site Statistics',
<?php
			if ($mosConfig_enable_stats == 1) {
?>					['<img src="../includes/js/ThemeOffice/globe4.png" />', 'Browser, OS, Domain', 'index2.php?option=com_statistics', null, 'Browser, OS, Domain'],
  					['<img src="../includes/js/ThemeOffice/globe3.png" />', 'Page Impressions', 'index2.php?option=com_statistics&task=pageimp', null, 'Page Impressions'],
<?php
			}
?>					['<img src="../includes/js/ThemeOffice/search_text.png" />', 'Search Text', 'index2.php?option=com_statistics&task=searches', null, 'Search Text']
				],
<?php
			if ($manageTemplates) {
?>				['<img src="../includes/js/ThemeOffice/template.png" />','Template Manager',null,null,'Change site template',
  					['<img src="../includes/js/ThemeOffice/template.png" />','Site Templates','index2.php?option=com_templates',null,'Change site template'],
  					_cmSplit,
  					['<img src="../includes/js/ThemeOffice/template.png" />','Administrator Templates','index2.php?option=com_templates&client=admin',null,'Change admin template'],
  					_cmSplit,
  					['<img src="../includes/js/ThemeOffice/template.png" />','Module Positions','index2.php?option=com_templates&task=positions',null,'Template positions']
  				],
<?php
			}
			if ($manageTrash) {
?>				['<img src="../includes/js/ThemeOffice/trash.png" />','Trash Manager','index2.php?option=com_trash',null,'Manage Trash'],
<?php
			}
			if ($canManageUsers || $canMassMail) {
?>				['<img src="../includes/js/ThemeOffice/users.png" />','User Manager','index2.php?option=com_users&task=view',null,'Manage users'],
<?php
				}
?>			],
<?php
	// Menu Sub-Menu
?>			_cmSplit,
			[null,'Menu',null,null,'Menu Management',
<?php
			if ($manageMenuMan) {
?>				['<img src="../includes/js/ThemeOffice/menus.png" />','Menu Manager','index2.php?option=com_menumanager',null,'Menu Manager'],
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
?>			[null,'Content',null,null,'Content Management',
<?php
			if (count($sections) > 0) {
?>				['<img src="../includes/js/ThemeOffice/edit.png" />','Content by Section',null,null,'Content Managers',
<?php
				foreach ($sections as $section) {
					$txt = addslashes( $section->title ? $section->title : $section->name );
?>					['<img src="../includes/js/ThemeOffice/document.png" />','<?php echo $txt;?>', null, null,'<?php echo $txt;?>',
<?php
					if ($section->numcat) {
?>						['<img src="../includes/js/ThemeOffice/edit.png" />', '<?php echo $txt;?> Items', 'index2.php?option=com_content&sectionid=<?php echo $section->id;?>',null,null],
<?php
					}
?>						['<img src="../includes/js/ThemeOffice/add_section.png" />', 'Add/Edit <?php echo $txt;?> Categories', 'index2.php?option=com_categories&section=<?php echo $section->id;?>',null, null],
<?php
					if ($section->numarc) {
?>						['<img src="../includes/js/ThemeOffice/backup.png" />', '<?php echo $txt;?> Archive', 'index2.php?option=com_content&task=showarchive&sectionid=<?php echo $section->id;?>',null,null],
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
				['<img src="../includes/js/ThemeOffice/edit.png" />','All Content Items','index2.php?option=com_content&sectionid=0',null,'Manage Content Items'],
  				['<img src="../includes/js/ThemeOffice/edit.png" />','Static Content Manager','index2.php?option=com_typedcontent',null,'Manage Typed Content Items'],
  				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/add_section.png" />','Section Manager','index2.php?option=com_sections&scope=content',null,'Manage Content Sections'],
				['<img src="../includes/js/ThemeOffice/add_section.png" />','Category Manager','index2.php?option=com_categories&section=content',null,'Manage Content Categories'],
				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/home.png" />','Frontpage Manager','index2.php?option=com_frontpage',null,'Manage Frontpage Items'],
  				['<img src="../includes/js/ThemeOffice/edit.png" />','Archive Manager','index2.php?option=com_content&task=showarchive&sectionid=0',null,'Manage Archive Items'],
			],
<?php
	// Components Sub-Menu
	if ($installComponents) {
?>			_cmSplit,
			[null,'Components',null,null,'Component Management',
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
					$name = addslashes( $row->name );
					$alt = addslashes( $row->admin_menu_alt );
					$link = $row->admin_menu_link ? "'index2.php?$row->admin_menu_link'" : "null";
					echo "\t\t\t\t['<img src=\"../includes/$row->admin_menu_img\" />','$name',$link,null,'$alt'";
					if (array_key_exists( $row->id, $subs )) {
						foreach ($subs[$row->id] as $sub) {
							echo ",\n";
							$name = addslashes( $sub->name );
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
			echo "\t\t\t\t['<img src=\"../includes/js/ThemeOffice/sections.png\" />','More Components...','index2.php?option=com_admin&task=listcomponents',null,'More Components'],\n";
		}
?>
			],
<?php
	// Modules Sub-Menu
		if ($installModules | $editAllModules) {
?>			_cmSplit,
			[null,'Modules',null,null,'Module Management',
<?php
			if ($editAllModules) {
?>				['<img src="../includes/js/ThemeOffice/module.png" />', 'Site Modules', "index2.php?option=com_modules", null, 'Manage Site modules'],
				['<img src="../includes/js/ThemeOffice/module.png" />', 'Administrator Modules', "index2.php?option=com_modules&client=admin", null, 'Manage Administrator modules'],
<?php
			}
?>			],
<?php
		} // if ($installModules | $editAllModules)
	} // if $installComponents
	// Mambots Sub-Menu
	if ($installMambots | $editAllMambots) {
?>			_cmSplit,
			[null,'Mambots',null,null,'Mambot Management',
<?php
		if ($editAllMambots) {
?>				['<img src="../includes/js/ThemeOffice/module.png" />', 'Site Mambots', "index2.php?option=com_mambots", null, 'Manage Site Mambots'],
<?php
		}
?>			],
<?php
	}
?>
<?php
	// Installer Sub-Menu
	if ($installModules) {
?>			_cmSplit,
			[null,'Installers',null,null,'Installer List',
<?php
		if ($manageTemplates) {
?>				['<img src="../includes/js/ThemeOffice/install.png" />','Templates - Site','index2.php?option=com_installer&element=template&client=',null,'Install Site Templates'],
				['<img src="../includes/js/ThemeOffice/install.png" />','Templates - Admin','index2.php?option=com_installer&element=template&client=admin',null,'Install Administrator Templates'],
<?php
		}
		if ($manageLanguages) {
?>				['<img src="../includes/js/ThemeOffice/install.png" />','Languages','index2.php?option=com_installer&element=language',null,'Install Languages'],
				_cmSplit,
<?php
		}
?>				['<img src="../includes/js/ThemeOffice/install.png" />', 'Components','index2.php?option=com_installer&element=component',null,'Install/Uninstall Components'],
				['<img src="../includes/js/ThemeOffice/install.png" />', 'Modules', 'index2.php?option=com_installer&element=module', null, 'Install/Uninstall Modules'],
				['<img src="../includes/js/ThemeOffice/install.png" />', 'Mambots', 'index2.php?option=com_installer&element=mambot', null, 'Install/Uninstall Mambots'],
			],
<?php
	} // if ($installModules)
	// Messages Sub-Menu
	if ($canConfig) {
?>			_cmSplit,
  			[null,'Messages',null,null,'Messaging Management',
  				['<img src="../includes/js/ThemeOffice/messaging_inbox.png" />','Inbox','index2.php?option=com_messages',null,'Private Messages'],
  				['<img src="../includes/js/ThemeOffice/messaging_config.png" />','Configuration','index2.php?option=com_messages&task=config&hidemainmenu=1',null,'Configuration']
  			],
<?php
	// System Sub-Menu
?>			_cmSplit,
  			[null,'System',null,null,'System Management',
  			   ['<img src="../includes/js/ThemeOffice/sysinfo.png" />', 'System Info', 'index2.php?option=com_admin&task=sysinfo', null,'System Information'],

<?php
  		if ($canConfig) {
?>				['<img src="../includes/js/ThemeOffice/checkin.png" />', 'Global Checkin', 'index2.php?option=com_checkin', null,'Check-in all checked-out items'],
<?php
			if ($mosConfig_caching) {
?>				['<img src="../includes/js/ThemeOffice/config.png" />','Clean Content Cache','index2.php?option=com_admin&task=clean_cache',null,'Clean the content items cache'],
				['<img src="../includes/js/ThemeOffice/config.png" />','Clean All Caches','index2.php?option=com_admin&task=clean_all_cache',null,'Clean all caches'],
<?php
			}
		}
?>			],
<?php
			}
?>			_cmSplit,
<?php
	// Help Sub-Menu
?>			[null,'Help','index2.php?option=com_admin&task=help',null,null]
		];
		cmDraw ('myMenuID', myMenu, 'hbr', cmThemeOffice, 'ThemeOffice');
		</script>
<?php
	}


	/**
	* Show an disbaled version of the menu, used in edit pages
	* @param string The current user type
	*/
	function showDisabled( $usertype='' ) {
		global $acl, $_LANG;

		$canConfig 			= $acl->acl_check( 'administration', 'config', 'users', $usertype );
		$installModules 	= $acl->acl_check( 'administration', 'install', 'users', $usertype, 'modules', 'all' );
		$editAllModules 	= $acl->acl_check( 'administration', 'edit', 'users', $usertype, 'modules', 'all' );
		$installMambots 	= $acl->acl_check( 'administration', 'install', 'users', $usertype, 'mambots', 'all' );
		$editAllMambots 	= $acl->acl_check( 'administration', 'edit', 'users', $usertype, 'mambots', 'all' );
		$installComponents 	= $acl->acl_check( 'administration', 'install', 'users', $usertype, 'components', 'all' );
		$editAllComponents 	= $acl->acl_check( 'administration', 'edit', 'users', $usertype, 'components', 'all' );
		$canMassMail 		= $acl->acl_check( 'administration', 'manage', 'users', $usertype, 'components', 'com_massmail' );
		$canManageUsers 	= $acl->acl_check( 'administration', 'manage', 'users', $usertype, 'components', 'com_users' );

		$text = 'Menu inactive for this Page';
		?>
		<div id="myMenuID" class="inactive"></div>
		<script language="JavaScript" type="text/javascript">
		var myMenu =
		[
		<?php
	/* Home Sub-Menu */
		?>
			[null,'<?php echo 'Home'; ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit,
		<?php
	/* Site Sub-Menu */
		?>
			[null,'<?php echo 'Site'; ?>',null,null,'<?php echo $text; ?>'
			],
		<?php
	/* Menu Sub-Menu */
		?>
			_cmSplit,
			[null,'<?php echo 'Menu'; ?>',null,null,'<?php echo $text; ?>'
			],
			_cmSplit,
		<?php
	/* Content Sub-Menu */
		?>
 			[null,'<?php echo 'Content'; ?>',null,null,'<?php echo $text; ?>'
			],
		<?php
	/* Components Sub-Menu */
			if ( $installComponents) {
				?>
				_cmSplit,
				[null,'<?php echo 'Components'; ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if $installComponents
			?>
		<?php
	/* Modules Sub-Menu */
			if ( $installModules | $editAllModules) {
				?>
				_cmSplit,
				[null,'<?php echo 'Modules'; ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if ( $installModules | $editAllModules)
			?>
		<?php
	/* Mambots Sub-Menu */
			if ( $installMambots | $editAllMambots) {
				?>
				_cmSplit,
				[null,'<?php echo 'Mambots'; ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if ( $installMambots | $editAllMambots)
			?>


			<?php
	/* Installer Sub-Menu */
			if ( $installModules) {
				?>
				_cmSplit,
				[null,'<?php echo 'Installers'; ?>',null,null,'<?php echo $text; ?>'
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
	  			[null,'<?php echo 'Messages'; ?>',null,null,'<?php echo $text; ?>'
	  			],
				<?php
			}
			?>

			<?php
	/* System Sub-Menu */
			if ( $canConfig) {
				?>
				_cmSplit,
	  			[null,'<?php echo 'System'; ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			}
			?>
			_cmSplit,
			<?php
	/* Help Sub-Menu */
			?>
			[null,'<?php echo 'Help'; ?>',null,null,'<?php echo $text; ?>']
		];
		cmDraw ('myMenuID', myMenu, 'hbr', cmThemeOffice, 'ThemeOffice');
		</script>
		<?php
	}
}
$cache =& mosCache::getCache( 'mos_fullmenu' );

$hide = mosGetParam( $_REQUEST, 'hidemainmenu', 0 );

if ( $hide ) {
	mosFullAdminMenu::showDisabled( $my->usertype );
} else {
	mosFullAdminMenu::show( $my->usertype );
}
?>