<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/29/14 1:00 PM $
* @package ${NAMESPACE}
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\AhaWow\View\RegistryEditView;
use CBLib\Language\CBTxt;
use CBLib\Xml\SimpleXMLElement;

defined('CBLIB') or die();

/**
 * ${NAMESPACE}\TOOLBAR_usersextras Class implementation
 * 
 */
class TOOLBAR_usersextras
{
	/**
	 * Draws the menu for a New users
	 */
	static function _NEW( )
	{
		global $_CB_framework;

		CBtoolmenuBar::startTable();
		CBtoolmenuBar::apply( 'apply', CBTxt::T( 'TOOLEBAR_SAVE_NEW TOOLBAR_SAVE', 'Save' ) );
		CBtoolmenuBar::save( 'save', CBTxt::T( 'TOOLEBAR_SAVE_CLOSE_NEW TOOLBAR_SAVE_CLOSE', 'Save & Close' ) );
		CBtoolmenuBar::linkAction( 'cancel', $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=showusers' ), 'Cancel' );
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::help( 'http://www.joomlapolis.com/documentation/community-builder/help/user-management-new?pk_campaign=in-cb&amp;pk_kwd=help' );
		CBtoolmenuBar::endTable();
	}

	/**
	 * Edit user
	 */
	static function _EDIT( )
	{
		global $_CB_framework;

		CBtoolmenuBar::startTable();
		CBtoolmenuBar::apply( 'apply', CBTxt::T( 'TOOLBAR_SAVE_EDIT TOOLBAR_SAVE', 'Save' ) );
		CBtoolmenuBar::save( 'save', CBTxt::T( 'TOOLBAR_SAVE_CLOSE_EDIT TOOLBAR_SAVE_CLOSE', 'Save & Close' ) );
		CBtoolmenuBar::linkAction( 'cancel', $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=showusers' ), 'Cancel' );
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::help( 'http://www.joomlapolis.com/documentation/community-builder/help/user-management-edit?pk_campaign=in-cb&amp;pk_kwd=help' );
		CBtoolmenuBar::endTable();
	}

	static function _TOOLS( )
	{
		global $_CB_framework;

		CBtoolmenuBar::startTable();
		CBtoolmenuBar::linkAction( 'cancel', $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=tools' ), 'Close' );
		CBtoolmenuBar::endTable();
	}

	private static function _TRANSLATECONFIGTITLE( $assetname, $text )
	{
		global $_CB_framework;
		$_CB_framework->outputCbJQuery('$(".cboptionsbutton_' . $assetname . '").click( function() {
	    var mytimer = setInterval( function() {
			var myframe = $("iframe");
			if ( myframe.length ) {
		    	var doc = myframe[0].contentWindow.document;
    	        var mybody = $("body .configuration",doc);
    	        if ( mybody.length ) {
		            mybody.html("' . addslashes( $text ) . '");
		            clearInterval( mytimer );
		        }
		    }
	    }, 10 );
	    });');

	}

	static function _PERMISSIONS( $assetname =  'com_comprofiler', $headerHtml = null )
	{
		if ( checkJversion() >= 2 ) {
			if ( JFactory::getUser()->authorise('core.admin', $assetname ) ) {
				// JText::_($string);
				JToolBarHelper::preferences( $assetname );

				if ( ! checkJversion( 'j3.0+' ) ) {
					echo str_replace( '<a class="modal"', '<a class="modal cboptionsbutton cboptionsbutton_' . $assetname . '"', JToolBar::getInstance( 'toolbar' )->render() );
					if ( $headerHtml ) {
						global $_CB_framework;
						$_CB_framework->outputCbJQuery( '$(".cboptionsbutton_' . $assetname . '").click( function() { $("#sbox-window #sbox-content").before("' . addslashes( $headerHtml ) . '")} ); $(document).on("click", ".configuration", function() { $(this).css("color","red"); } );' );
					}
				}
			}
		}
	}

	static function _EMAIL_USERS( )
	{
		global $_CB_framework;

		CBtoolmenuBar::startTable();
		CBtoolmenuBar::addToToolBar( 'startemailusers', 'Send Mails', 'Send Mails', 'mail' );
		//CBtoolmenuBar::custom( 'startemailusers', 'mail.png', 'mail.png', 'Send Mails', false );
		CBtoolmenuBar::linkAction( 'cancel', $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=showusers' ), 'Cancel' );
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::help( 'http://www.joomlapolis.com/documentation/community-builder/help/user-management-mass-mailer?pk_campaign=in-cb&amp;pk_kwd=help' );
		CBtoolmenuBar::endTable();
	}

	static function _PLUGIN_ACTION_SHOW( )
	{
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::cancel( 'cancelPluginAction', 'Close' );
		CBtoolmenuBar::endTable();
	}

	static function _PLUGIN_ACTION_EDIT( )
	{
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('savePlugin');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::apply('applyPlugin');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::cancel( 'cancelPluginAction', 'Close' );
		CBtoolmenuBar::endTable();
	}

	/**
	 * @param  SimpleXMLElement[]  $xmlToolbarMenuArray
	 * @return void
	 */
	static function _PLUGIN_MENU( $xmlToolbarMenuArray )
	{
		if ( $xmlToolbarMenuArray && ( count( $xmlToolbarMenuArray ) > 0 ) ) {
			$started						=	false;
			foreach ( $xmlToolbarMenuArray as $xmlTBmenu ) {
				if ( $xmlTBmenu && ( count( $xmlTBmenu->children() ) > 0 ) ) {
					foreach ( $xmlTBmenu->children() as $menu ) {
						/** @var SimpleXMLElement $menu */
						if ( $menu->getName() == 'menu' ) {
							// $name			=	$menu->attributes( 'name' );
							$action			=	$menu->attributes( 'action' );
							$task			=	$menu->attributes( 'task' );
							$label			=	$menu->attributes( 'label' );
							$class			=	RegistryEditView::buildClasses( $menu );
							$description	=	$menu->attributes( 'description' );

							if ( in_array( $action, get_class_methods( 'CBtoolmenuBar' ) ) || in_array( strtolower( $action ), get_class_methods( 'CBtoolmenuBar' ) ) ) {		// PHP 5 || PHP 4
								if ( ! $started ) {
									CBtoolmenuBar::startTable();
									$started		=	true;
								}
								switch ( $action ) {
									case 'custom':
									case 'customX':
										$icon		=	$menu->attributes( 'icon' );
										$iconOver	=	$menu->attributes( 'iconover' );
										CBtoolmenuBar::$action( $task, $icon, $iconOver, $label, false, null, $class );
										break;
									case 'editList':
										CBtoolmenuBar::editListNoSelect( $task, $label );
										break;
									case 'deleteList':
									case 'deleteListX':
										$message	=	$menu->attributes( 'message' );
										CBtoolmenuBar::$action( $message, $task, $label );
										break;
									case 'trash':
										CBtoolmenuBar::$action( $task, $label, false );
										break;
									case 'preview':
										$popup	=	$menu->attributes( 'popup' );
										CBtoolmenuBar::$action( $popup, true );
										break;
									case 'help':
										$ref	=	$menu->attributes( 'href' );
										if ( ! $ref ) {
											// Backwards compatibility to CB 1.x:
											$ref	=	$menu->attributes( 'ref' );
										}
										CBtoolmenuBar::$action( $ref, true );
										break;
									case 'divider':
									case 'spacer':
										CBtoolmenuBar::$action();
										break;
									case 'back':
										$href	=	$menu->attributes( 'href' );
										CBtoolmenuBar::$action( $label, $href );
										break;
									case 'media_manager':
										$directory	=	$menu->attributes( 'directory' );
										CBtoolmenuBar::$action( $directory, $label );
										break;
									case 'linkAction':
										$urllink	=	$menu->attributes( 'urllink' );
										if ( $menu->attributes( 'task' ) == 'new' ) {
											CBtoolmenuBar::$action( $task, $urllink, $label, ( $class ? $class : ( checkJversion( 'j3.0+' ) ? 'btn-success' : null ) ) );
										} else {
											CBtoolmenuBar::$action( $task, $urllink, $label, $class );
										}
										break;
									default:
										CBtoolmenuBar::$action( $task, $label );
										break;
								}
							} elseif ( $action == 'permissions' ) {
								if ( $description ) {
									$headerHtml		=	'<div class="cbbejeoptionsintro cbbejeoptionsintro' . htmlspecialchars( $task ) .'">'
										.		$description
										.	'</div>';
								} else {
									$headerHtml		=	null;
								}

								self::_PERMISSIONS( $task, $headerHtml );

								if ( $label ) {
									self::_TRANSLATECONFIGTITLE( $task, $label );
								}
							}
							// if ( in_array( $action, array(	'customX', 'addNew', 'addNewX', 'publish', 'publishList', 'makeDefault', 'assign', 'unpublish', 'unpublishList',
							//								'archiveList', 'unarchiveList', ) ) ) {
							// nothing
							// }
						}
					}
				}
			}
			if ( $started ) {
				CBtoolmenuBar::endTable();
			}
		}
	}

	static function _DEFAULT_PLUGIN_MENU( )
	{
		global $_CB_framework;

		CBtoolmenuBar::startTable();
		CBtoolmenuBar::linkAction( 'cancel', $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=showPlugins' ), 'Close' );
		CBtoolmenuBar::endTable();
	}
}
