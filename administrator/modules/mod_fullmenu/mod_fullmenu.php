<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* Full DHTML Admnistrator Menus
*
* @package Joomla
*/
class JFullAdminMenu {
	/**
	* Show the menu
	* @param string The current user type
	*/
	function show($usertype = '')
	{
		global $mainframe;

		$lang 			= & JFactory::getLanguage();
		$user 			= & JFactory::getUser();
		$db 			= & JFactory::getDBO();
		$enableStats 	= $mainframe->getCfg('enable_stats');
		$enableSearches = $mainframe->getCfg('enable_log_searches');
		$caching 		= $mainframe->getCfg('caching');

		// cache some acl checks
		$canCheckin 		= $user->authorize('com_checkin', 		'manage');
		$canConfig 			= $user->authorize('com_config', 		'manage');
		$manageTemplates 	= $user->authorize('com_templates', 	'manage');
		$manageTrash 		= $user->authorize('com_trash', 		'manage');
		$manageMenuMan 		= $user->authorize('com_menumanager', 	'manage');
		$manageLanguages 	= $user->authorize('com_languages', 	'manage');
		$installModules 	= $user->authorize('com_installer', 	'module');
		$editAllModules 	= $user->authorize('com_modules', 		'manage');
		$installPlugins 	= $user->authorize('com_installer', 	'plugin');
		$editAllPlugins 	= $user->authorize('com_plugins', 		'manage');
		$installComponents 	= $user->authorize('com_installer', 	'component');
		$editAllComponents 	= $user->authorize('com_components', 	'manage');
		$canMassMail 		= $user->authorize('com_massmail', 		'manage');
		$canManageUsers 	= $user->authorize('com_users', 		'manage');

		// Menu Types
		require_once( JPATH_ADMINISTRATOR . '/components/com_menus/helper.php' );
		$menuTypes 	= JMenuHelper::getMenuTypes();
		?>
		<div id="myMenuID"></div>
		<script language="JavaScript" type="text/javascript">
		var myMenu =
		[
<?php /* Site Sub-Menu */ ?>
			[null,'<?php echo JText::_( 'System', true ); ?>',null,null,'<?php echo JText::_( 'Site Management', true ); ?>',
				['<img src="../includes/js/ThemeOffice/controlpanel.png" />','<?php echo JText::_( 'Control Panel', true ); ?>','index2.php',null,'<?php echo JText::_( 'Control Panel', true ); ?>'],
				_cmSplit,
		<?php
		if ($canManageUsers || $canMassMail) {
			?>
				['<img src="../includes/js/ThemeOffice/users.png" />','<?php echo JText::_( 'User Manager', true ); ?>','index2.php?option=com_users&task=view',null,'<?php echo JText::_( 'Manage users', true ); ?>'],
			<?php
		}
		?>
				['<img src="../includes/js/ThemeOffice/media.png" />','<?php echo JText::_( 'Media Manager', true ); ?>','index2.php?option=com_media',null,'<?php echo JText::_( 'Manage Media Files', true ); ?>'],
		<?php
		if ($manageTemplates) {
			?>
				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/preview.png" />', '<?php echo JText::_( 'Preview...', true ); ?>', 'index2.php?option=com_templates&task=preview', null, null],
			<?php
		}
		?>
		<?php
		if ($enableStats || $enableSearches ) {
			?>
				['<img src="../includes/js/ThemeOffice/globe1.png" />', '<?php echo JText::_( 'Statistics', true ); ?>', null, null, '<?php echo JText::_( 'Site Statistics', true ); ?>',
			<?php
			if ($enableStats ) {
				?>
					['<img src="../includes/js/ThemeOffice/globe4.png" />', '<?php echo JText::_( 'Browser, OS, Domain', true ); ?>', 'index2.php?option=com_statistics', null, '<?php echo JText::_( 'Browser, OS, Domain', true ); ?>']
				<?php
			}
			?>
			<?php
			if ($enableStats == 1 && $enableSearches ) {
				?>
					,
				<?php
			}
			?>
			<?php
			if ($enableSearches ) {
				?>
					['<img src="../includes/js/ThemeOffice/search_text.png" />', '<?php echo JText::_( 'Search Text', true ); ?>', 'index2.php?option=com_statistics&task=searches', null, '<?php echo JText::_( 'Search Text', true ); ?>']
				<?php
			}
			?>
			],
			<?php
		}
		?>
			<?php if ($canConfig) { ?>
				_cmSplit,
				['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo JText::_( 'Configuration', true ); ?>','index2.php?option=com_config',null,'<?php echo JText::_( 'Configuration', true ); ?>'],
			<?php } ?>
			_cmSplit,
			[null,'<?php echo JText::_( 'Logout', true ); ?>','index2.php?option=logout',null,'<?php echo JText::_( 'Logout', true ); ?>'],
			],
			_cmSplit,

<?php /* Menu Sub-Menu */ ?>
			[null,'<?php echo JText::_( 'Menus', true ); ?>',null,null,'<?php echo JText::_( 'Menu Management', true ); ?>',
		<?php
		if ($manageMenuMan) {
			?>
				['<img src="../includes/js/ThemeOffice/menus.png" />','<?php echo JText::_( 'Menu Manager', true ); ?>','index2.php?option=com_menumanager',null,'<?php echo JText::_( 'Manage menu', true ); ?>'],
			<?php
		}

		if ($manageTrash) {
			?>
				['<img src="../includes/js/ThemeOffice/trash.png" />','<?php echo JText::_( 'Trash Manager', true ); ?>','index2.php?option=com_trash&task=viewMenu',null,'<?php echo JText::_( 'Manage Trash', true ); ?>'],
			<?php
		}
		?>
				_cmSplit,
		<?php
		foreach ($menuTypes as $menuType) {
			?>
				['<img src="../includes/js/ThemeOffice/menus.png" />','<?php echo $menuType;?>','index2.php?option=com_menus&menutype=<?php echo $menuType;?>',null,''],
			<?php
		}
		?>
			],
			_cmSplit,

<?php /* Content Sub-Menu */ ?>
			[null,'<?php echo JText::_( 'Content', true ); ?>',null,null,'<?php echo JText::_( 'Content Management', true ); ?>',
				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo JText::_( 'Article Manager', true ); ?>','index2.php?option=com_content',null,'<?php echo JText::_( 'Manage Articles', true ); ?>'],
  				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/add_section.png" />','<?php echo JText::_( 'Section Manager', true ); ?>','index2.php?option=com_sections&scope=content',null,'<?php echo JText::_( 'Manage Article Sections', true ); ?>'],
				['<img src="../includes/js/ThemeOffice/add_section.png" />','<?php echo JText::_( 'Category Manager', true ); ?>','index2.php?option=com_categories&section=content',null,'<?php echo JText::_( 'Manage Article Categories', true ); ?>'],
				_cmSplit,
  				['<img src="../includes/js/ThemeOffice/home.png" />','<?php echo JText::_( 'Frontpage Manager', true ); ?>','index2.php?option=com_frontpage',null,'<?php echo JText::_( 'Manage Frontpage Items', true ); ?>'],
				['<img src="../includes/js/ThemeOffice/edit.png" />','<?php echo JText::_( 'Archive Manager', true ); ?>','index2.php?option=com_content&task=showarchive&sectionid=0',null,'<?php echo JText::_( 'Manage Archive Items', true ); ?>'],
		<?php
		if ($manageTrash) {
			?>
				_cmSplit,
				['<img src="../includes/js/ThemeOffice/trash.png" />','<?php echo JText::_( 'Trash Manager', true ); ?>','index2.php?option=com_trash&task=viewContent',null,'<?php echo JText::_( 'Manage Trash', true ); ?>'],
  				['<img src="../includes/js/ThemeOffice/globe3.png" />', '<?php echo JText::_( 'Page Hits', true ); ?>', 'index2.php?option=com_statistics&task=pageimp', null, '<?php echo JText::_( 'Page Impressions', true ); ?>']
			<?php
		}
		?>
			],
			_cmSplit,

<?php /* Components Sub-Menu */ ;?>
		<?php
		if ($installComponents) {
			?>
			[null,'<?php echo JText::_( 'Components', true ); ?>',null,null,'<?php echo JText::_( 'Component Management', true ); ?>',
			<?php
			$query = "SELECT *" .
					"\n FROM #__components" .
					"\n WHERE name <> 'frontpage'" .
					"\n AND name <> 'media manager'" .
					"\n ORDER BY ordering, name";
			$db->setQuery($query);
			$comps 	= $db->loadObjectList(); // component list
			$subs	 = array (); // sub menus
			// first pass to collect sub-menu items
			foreach ($comps as $row) {
				if ($row->parent) {
					if (!array_key_exists($row->parent, $subs)) {
						$subs[$row->parent] = array ();
					}
					$subs[$row->parent][] = $row;
				}
			}
			$topLevelLimit = 19; //You can get 19 top levels on a 800x600 Resolution
			$topLevelCount = 0;
			foreach ($comps as $row) {
				if ($editAllComponents | $user->authorize('administration', 'edit', 'components', $row->option)) {
					if ($row->parent == 0 && (trim($row->admin_menu_link) || array_key_exists($row->id, $subs))) {
						$topLevelCount ++;
						if ($topLevelCount > $topLevelLimit) {
							continue;
						}
						$name 	= JText::_($row->name);
						$name 	= addslashes($name);
						$alt 	= addslashes($row->admin_menu_alt);
						$link 	= $row->admin_menu_link ? "'index2.php?$row->admin_menu_link'" : "null";
						?>
				['<img src=\"../includes/<?php echo $row->admin_menu_img; ?>\" />','<?php echo $name; ?>',<?php echo $link; ?>,null,'<?php echo $alt; ?>'
						<?php
						if (array_key_exists($row->id, $subs)) 	{
							foreach ($subs[$row->id] as $sub) {
								echo ",\n";
								$name 	= JText::_($sub->name);
								$name 	= addslashes($name);
								$alt 	= addslashes($sub->admin_menu_alt);
								$link 	= $sub->admin_menu_link ? "'index2.php?$sub->admin_menu_link'" : "null";
								?>
					,['<img src=\"../includes/<?php echo $sub->admin_menu_img; ?>\" />','<?php echo $name; ?>',<?php echo $link; ?>,null,'<?php echo $alt; ?>']
								<?php
							}
						}
						?>
				],
						<?php
					}
				}
			}
			if ($topLevelLimit < $topLevelCount) {
				?>
				['<img src=\"../includes/js/ThemeOffice/sections.png\" />','<?php echo JText::_('More Components...', true); ?>','index2.php?option=com_admin&task=listcomponents',null,'<?php echo JText::_( 'More Components...', true ); ?>']
				<?php
			}
			?>
			],
			_cmSplit,
			<?php
		}
		?>

<?php /* Extensions Sub-Menu */ ;?>
		<?php
		if ($installModules) {
			?>
			[null,'<?php echo JText::_( 'Extensions', true ); ?>',null,null,'<?php echo JText::_( 'Extensions', true ); ?>',
				['<img src="../includes/js/ThemeOffice/install.png" />','<?php echo JText::_( 'Install/Uninstall', true ); ?>','index2.php?option=com_installer',null,'<?php echo JText::_( 'Install/Uninstall', true ); ?>'],
				_cmSplit,

			<?php /* Modules Sub-Menu */
			if ($installModules | $editAllModules) { ?>
				['<img src="../includes/js/ThemeOffice/component.png" />','<?php echo JText::_( 'Modules', true ); ?>','index2.php?option=com_modules&client=0',null,'<?php echo JText::_( 'Manage modules', true ); ?>'],
			<?php } ?>

			<?php /* Plugins Sub-Menu */
			if ($installPlugins | $editAllPlugins) 	{ ?>
				['<img src="../includes/js/ThemeOffice/component.png" />','<?php echo JText::_( 'Plugins', true ); ?>', 'index2.php?option=com_plugins',null,'<?php echo JText::_( 'Manage plugins', true ); ?>'],
			<?php } ?>

			<?php /* Templates Sub-Menu */
			if ($manageTemplates) { ?>
				['<img src="../includes/js/ThemeOffice/template.png" />','<?php echo JText::_( 'Templates', true ); ?>','index2.php?option=com_templates&client=0',null,'<?php echo JText::_( 'Manage templates', true ); ?>'],
			<?php } ?>

			<?php
			if ($manageLanguages) { ?>
				['<img src="../includes/js/ThemeOffice/language.png" />','<?php echo JText::_( 'Languages', true ); ?>','index2.php?option=com_languages&client=0',null,'<?php echo JText::_( 'Manage languages', true ); ?>'],
			<?php } ?>
			],
			_cmSplit,
			<?php
		}
		?>

<?php /* System Sub-Menu */ ;?>
		<?php
		if ($canConfig)	{
			?>
  			[null,'<?php echo JText::_( 'Tools', true ); ?>',null,null,'<?php echo JText::_( 'Tools', true ); ?>',
	  			['<img src="../includes/js/ThemeOffice/messaging_inbox.png" />','<?php echo JText::_( 'Read Messages', true ); ?>','index2.php?option=com_messages',null,null],
	  			['<img src="../includes/js/ThemeOffice/messaging_inbox.png" />','<?php echo JText::_( 'New Message', true ); ?>','index2.php?option=com_messages&taks=new',null,null],
				_cmSplit,
			<?php
			if ($canMassMail)	{
				?>
				['<img src=\"../includes/js/ThemeOffice/mass_email.png\" />','<?php echo JText::_( 'Mass Mail', true ); ?>','index2.php?option=com_massmail&hidemainmenu=1',null,'<?php echo JText::_( 'Send Mass Mail', true ); ?>'],
				_cmSplit,
				<?php
			}
			?>
			<?php
			if ($canCheckin) {
				?>
				['<img src="../includes/js/ThemeOffice/checkin.png" />', '<?php echo JText::_( 'Global Checkin', true ); ?>', 'index2.php?option=com_checkin', null,'<?php echo JText::_( 'Check-in all checked-out items', true ); ?>'],
				_cmSplit,
				<?php
			}
			?>
			<?php
			if ($caching) {
				?>
				['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo JText::_( 'Clean Content Cache', true ); ?>','index2.php?option=com_admin&task=clean_cache',null,'<?php echo JText::_( 'Clean the articles cache', true ); ?>'],
				['<img src="../includes/js/ThemeOffice/config.png" />','<?php echo JText::_( 'Clean All Caches', true ); ?>','index2.php?option=com_admin&task=clean_all_cache',null,'<?php echo JText::_( 'Clean all caches', true ); ?>'],
				_cmSplit,
				<?php
			}
			?>
				['<img src="../includes/js/ThemeOffice/sysinfo.png" />', '<?php echo JText::_( 'System Info', true ); ?>', 'index2.php?option=com_admin&task=sysinfo', null,'<?php echo JText::_( 'System Information', true ); ?>'],
			],
			_cmSplit,
			<?php
		}
		?>

<?php /* Help Sub-Menu */ ;?>
			[null,'<?php echo JText::_( 'Help', true ); ?>','index2.php?option=com_admin&task=help',null,null],
			_cmSplit,

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
	function showDisabled($usertype = '')
	{
		$lang 	=& JFactory::getLanguage();
		$user 	=& JFactory::getUser();

		$canConfig 			= $user->authorize('com_config', 		'manage');
		$installModules 	= $user->authorize('com_installer', 	'module');
		$editAllModules 	= $user->authorize('com_modules', 		'manage');
		$installPlugins 	= $user->authorize('com_installer', 	'plugin');
		$editAllPlugins 	= $user->authorize('com_plugins', 		'manage');
		$installComponents 	= $user->authorize('com_installer', 	'component');
		$editAllComponents 	= $user->authorize('com_components',	'manage');
		$canMassMail 		= $user->authorize('com_massmail', 		'manage');
		$canManageUsers 	= $user->authorize('com_users', 		'manage');

		$text = JText::_('Menu inactive for this Page', true);
		?>
		<div id="myMenuID" class="inactive"></div>
		<script language="JavaScript" type="text/javascript">
		var myMenu =
		[

<?php /* Site Sub-Menu */ ?>
			[null,'<?php echo JText::_( 'System', true ); ?>',null,null,'<?php echo JText::_( 'Site Management', true ); ?>'],
			_cmSplit,

<?php /* Menu Sub-Menu */ ?>
			[null,'<?php echo JText::_( 'Menus', true ); ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit,

<?php /* Content Sub-Menu */ ?>
 			[null,'<?php echo JText::_( 'Content', true ); ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit,

<?php /* Components Sub-Menu */ ?>
<?php
if ($installComponents) {
	?>
			[null,'<?php echo JText::_( 'Components', true ); ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit,
	<?php
}
?>

<?php /* Extensions Sub-Menu */ ?>
<?php
if ($installModules) {
	?>
			[null,'<?php echo JText::_( 'Extensions', true ); ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit,
	<?php
}
?>

<?php /* System Sub-Menu */ ?>
<?php
if ($canConfig)	{
	?>
  			[null,'<?php echo JText::_( 'Tools', true ); ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit,
	<?php
}
?>

<?php /* Help Sub-Menu */ ?>
			[null,'<?php echo JText::_( 'Help', true ); ?>',null,null,'<?php echo $text; ?>'],
			_cmSplit

		];
		cmDraw ('myMenuID', myMenu, <?php echo ($lang->isRTL()) ? "'hbl'" : "'hbr'"; ?>, cmThemeOffice, 'ThemeOffice');
		</script>
		<?php

	}
}

/*
 * Lets get some variables we will need to render the menu
 */
$lang 	= & JFactory::getLanguage();
$doc 	= & JFactory::getDocument();
$user 	= & JFactory::getUser();
$hide 	= JRequest::getVar( 'hidemainmenu', 0 );

/*
 * TODO: Implement caching
 * $cache 	= & JFactory::getCache('jos_fullmenu');
 */

// Add the javascript to the page head
$doc->addScript('../includes/js/JSCookMenu.js');

// Load the theme script depending upon whether we are using a RTL or LTR language
if ($lang->isRTL()) {
	$doc->addScript('includes/js/ThemeOffice/theme_rtl.js');
} else {
	$doc->addScript('includes/js/ThemeOffice/theme.js');
}

/*
 * If we are disabling the menu, show the disabled menu... otherwise show the
 * full menu.
 */
if ($hide) {
	JFullAdminMenu::showDisabled($user->get('usertype'));
} else {
	JFullAdminMenu::show($user->get('usertype'));
}
?>
