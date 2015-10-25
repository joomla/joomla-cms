<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/29/14 12:54 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;

defined('CBLIB') or die();

/**
 * cbMenuBarBase Class implementation
 * Utility class for the button bar
 */
class cbMenuBarBase
{
	/**
	 * opening tag for toolbar
	 */
	static function startTable( )
	{
		global $_CB_Backend_task, $_CB_Backend_Menu, $_PLUGINS;

		$_PLUGINS->trigger( 'onBeforeToolbar', array( $_CB_Backend_task, $_CB_Backend_Menu ) );
	}

	/**
	 * builds and returns joomla specific toolbar button
	 *
	 * @param string $onClick
	 * @param string $icon
	 * @param string $alt
	 * @param string $link
	 * @param string $class
	 */
	static function _output( $onClick, $icon, $alt, $link = '#', $class = null )
	{
		$toolbar			=	JToolbar::getInstance( 'toolbar' );
		$translated			=	CBTxt::T( $alt );
		$orgIcon			=	$icon;

		if ( checkJversion( 'j3.0+' ) ) {
			$html			=	'<button' . ( ( ! $onClick ) && $link[0] == '#' ? ' value="' . htmlspecialchars( $link ) . '"' : ' onclick="' . ( $onClick ? $onClick : 'location.href=\'' . addslashes( $link ) . '\'' ) . '"' ) . ' class="btn btn-small' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . ' cbtoolbaraction">'
							.		'<i class="icon-' . htmlspecialchars( $icon ) . ' cbicon-' . htmlspecialchars( $orgIcon ) . '"></i> '
							.		htmlspecialchars( $translated )
							.	'</button> ';
		} else {
			// Map J3.0 icons to J2.5:
			$iconMap		=	array(	'mail' => 'send',
										'download' => 'export',
										'assign' => 'publish',
										'permissions' => 'options',
										'settings' => 'options'
									);

			foreach ( $iconMap as $k => $v ) {
				if ( $icon == $k ) {
					$icon	=	$v;
					break;
				}
			}

			$html			=	'<a href="' . $link . '"' . ( $onClick ? ' onclick="' . $onClick . '"' : null ) . ' class="' . ( $class ? htmlspecialchars( $class ) . ' ' : null ) . 'cbtoolbaraction">'
							.		'<span class="icon-32-' . htmlspecialchars( $icon ) . ' cbicon-32-' . htmlspecialchars( $orgIcon ) . '"></span> '
							.		htmlspecialchars( $translated )
							.	'</a>';
		}

		$toolbar->appendButton( 'Custom', $html, $icon );
	}

	/**
	 * builds and outputs custom toolbar button
	 *
	 * @param string $task
	 * @param string $icon
	 * @param string $iconOver
	 * @param string $alt
	 * @param bool $listSelect
	 * @param string $prefix
	 * @param string $class
	 */
	static function custom( $task = null, $icon = null, /** @noinspection PhpUnusedParameterInspection */ $iconOver = null, $alt = null, $listSelect = true, $prefix = null, $class = null )
	{
		if ( $listSelect ) {
			$onClick	=	"if ( document.adminForm.boxchecked.value == 0 ) {"
				.		"alert( '" . addslashes( sprintf( CBTxt::T( 'Please make a selection from the list to %s' ), CBTxt::T( $alt ) ) ) . "' );"
				.	"} else {"
				.		$prefix . "submitbutton( '$task' );"
				.	"}";
		} else {
			$onClick	=	$prefix . "submitbutton( '$task' )";
		}

		$icon			=	preg_replace( '/\.[^.]*$/', '', $icon );

		cbMenuBarBase::_output( $onClick, $icon, $alt, '#', $class );
	}

	/**
	 * builds and outputs custom toolbar button
	 * extended version of custom() calling cbhideMainMenu() before submitbutton()
	 *
	 * @param string $task
	 * @param string $icon
	 * @param string $iconOver
	 * @param string $alt
	 * @param bool $listSelect
	 * @param string $class
	 */
	static function customX( $task = null, $icon = null, $iconOver = null, $alt = null, $listSelect = true, $class = null )
	{
		CBtoolmenuBar::custom( $task, $icon, $iconOver, $alt, $listSelect, 'cbhideMainMenu();', $class );
	}

	/**
	 * standard method for displaying toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 * @param string $name
	 * @param string $imagename
	 * @param bool $extended
	 * @param string $listprompt
	 * @param string $confirmMsg
	 * @param bool $inlineJs
	 * @param string $class
	 */
	static function addToToolBar( $task, $alt, $name, $imagename, $extended = false, $listprompt = null, $confirmMsg = null, $inlineJs = true, $class = null )
	{
		if ( ! $alt ) {
			$alt	=	$name;
		}

		CBtoolmenuBar::_output( ( $inlineJs ? CBtoolmenuBar::makeJavaScript( $task, $extended, $listprompt, $confirmMsg ) : null ), $imagename, $alt, '#' . $task, $class );
	}

	/**
	 * prepare and return button form submit javascript
	 *
	 * @param string $task
	 * @param bool $extended
	 * @param string $listprompt
	 * @param string $confirmMsg
	 * @return string
	 */
	static function makeJavaScript( $task, $extended = false, $listprompt = null, $confirmMsg = null )
	{
		$script			=	'';

		if ( $listprompt ) {
			$script		.=	"if ( document.adminForm.boxchecked.value == 0 ){ alert( '$listprompt' ); } else {";
		}

		if ( $confirmMsg ) {
			$script		.=	"if ( confirm( '" . addslashes( $confirmMsg ) . "' ) ) { ";
		}

		if ( $extended ) {
			$script		.=	'cbhideMainMenu();';
		}

		$script			.=	"submitbutton( '$task' )";

		if ( $confirmMsg ) {
			$script		.=	'}';
		}

		if ( $listprompt ) {
			$script		.=	'}';
		}

		return $script;
	}

	/**
	 * find current backend template
	 *
	 * @return string
	 */
	static function getTemplate( )
	{
		global $_CB_database;

		if ( checkJversion() >= 2 ) {
			$query		=	'SELECT ' . $_CB_database->NameQuote( 'template' )
				.	"\n FROM " . $_CB_database->NameQuote( '#__template_styles' )
				.	"\n WHERE " . $_CB_database->NameQuote( 'client_id' ) . " = 1"
				.	"\n AND " . $_CB_database->NameQuote( 'home' ) . " = 1";
		} else {
			$query		=	'SELECT ' . $_CB_database->NameQuote( 'template' )
				.	"\n FROM " . $_CB_database->NameQuote( '#__templates_menu' )
				.	"\n WHERE " . $_CB_database->NameQuote( 'client_id' ) . " = 1"
				.	"\n AND " . $_CB_database->NameQuote( 'menuid' ) . " = 0";
		}

		$_CB_database->setQuery( $query );

		return $_CB_database->loadResult();
	}

	/**
	 * displays "new" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function addNew( $task = 'new', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'New', 'new', false, null, null, true, ( checkJversion( 'j3.0+' ) ? 'btn-success' : null ) );
	}

	/**
	 * displays "new" toolbar button
	 * extended version of addNew() calling cbhideMainMenu() before submitbutton()
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function addNewX( $task = 'new', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'New', 'new', true, null, null, true, ( checkJversion( 'j3.0+' ) ? 'btn-success' : null ) );
	}

	/**
	 * displays "publish" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function publish( $task = 'publish', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Publish', 'publish' );
	}

	/**
	 * displays "publish" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function publishList( $task = 'publish', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Publish', 'publish', false, CBTxt::T( 'Please make a selection from the list to publish' ) );
	}

	/**
	 * displays "default" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function makeDefault( $task = 'default', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Default', 'publish', false, CBTxt::T( 'Please select an item to make default' ) );
	}

	/**
	 * displays "assign" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function assign( $task = 'assign', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Assign', 'publish', false, CBTxt::T( 'Please select an item to assign' ) );
	}

	/**
	 * displays "unpublish" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function unpublish( $task = 'unpublish', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Unpublish', 'unpublish' );
	}

	/**
	 * displays "unpublish" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function unpublishList( $task = 'unpublish', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Unpublish', 'unpublish', false, CBTxt::T( 'Please make a selection from the list to unpublish' ) );
	}

	/**
	 * displays "archive" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function archiveList( $task = 'archive', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Archive', 'archive', false, CBTxt::T( 'Please make a selection from the list to archive' ) );
	}

	/**
	 * displays "copy" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function copyList( $task='edit', $alt=null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Copy', 'copy', false, CBTxt::T( 'Please select an item from the list to copy' ) );
	}

	/**
	 * displays "copy" toolbar button for list of rows
	 * extended version of copyList() calling hideMainMenu() before submitbutton()
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function copyListX( $task = 'edit', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Copy', 'copy', true, CBTxt::T( 'Please select an item from the list to copy' ) );
	}

	/**
	 * displays "unarchive" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function unarchiveList( $task = 'unarchive', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Unarchive', 'unarchive', false, CBTxt::T( 'Please select a news story to unarchive' ) );
	}

	/**
	 * displays "edit" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function editList( $task = 'edit', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Edit', 'edit', false, CBTxt::T( 'Please select an item from the list to edit' ) );
	}

	/**
	 * displays "edit" toolbar button for list of rows
	 * extended version of editList() calling hideMainMenu() before submitbutton()
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function editListX( $task = 'edit', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Edit', 'edit', true, CBTxt::T( 'Please select an item from the list to edit' ) );
	}

	/**
	 * displays "html" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function editHtml( $task = 'edit_source', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Edit HTML', 'html', false, CBTxt::T( 'Please select an item from the list to edit' ) );
	}

	/**
	 * displays "html" toolbar button for list of rows
	 * extended version of editHtml() calling hideMainMenu() before submitbutton()
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function editHtmlX( $task='edit_source', $alt=null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Edit HTML', 'html', true, CBTxt::T( 'Please select an item from the list to edit' ) );
	}

	/**
	 * displays "css" toolbar button for list of rows
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function editCss( $task = 'edit_css', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Edit CSS', 'css', false, CBTxt::T( 'Please select an item from the list to edit' ) );
	}

	/**
	 * displays "css" toolbar button for list of rows
	 * extended version of editCss() calling hideMainMenu() before submitbutton()
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function editCssX( $task = 'edit_css', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Edit CSS', 'css', true, CBTxt::T( 'Please select an item from the list to edit' ) );
	}

	/**
	 * displays "delete" toolbar button for list of rows
	 *
	 * @param string $msg
	 * @param string $task
	 * @param string $alt
	 */
	static function deleteList( $msg = null, $task = 'remove', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Delete', 'delete', false, CBTxt::T( 'Please make a selection from the list to delete' ), CBTxt::T( 'Are you sure you want to delete the selected items?' ) . ( $msg ? ' ' . $msg : null ) );
	}

	/**
	 * displays "delete" toolbar button for list of rows
	 * extended version of deleteList() calling hideMainMenu() before submitbutton()
	 *
	 * @param string $msg
	 * @param string $task
	 * @param string $alt
	 */
	static function deleteListX( $msg = null, $task = 'remove', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Delete', 'delete', true, CBTxt::T( 'Please make a selection from the list to delete' ), CBTxt::T( 'Are you sure you want to delete the selected items?' ) . ( $msg ? ' ' . $msg : null ) );
	}

	/**
	 * displays "trash" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 */
	static function trash( $task = 'remove', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Trash', 'delete' );
	}

	/**
	 * displays "preview" toolbar button
	 *
	 * @param string $popup
	 * @param bool $updateEditors
	 */
	static function preview( $popup = null, $updateEditors = false )
	{
		global $_CB_framework;

		$template		=	CBtoolmenuBar::getTemplate();

		$js				=	"function popup() {";

		if ( $updateEditors ) {
			$js			.=		"if ( $popup == 'contentwindow' ) {"
				.			$_CB_framework->saveCmsEditorJS( 'introtext' )
				.			$_CB_framework->saveCmsEditorJS( 'fulltext' )
				.		"} elseif ( $popup == 'modulewindow' ) {"
				.			$_CB_framework->saveCmsEditorJS( 'content' )
				.		"}";
		}

		$js				.=		"window.open( '" . $_CB_framework->backendUrl( "index.php?pop=/$popup.php&t=$template", true, 'component' ) . "', 'win1', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no' );"
			.	"}";

		$_CB_framework->document->addHeadScriptDeclaration( $js );

		CBtoolmenuBar::_output( 'popup();', 'preview', 'Preview' );
	}

	/**
	 * displays "help" toolbar button
	 *
	 * @param string $ref
	 * @param string $option
	 * @param bool $com
	 */
	static function help( $ref, $option = 'com_comprofiler', $com = false )
	{
		global $_CB_framework;

		if ( cbStartOfStringMatch( $ref, 'http' ) ) {
			$url			=	$ref;
		} else {
			$liveSite		=	$_CB_framework->getCfg( 'live_site' );
			$rootpath		=	$_CB_framework->getCfg( 'absolute_path' );

			if ( substr( $option, 0, 4 ) != 'com_' ) {
				$option		=	"com_$option";
			}

			$component		=	substr( $option, 4 );

			if ( $com ) {
				$url		=	'/administrator/components/' . $option . '/help/';
			} else {
				$url		=	'/help/';
			}

			$ref			=	$component . '.' . $ref . '.html';
			$url			.=	$ref;

			if ( ! file_exists( $rootpath . '/help/' . $ref ) ) {
				return;
			}

			$url			=	$liveSite . $url;
		}

		$onClickJs			=	"window.open( '$url', 'mambo_help_win', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1024,height=640,directories=no,location=no' );";

		if ( checkJversion( '3.0+' ) ) {
			JToolbarHelper::help( 'help', false, $url );
		} else {
			CBtoolmenuBar::_output( $onClickJs, 'help', 'Help' );
		}
	}

	/**
	 * displays "apply" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 * @param bool $inlineJs
	 */
	static function apply( $task = 'apply', $alt = null, $inlineJs = true  )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Apply', 'apply', false, '', '', $inlineJs, ( checkJversion( 'j3.0+' ) ? 'btn-success' : null ) );
	}

	/**
	 * displays "save" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 * @param bool $inlineJs
	 */
	static function save( $task = 'save', $alt = null, $inlineJs = true )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Save', 'save', false, '', '', $inlineJs );
	}

	/**
	 * displays "savenew" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 * @depricated 2.0
	 */
	static function savenew( $task = 'save', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Save', 'savenew' );
	}

	/**
	 * displays "saveedit" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 * @depricated 2.0
	 */
	static function saveedit( $task = 'save', $alt = null )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Save', 'saveedit' );
	}

	/**
	 * displays "cancel" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 * @param bool $inlineJs
	 */
	static function cancel( $task = 'cancel', $alt = null, $inlineJs = true )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Cancel', 'cancel', false, '', '', $inlineJs );
	}

	/**
	 * displays "back" toolbar button
	 *
	 * @param string $alt
	 * @param string $href
	 */
	static function back( $alt = null, $href = null )
	{
		if ( ! $alt ) {
			$alt 		= 'Back';
		}

		if ( $href ) {
			$link		=	$href;
			$onClickJs	=	null;
		} else {
			$link		=	'#';
			$onClickJs	=	'window.history.go(-1);return false;';
		}

		CBtoolmenuBar::_output( $onClickJs, ( checkJversion( '3.0+' ) ? 'undo' : 'back' ), $alt, $link );
	}

	/**
	 * displays a spacer to be used between buttons
	 */
	static function divider( )
	{
		JToolBarHelper::divider();
	}

	/**
	 * displays "media manager" toolbar button
	 *
	 * @param string $directory
	 * @param string $alt
	 */
	static function media_manager( $directory = null, $alt = null )
	{
		global $_CB_framework;

		if ( ! $alt ) {
			$alt	=	'Upload';
		}

		$template	=	CBtoolmenuBar::getTemplate();
		$image		=	cbMenuBarBase::ImageCheckAdmin( 'upload.png', '/administrator/images/', NULL, NULL, CBTxt::T( 'Upload Image' ), 'uploadPic' );
		$onClickJs	=	"popupWindow( '" . $_CB_framework->backendUrl( "index.php?pop=uploadimage.php&directory=$directory&t=$template", true, 'component' ) . "','win1',350,100,'no' );";

		CBtoolmenuBar::_output( $onClickJs, $image, $alt );
	}

	/**
	 * displays a spacer to be used between buttons
	 *
	 * @param int $width
	 */
	static function spacer( $width = null )
	{
		JToolBarHelper::spacer( $width );
	}

	/**
	 * closing tag for toolbar
	 */
	static function endTable( )
	{
		global $_CB_Backend_task, $_CB_Backend_Menu, $_PLUGINS;

		$_PLUGINS->trigger( 'onAfterToolbar', array( $_CB_Backend_task, $_CB_Backend_Menu ) );
	}

	/**
	 * checks if image exists in template
	 * if it does exist render it otherwise render default
	 *
	 * @param string $file
	 * @param string $directory
	 * @param string $param
	 * @param string $param_directory
	 * @param string $alt
	 * @param string $name
	 * @param int $type
	 * @param string $align
	 * @return string
	 */
	static function ImageCheckAdmin( $file, $directory = '/administrator/images/', $param = null, $param_directory = '/administrator/images/', $alt = null, $name = null, $type = 1, $align = 'middle' )
	{
		global $_CB_framework;

		$live_site		=	$_CB_framework->getCfg( 'live_site' );
		$mainframe		=	$_CB_framework->_baseFramework;
		$template 		=	$mainframe->getTemplate();

		if ( $param ) {
			$image		=	$live_site . $param_directory . $param;
		} else {
			if ( file_exists( $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/templates/' . $template . '/images/' . $file ) ) {
				$image	=	$live_site . '/administrator/templates/' . $template . '/images/' . $file;
			} else {
				$image	=	$live_site . $directory . $file;
			}
		}

		if ( $type ) {
			$image		=	'<img src="' . $image . '" alt="' . $alt . '" align="' . $align . '" name="' . $name . '" border="0" />';
		}

		return $image;
	}
}
