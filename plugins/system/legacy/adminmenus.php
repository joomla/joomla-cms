<?php
/**
* @version		$Id$
* @package		Joomla.Legacy
* @subpackage	1.5
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Utility class for drawing admin menu HTML elements
 *
 * @static
 * @package 	Joomla.Legacy
 * @subpackage	1.5
 * @since	1.0
 * @deprecated	As of version 1.5
 */
class mosAdminMenus
{
	/**
 	 * Legacy function, use {@link JHTML::_('menu.ordering')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Ordering( &$row, $id )
	{
		return JHTML::_('menu.ordering', $row, $id);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.accesslevel', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Access( &$row )
	{
		return JHTML::_('list.accesslevel', $row);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Published( &$row )
	{
		$published = JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published );
		return $published;
	}

	/**
 	 * Legacy function, use {@link JAdminMenus::MenuLinks()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function MenuLinks( &$lookup, $all=NULL, $none=NULL, $unassigned=1 )
	{
		$options = JHTML::_('menu.linkoptions', $lookup, $all, $none|$unassigned);
		if (empty( $lookup )) {
			$lookup = array( JHTML::_('select.option',  -1 ) );
		}
		$pages = JHTML::_('select.genericlist',   $options, 'selections[]', 'class="inputbox" size="15" multiple="multiple"', 'value', 'text', $lookup, 'selections' );
		return $pages;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Category( &$menu, $id, $javascript='' )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( " / ", s.title, c.title) AS `text`'
		. ' FROM #__sections AS s'
		. ' INNER JOIN #__categories AS c ON c.section = s.id'
		. ' WHERE s.scope = "content"'
		. ' ORDER BY s.name, c.name'
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		$category = '';

		$category .= JHTML::_('select.genericlist',   $rows, 'componentid', 'class="inputbox" size="10"'. $javascript, 'value', 'text', $menu->componentid );
		$category .= '<input type="hidden" name="link" value="" />';

		return $category;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Section( &$menu, $id, $all=0 )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT s.id AS `value`, s.id AS `id`, s.title AS `text`'
		. ' FROM #__sections AS s'
		. ' WHERE s.scope = "content"'
		. ' ORDER BY s.name'
		;
		$db->setQuery( $query );
		if ( $all ) {
			$rows[] = JHTML::_('select.option',  0, '- '. JText::_( 'All Sections' ) .' -' );
			$rows = array_merge( $rows, $db->loadObjectList() );
		} else {
			$rows = $db->loadObjectList();
		}

		$section = JHTML::_('select.genericlist',   $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid );
		$section .= '<input type="hidden" name="link" value="" />';

		return $section;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Component( &$menu, $id )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT c.id AS value, c.name AS text, c.link'
		. ' FROM #__components AS c'
		. ' WHERE c.link <> ""'
		. ' ORDER BY c.name'
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList( );

		$component = JHTML::_('select.genericlist',   $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid, '', 1 );

		return $component;
	}


	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ComponentName( &$menu, $id )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT c.id AS value, c.name AS text, c.link'
		. ' FROM #__components AS c'
		. ' WHERE c.link <> ""'
		. ' ORDER BY c.name'
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList( );

		$component = 'Component';
		foreach ( $rows as $row ) {
			if ( $row->value == $menu->componentid ) {
				$component = JText::_( $row->text );
			}
		}

		return $component;
	}


	/**
 	 * Legacy function, use {@link JHTML::_('list.images', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Images( $name, &$active, $javascript=NULL, $directory=NULL )
	{
		return JHTML::_('list.images', $name, $active, $javascript, $directory);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.specificordering', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function SpecificOrdering( &$row, $id, $query, $neworder=0 )
	{
		return JHTML::_('list.specificordering', $row, $id, $query, $neworder);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.users', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function UserSelect( $name, $active, $nouser=0, $javascript=NULL, $order='name', $reg=1 )
	{
		return JHTML::_('list.users', $name, $active, $nouser, $javascript, $order, $reg);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.positions', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Positions( $name, $active=NULL, $javascript=NULL, $none=1, $center=1, $left=1, $right=1, $id=false )
	{
		return JHTML::_('list.positions', $name, $active, $javascript, $none, $center, $left, $right, $id);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.category', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ComponentCategory( $name, $section, $active=NULL, $javascript=NULL, $order='ordering', $size=1, $sel_cat=1 )
	{
		return JHTML::_('list.category', $name, $section, $active, $javascript, $order, $size, $sel_cat);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.section', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function SelectSection( $name, $active=NULL, $javascript=NULL, $order='ordering' )
	{
		return JHTML::_('list.section', $name, $active, $javascript, $order);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Links2Menu( $type, $and )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT * '
		. ' FROM #__menu '
		. ' WHERE type = '.$db->Quote($type)
		. ' AND published = 1'
		. $and
		;
		$db->setQuery( $query );
		$menus = $db->loadObjectList();

		return $menus;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function MenuSelect( $name='menuselect', $javascript=NULL )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT params'
		. ' FROM #__modules'
		. ' WHERE module = "mod_mainmenu"'
		;
		$db->setQuery( $query );
		$menus = $db->loadObjectList();
		$total = count( $menus );
		$menuselect = array();
		for( $i = 0; $i < $total; $i++ )
		{
			$registry = new JRegistry();
			$registry->loadINI($menus[$i]->params);
			$params = $registry->toObject( );

			$menuselect[$i]->value 	= $params->menutype;
			$menuselect[$i]->text 	= $params->menutype;
		}
		// sort array of objects
		JArrayHelper::sortObjects( $menuselect, 'text', 1 );

		$menus = JHTML::_('select.genericlist',   $menuselect, $name, 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $menus;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ReadImages( $imagePath, $folderPath, &$folders, &$images )
	{
		jimport( 'joomla.filesystem.folder' );
		$imgFiles = JFolder::files( $imagePath );

		foreach ($imgFiles as $file)
		{
			$ff_ 	= $folderPath.DS.$file;
			$ff 	= $folderPath.DS.$file;
			$i_f 	= $imagePath .'/'. $file;

			if ( is_dir( $i_f ) && $file <> 'CVS' && $file <> '.svn') {
				$folders[] = JHTML::_('select.option',  $ff_ );
				mosAdminMenus::ReadImages( $i_f, $ff_, $folders, $images );
			} else if ( eregi( "bmp|gif|jpg|png", $file ) && is_file( $i_f ) ) {
				// leading / we don't need
				$imageFile = substr( $ff, 1 );
				$images[$folderPath][] = JHTML::_('select.option',  $imageFile, $file );
			}
		}
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function GetImageFolders( &$folders, $path )
	{
		$javascript 	= "onchange=\"changeDynaList( 'imagefiles', folderimages, document.adminForm.folders.options[document.adminForm.folders.selectedIndex].value, 0, 0);  previewImage( 'imagefiles', 'view_imagefiles', '$path/' );\"";
		$getfolders 	= JHTML::_('select.genericlist',   $folders, 'folders', 'class="inputbox" size="1" '. $javascript, 'value', 'text', '/' );
		return $getfolders;
	}

	/**
	 * Legacy function, deprecated
	 *
	 * @deprecated	As of version 1.5
	 */
	function GetImages( &$images, $path )
	{
		if ( !isset($images['/'] ) ) {
			$images['/'][] = JHTML::_('select.option',  '' );
		}

		//$javascript	= "onchange=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\" onfocus=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\"";
		$javascript	= "onchange=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\"";
		$getimages	= JHTML::_('select.genericlist',   $images['/'], 'imagefiles', 'class="inputbox" size="10" multiple="multiple" '. $javascript , 'value', 'text', null );

		return $getimages;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function GetSavedImages( &$row, $path )
	{
		$images2 = array();
		foreach( $row->images as $file ) {
			$temp = explode( '|', $file );
			if( strrchr($temp[0], '/') ) {
				$filename = substr( strrchr($temp[0], '/' ), 1 );
			} else {
				$filename = $temp[0];
			}
			$images2[] = JHTML::_('select.option',  $file, $filename );
		}
		//$javascript	= "onchange=\"previewImage( 'imagelist', 'view_imagelist', '$path/' ); showImageProps( '$path/' ); \" onfocus=\"previewImage( 'imagelist', 'view_imagelist', '$path/' )\"";
		$javascript	= "onchange=\"previewImage( 'imagelist', 'view_imagelist', '$path/' ); showImageProps( '$path/' ); \"";
		$imagelist 	= JHTML::_('select.genericlist',   $images2, 'imagelist', 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $imagelist;
	}

	/**
 	 * Legacy function, use {@link JHTML::_('image.site')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ImageCheck( $file, $directory='/images/M_images/', $param=NULL, $param_directory='/images/M_images/', $alt=NULL, $name='image', $type=1, $align='top' )
	{
		$attribs = array('align' => $align);
		return JHTML::_('image.site', $file, $directory, $param, $param_directory, $alt, $attribs, $type);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('image.administrator')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ImageCheckAdmin( $file, $directory='/images/', $param=NULL, $param_directory='/images/', $alt=NULL, $name=NULL, $type=1, $align='middle' )
	{
		$attribs = array('align' => $align);
		return JHTML::_('image.administrator', $file, $directory, $param, $param_directory, $alt, $attribs, $type);
	}

	/**
 	 * Legacy function, use {@link MenusHelper::getMenuTypes()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function menutypes()
	{
		JError::raiseNotice( 0, 'mosAdminMenus::menutypes method deprecated' );
	}

	/**
 	 * Legacy function, use {@link MenusHelper::menuItem()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function menuItem( $item )
	{
		JError::raiseNotice( 0, 'mosAdminMenus::menuItem method deprecated' );
	}
}