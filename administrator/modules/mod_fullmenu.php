<?php
/**
* @version $Id: mod_fullmenu.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* AdminMenu cache handler
* @package Joomla
*/
class mosCache_AdminMenu extends mosCache_Function {

    /**
	 * Constructor
	 *
	 * @param array $options options
	 * @access protected
	 */
    function _construct($options) {
    	$options['fileNameProtection'] = false;
		$options['readControl'] = false;

	   parent::_construct($options);
    }

    /**
	 * Calls a cacheable function or method (or not if there is already a cache for it)
	 * and specify a specific id
	 *
	 * @param string Function to call
	 * @param array  Argument of the function
	 * @param id	 Cache id
	 * @return mixed result of the function/method
	 * @access public
	 */
    function callId( $target, $arguments, $id ) {
		$data = $this->get( $id, $this->_defaultGroup, !$this->_validateCache );

	   if ( $data == false ) {

		  ob_start();
		  ob_implicit_flush(false);

		  list( $class, $method ) = explode( '::', $target );
		  call_user_func_array( array( $class, $method ), $arguments );

		  $output = ob_get_contents();
		  ob_end_clean();

		  $this->save( $output, $id, $this->_defaultGroup );
	   }
    }
}

/**
* Full DHTML Admnistrator Menus
* @package Joomla
*/
class mosFullAdminMenu {

	/**
	* Show the menu
	* @param string 	The current user type
	* @param boolean	If true, menu will be disabled
	*/
	function showMenu($usertype='', $disabled = false) {
		global $_LANG, $mainframe, $mosConfig_live_site, $my;

		$class = $disabled ? 'class="inactive"' : '';
		?>
		<div id="mosmenu" <?php echo $class ?>></div>
		<?php

		$cache =& mosFactory::getCache( 'mod_fullmenu', 'mosCache_AdminMenu' );
		$cache->setCacheValidation(false);

		if( !$disabled ) {
			if( $cache->_caching ) {
				$cache->callId( "mosFullAdminMenu::_showEnabled", array($usertype), 'enabled_'.$my->gid.'.js' );
			} else {
				?>
				<script language="JavaScript" type="text/javascript">
				<!--
				<?php mosFullAdminMenu::_showEnabled($usertype) ?>
				//-->
				</script>
				<?php
			}
		} else {
			if( $cache->_caching ) {
				$cache->callId( "mosFullAdminMenu::_showEnabled", array($usertype), 'disabled.js' );
			} else {

				?>
				<script language="JavaScript" type="text/javascript">
				<!--
				<?php mosFullAdminMenu::_showDisabled( $usertype ) ?>
				//-->
				</script>
				<?php
			}
		}

		?>
		<script language="JavaScript" type="text/javascript">
		<!--
		cmDraw ('mosmenu', myMenu, '<?php echo $_LANG->rtl() ? 'hbl': 'hbr'; ?>', cmThemeOffice, 'ThemeOffice');
		//-->
		</script>
		<?php
	}

	/**
	* Show the menu
	* @param string The current user type
	*/
	function _showEnabled( $usertype='' ) {
		global $acl, $database;
		global $mosConfig_live_site, $mosConfig_enable_stats, $mosConfig_caching;
		global $_LANG;

		// cache some acl checks
		$canConfig 			= $acl->acl_check( 'com_config', 		'manage', 'users', $usertype );
		$manageTemplates 	= $acl->acl_check( 'com_templates', 	'manage', 'users', $usertype );
		$manageTrash 		= $acl->acl_check( 'com_trash', 		'manage', 'users', $usertype );
		$manageMenuMan 		= $acl->acl_check( 'com_menumanager', 	'manage', 'users', $usertype );
		$manageLanguages 	= $acl->acl_check( 'com_languages', 	'manage', 'users', $usertype );
		$editAllModules 	= $acl->acl_check( 'com_modules', 		'manage', 'users', $usertype );
		$installMambots 	= $acl->acl_check( 'com_installer', 	'mambot', 'users', $usertype );
		$editAllMambots 	= $acl->acl_check( 'com_mambots', 		'manage', 'users', $usertype );
		$canMassMail 		= $acl->acl_check( 'com_massmail', 		'manage', 'users', $usertype );
		$canManageUsers 	= $acl->acl_check( 'com_users', 		'manage', 'users', $usertype );
		$installModules 	= $acl->acl_check( 'com_installer', 	'module', 'users', $usertype );
		$editAllComponents 	= $acl->acl_check( 'administration', 	'edit', 'users', $usertype, 'components', 'all' );
		$installComponents 	= $acl->acl_check( 'com_installer', 	'component', 'users', $usertype );
		$installLanguages 	= $acl->acl_check( 'com_installer', 	'language', 'users', $usertype );

	   $query = "SELECT a.id, a.title, a.name, COUNT(DISTINCT c.id) AS numcat, COUNT(DISTINCT b.id) AS numarc"
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
		foreach ( $sections as $section ) {
			if ( $section->numcat > 0 ) {
				$nonemptySections++;
			}
		}
		// preload menus
		$query = "SELECT menutype"
		. "\n FROM #__menu"
		. "\n GROUP BY menutype"
		. "\n ORDER BY menutype"
		;
		$database->setQuery( $query	);
		$menuTypes = $database->loadResultArray();
		?>
		var myMenu =
		[
		<?php
	/* Home Sub-Menu */
		?>
			[null,'<?php echo $_LANG->_( 'Home' ); ?>','index2.php',null,'Control Panel'],
			_cmSplit,
		<?php
	/* Site Sub-Menu */
		?>
			[null,'<?php echo $_LANG->_( 'Site' ); ?>',null,null,'Site Management',
				<?php
				if ( $canConfig) {
					?>
					['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo $_LANG->_( 'Global Configuration' ); ?>','index2.php?option=com_config',null,'Configuration'],
					<?php
				}
				if ( $manageLanguages) {
					?>
					['<img src="../includes/js/ThemeOffice/language.png" />','<?php echo $_LANG->_( 'Language Manager' ); ?>','index2.php?option=com_languages',null,'Manage languages'],
					<?php
				}
				?>
				['<img src="../includes/js/ThemeOffice/media.png" />','<?php echo $_LANG->_( 'Media Manager' ); ?>','index2.php?option=com_media',null,'Manage Media Files'],
					['<img src="../includes/js/ThemeOffice/preview.png" />', '<?php echo $_LANG->_( 'Preview' ); ?>', null, null, 'Preview',
					['<img src="../includes/js/ThemeOffice/preview.png" />','<?php echo $_LANG->_( 'In New Window' ); ?>','<?php echo $mosConfig_live_site; ?>/index.php','_blank','<?php echo $mosConfig_live_site; ?>'],
					['<img src="../includes/js/ThemeOffice/preview.png" />','<?php echo $_LANG->_( 'Inline' ); ?>','index2.php?option=com_templates&task=preview',null,'<?php echo $mosConfig_live_site; ?>'],
					['<img src="../includes/js/ThemeOffice/preview.png" />','<?php echo $_LANG->_( 'Inline with Positions' ); ?>','index2.php?option=com_templates&task=preview2',null,'<?php echo $mosConfig_live_site; ?>'],
				],
				['<img src="../includes/js/ThemeOffice/globe1.png" />', '<?php echo $_LANG->_( 'Statistics' ); ?>', null, null, 'Site Statistics',
					['<img src="../includes/js/ThemeOffice/search_text.png" />', '<?php echo $_LANG->_( 'Search Text' ); ?>', 'index2.php?option=com_statistics&task=searches', null, 'Search Text'],
					<?php
					if ( $mosConfig_enable_stats == 1) {
						?>
						['<img src="../includes/js/ThemeOffice/globe4.png" />', '<?php echo $_LANG->_( 'Browser' ); ?>, OS, Domain', 'index2.php?option=com_statistics', null, 'Browser, OS, Domain'],
	  					['<img src="../includes/js/ThemeOffice/globe3.png" />', '<?php echo $_LANG->_( 'Page Impressions' ); ?>', 'index2.php?option=com_statistics&task=pageimp', null, 'Page Impressions']
						<?php
					}
					?>
				],
			<?php
			if ( $manageTemplates) {
				?>
				['<img src="../includes/js/ThemeOffice/template.png" />','<?php echo $_LANG->_( 'Template Manager' ); ?>','index2.php?option=com_templates',null,'Change site template'],
				<?php
			}
			/*
			if ( $manageTrash) {
				?>
				['<img src="../includes/js/ThemeOffice/trash.png" />','<?php echo $_LANG->_( 'Trash Manager' ); ?>','',null,'Manage Trash',
					['<img src="../includes/js/ThemeOffice/trash.png" />','<?php echo $_LANG->_( 'Content Trash' ); ?>','index2.php?option=com_content&task=trashview',null,'Manage Trash'],
					['<img src="../includes/js/ThemeOffice/trash.png" />','<?php echo $_LANG->_( 'Menu Trash' ); ?>','index2.php?option=com_menus&task=trashview',null,'Manage Trash'],
				],
				<?php
			}
			*/
			if ( $canManageUsers || $canMassMail) {
				?>
				['<img src="../includes/js/ThemeOffice/users.png" />','<?php echo $_LANG->_( 'User Manager' ); ?>','index2.php?option=com_users&task=view',null,'Manage users'],
				<?php
				}
				?>
			],
			<?php
	/* Menu Sub-Menu */
			?>
			_cmSplit,
			[null,'<?php echo $_LANG->_( 'Menu' ); ?>',null,null,'Menu Management',
				<?php
				if ( $manageMenuMan) {
					?>
					['<img src="../includes/js/ThemeOffice/menus.png" />','<?php echo $_LANG->_( 'Menu Manager' ); ?>','index2.php?option=com_menumanager',null,'Menu Manager'],
					_cmSplit,
 					['<img src="../includes/js/ThemeOffice/trash.png" />','<?php echo $_LANG->_( 'Trashed Menu Items' ); ?>','index2.php?option=com_menus&task=trashview',null,'Manage Trashed Menu Items'],
					<?php
				}
				foreach ( $menuTypes as $menuType ) {
					$menuType = addslashes( $menuType );
					?>
					['<img src="../includes/js/ThemeOffice/menus.png" />','<?php echo $menuType;?>','index2.php?option=com_menus&menutype=<?php echo $menuType;?>',null,''],
					<?php
				}
				?>
			],
			_cmSplit,
			<?php
	/* Content Sub-Menu */
			?>
			[null,'<?php echo $_LANG->_( 'Content' ); ?>',null,null,'Content Management',
				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo $_LANG->_( 'Content Items Manager' ); ?>','index2.php?option=com_content&sectionid=0',null,'Manage Content Items'],
  				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo $_LANG->_( 'Static Content Manager' ); ?>','index2.php?option=com_typedcontent',null,'Manage Typed Content Items'],
  				['<img src="../includes/js/ThemeOffice/trash.png" />','<?php echo $_LANG->_( 'Trashed Content' ); ?>','index2.php?option=com_content&task=trashview',null,'Manage Trashed Content'],
 				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/add_section.png" />','<?php echo $_LANG->_( 'Section Manager' ); ?>','index2.php?option=com_sections&scope=content',null,'Manage Content Sections'],
				<?php
				if ( count($sections) > 0) {
					?>
					['<img src="../includes/js/ThemeOffice/add_section.png" />','<?php echo $_LANG->_( 'Category Manager' ); ?>','index2.php?option=com_categories&section=content',null,'Manage Content Categories'],
					<?php
				}
				?>
				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/home.png" />','<?php echo $_LANG->_( 'Frontpage Manager' ); ?>','index2.php?option=com_frontpage',null,'Manage Frontpage Items'],
  				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo $_LANG->_( 'Archive Manager' ); ?>','index2.php?option=com_content&task=showarchive&sectionid=0',null,'Manage Archive Items'],
 			],
			<?php
	/* Components Sub-Menu */
			if ( $installComponents) {
				?>
				_cmSplit,
				[null,'<?php echo $_LANG->_( 'Components' ); ?>',null,null,'Component Management',
					['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo $_LANG->_( 'Component Manager' ); ?>','index2.php?option=com_components',null,'Componets'],
					_cmSplit,
					<?php
				   $query = "SELECT *"
				   . "\n FROM #__components"
				   . "\n WHERE LOWER( name ) <> 'frontpage'"
				   . "\n AND LOWER( name ) <> 'media manager'"
				   . "\n ORDER BY ordering, name"
				   ;
				   $database->setQuery( $query );
				   $comps = $database->loadObjectList();   // component list
				   $subs = array();    // sub menus
				   // first pass to collect sub-menu items
				   foreach ($comps as $row) {
					  if ( $row->parent) {
						 if ( !array_key_exists( $row->parent, $subs )) {
							$subs[$row->parent] = array();
						 }
						 $subs[$row->parent][] = $row;
					  }
				   }
				   $topLevelLimit = 19; //You can get 19 top levels on a 800x600 Resolution
				   $topLevelCount = 0;
				   foreach ($comps as $row) {
					  if ( $editAllComponents | $acl->acl_check( $row->option, 'manage', 'users', $usertype )) {
						 if ( $row->parent == 0 && (trim( $row->admin_menu_link ) || array_key_exists( $row->id, $subs ))) {
							$topLevelCount++;
							if ( $topLevelCount > $topLevelLimit) {
							    continue;
							}
							$name 	= addslashes( $row->name );
							$alt 	= addslashes( $row->admin_menu_alt );
							$link 	= $row->admin_menu_link ? "'index2.php?$row->admin_menu_link'" : 'null';

							echo "\t\t\t\t['<img src=\"../includes/$row->admin_menu_img\" />','$name',$link,null,'$alt'";

							if ( array_key_exists( $row->id, $subs ) ) {
							    foreach ( $subs[$row->id] as $sub ) {
								    echo ",\n";
								   $name 	= addslashes( $sub->name );
								   $alt 	= addslashes( $sub->admin_menu_alt );
								   $link 	= $sub->admin_menu_link ? "'index2.php?$sub->admin_menu_link'" : "null";

								   echo "\t\t\t\t\t['<img src=\"../includes/$sub->admin_menu_img\" />','$name',$link,null,'$alt']";
							    }
							}
							echo "\n\t\t\t\t],\n";
						 }
					  }
				   }
				   if ( $topLevelLimit < $topLevelCount ) {
					  echo "\t\t\t\t['<img src=\"../includes/js/ThemeOffice/sections.png\" />','". $_LANG->_( 'More Components' ) ."','index2.php?option=com_admin&task=listcomponents',null,'More Components'],\n";
				   }
					?>
				],
				<?php
			} // if $installComponents
			?>
			<?php
	/* Modules Sub-Menu */
			if ( $installModules | $editAllModules) {
				?>
				_cmSplit,
				[null,'<?php echo $_LANG->_( 'Modules' ); ?>',null,null,'Module Management',
				<?php
				if ( $editAllModules) {
					?>
					['<img src="../includes/js/ThemeOffice/module.png" />', '<?php echo $_LANG->_( 'Site Modules' ); ?>', "index2.php?option=com_modules", null, 'Manage Site modules'],
					['<img src="../includes/js/ThemeOffice/module.png" />', '<?php echo $_LANG->_( 'Administrator Modules' ); ?>', "index2.php?option=com_modules&client=admin", null, 'Manage Administrator modules'],
					<?php
				}
				?>
				],
				<?php
			} // if ( $installModules | $editAllModules)
			?>
			<?php
	/* Mambots Sub-Menu */
			if ( $installMambots | $editAllMambots) {
				?>
				_cmSplit,
				[null,'<?php echo $_LANG->_( 'Mambots' ); ?>',null,null,'Mambot Management',
					<?php
					if ( $editAllMambots) {
						?>
						['<img src="../includes/js/ThemeOffice/module.png" />', '<?php echo $_LANG->_( 'Site Mambots' ); ?>', "index2.php?option=com_mambots", null, 'Manage Site Mambots'],
						<?php
					}
					?>
				],
				<?php
			} // if ( $installMambots | $editAllMambots)
			?>
			<?php
	/* Installer Sub-Menu */
			if ( $installModules) {
				?>
				_cmSplit,
				[null,'<?php echo $_LANG->_( 'Installers' ); ?>',null,null,'Installer List',
					['<img src="../includes/js/ThemeOffice/install.png" />', '<?php echo $_LANG->_( 'Components' ); ?>','index2.php?option=com_components&task=installOptions',null,'Install/Uninstall Components'],
					['<img src="../includes/js/ThemeOffice/install.png" />', '<?php echo $_LANG->_( 'Modules' ); ?>', 'index2.php?option=com_modules&task=installOptions', null, 'Install/Uninstall Modules'],
					['<img src="../includes/js/ThemeOffice/install.png" />', '<?php echo $_LANG->_( 'Mambots' ); ?>', 'index2.php?option=com_mambots&task=installOptions', null, 'Install/Uninstall Mambots'],
					<?php
					if ( $manageTemplates) {
						?>
						['<img src="../includes/js/ThemeOffice/install.png" />','<?php echo $_LANG->_( 'Templates' ); ?>','index2.php?option=com_templates&task=installOptions',null,'Install Templates'],
						<?php
					}
					if ( $installLanguages) {
						?>
						['<img src="../includes/js/ThemeOffice/install.png" />','<?php echo $_LANG->_( 'Languages' ); ?>','index2.php?option=com_languages&task=installOptions',null,'Install Languages'],
						//_cmSplit,
						<?php
					}
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
	  			[null,'<?php echo $_LANG->_( 'Messages' ); ?>',null,null,'Messaging Management',
	  				['<img src="../includes/js/ThemeOffice/messaging_inbox.png" />','<?php echo $_LANG->_( 'Inbox' ); ?>','index2.php?option=com_messages',null,'Private Messages'],
	  				['<img src="../includes/js/ThemeOffice/messaging_config.png" />','<?php echo $_LANG->_( 'Configuration' ); ?>','index2.php?option=com_messages&task=config',null,'Configuration']
	  			],
				<?php
			}
			?>
			<?php
	/* System Sub-Menu */
			if ( $canConfig) {
				?>
				_cmSplit,
	  			[null,'<?php echo $_LANG->_( 'System' ); ?>',null,null,'System Management',
					['<img src="../includes/js/ThemeOffice/sysinfo.png" />', '<?php echo $_LANG->_( 'System Info' ); ?>', 'index2.php?option=com_admin&task=sysinfo', null,'System Information'],
					['<img src="../includes/js/ThemeOffice/restore.png" />', '<?php echo $_LANG->_( 'Export' ); ?>', 'index2.php?option=com_export', null,'Export'],
					<?php
		 	 		if ( $canConfig) {
						?>
						['<img src="../includes/js/ThemeOffice/checkin.png" />', '<?php echo $_LANG->_( 'Checkin Manager' ); ?>', 'index2.php?option=com_checkin', null,'Check-in, checked-out items'],
						<?php
		 	 		}
					if ( $mosConfig_caching) {
						?>
						['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo $_LANG->_( 'Cache manager' ); ?>','index2.php?option=com_cachemanager',null,'Manage the cache'],
						<?php
					}
					?>
				],
				<?php
			}
			?>
			_cmSplit,
			<?php
	/* Help Sub-Menu */
			?>
			[null,'<?php echo $_LANG->_( 'Help' ); ?>','index2.php?option=com_admin&task=help',null,null]
		];
		<?php
	}


	/**
	* Show an disbaled version of the menu, used in edit pages
	* @param string The current user type
	*/
	function _showDisabled( $usertype='' ) {
		global $acl, $_LANG;

		// cache some acl checks
		$canConfig 			= $acl->acl_check( 'com_config', 		'manage', 'users', $usertype );
		$editAllModules 	= $acl->acl_check( 'com_modules', 		'manage', 'users', $usertype );
		$installMambots 	= $acl->acl_check( 'com_installer', 	'mambot', 'users', $usertype );
		$editAllMambots 	= $acl->acl_check( 'com_mambots', 		'manage', 'users', $usertype );
		$installModules 	= $acl->acl_check( 'com_installer', 	'module', 'users', $usertype );
		$installComponents 	= $acl->acl_check( 'com_installer', 	'component', 'users', $usertype );

		$text = $_LANG->_( 'Menu inactive for this Page' );
		?>

		var myMenu =
		[
		<?php
	/* Home Sub-Menu */
		?>
			[null,'<?php echo $_LANG->_( 'Home' ); ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit,
		<?php
	/* Site Sub-Menu */
		?>
			[null,'<?php echo $_LANG->_( 'Site' ); ?>',null,null,'<?php echo $text; ?>'
			],
		<?php
	/* Menu Sub-Menu */
		?>
			_cmSplit,
			[null,'<?php echo $_LANG->_( 'Menu' ); ?>',null,null,'<?php echo $text; ?>'
			],
			_cmSplit,
		<?php
	/* Content Sub-Menu */
		?>
 			[null,'<?php echo $_LANG->_( 'Content' ); ?>',null,null,'<?php echo $text; ?>'
			],
		<?php
	/* Components Sub-Menu */
			if ( $installComponents) {
				?>
				_cmSplit,
				[null,'<?php echo $_LANG->_( 'Components' ); ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if $installComponents
			?>
		<?php
	/* Modules Sub-Menu */
			if ( $installModules | $editAllModules) {
				?>
				_cmSplit,
				[null,'<?php echo $_LANG->_( 'Modules' ); ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if ( $installModules | $editAllModules)
			?>
		<?php
	/* Mambots Sub-Menu */
			if ( $installMambots | $editAllMambots) {
				?>
				_cmSplit,
				[null,'<?php echo $_LANG->_( 'Mambots' ); ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			} // if ( $installMambots | $editAllMambots)
			?>


			<?php
	/* Installer Sub-Menu */
			if ( $installModules) {
				?>
				_cmSplit,
				[null,'<?php echo $_LANG->_( 'Installers' ); ?>',null,null,'<?php echo $text; ?>'
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
	  			[null,'<?php echo $_LANG->_( 'Messages' ); ?>',null,null,'<?php echo $text; ?>'
	  			],
				<?php
			}
			?>

			<?php
	/* System Sub-Menu */
			if ( $canConfig) {
				?>
				_cmSplit,
	  			[null,'<?php echo $_LANG->_( 'System' ); ?>',null,null,'<?php echo $text; ?>'
				],
				<?php
			}
			?>
			_cmSplit,
			<?php
	/* Help Sub-Menu */
			?>
			[null,'<?php echo $_LANG->_( 'Help' ); ?>',null,null,'<?php echo $text; ?>']
		];
		<?php
	}
}

$disable = $mainframe->get('disableMenu', false);
mosFullAdminMenu::showMenu( $my->usertype, $disable );
?>