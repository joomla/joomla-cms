<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\AhaWow\Controller\ActionController;
use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CBLib\Registry\Registry;
use CBLib\Xml\SimpleXMLElement;

use CB\Database\Table\PluginTable;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBController_plugin {

	/**
	 * Saves legacy plugin edit display
	 *
	 * @param string $option
	 * @param string $task
	 * @deprecated 2.0 Use XML
	 */
	public function savePlugin( $option, $task ) {
		global $_CB_framework, $_PLUGINS;

		if ( $task == 'showPlugins' ) {
			cbRedirect( $_CB_framework->backendViewUrl( 'showPlugins', false ) );
		}

		cbimport( 'language.all' );
		cbimport( 'cb.tabs' );
		cbimport( 'cb.params' );
		cbimport( 'cb.adminfilesystem' );
		cbimport( 'cb.imgtoolbox' );

		$action					=	cbGetParam( $_REQUEST, 'action' );

		if ( $action ) {
			$uid				=	cbGetParam( $_REQUEST, 'cid' );
			$row				=	new PluginTable();

			if ( $uid ) {
				$row->load( (int) $uid );
			}

			// get params values
			$_PLUGINS->loadPluginGroup( $row->type, array( (int) $row->id ), 0 );

			// xml file for plugin
			$element			=	$_PLUGINS->loadPluginXML( 'action', $action, $row->id );

			$_REQUEST['task']	=	'editPlugin'; // so that the actionPath matches

			$params				=	new Registry( $row->params );

			/** @noinspection PhpDeprecationInspection */
			$this->editPluginView( $row, $option, 'editPlugin', $uid, $action, $element, $task, $params );
		}
	}

	/**
	 * Outputs legacy plugin edit display
	 *
	 * @param string  $option
	 * @param string  $task
	 * @param int     $uid
	 * @deprecated 2.0 Use XML
	 */
	public function editPlugin( $option, $task, $uid ) {
		global $_CB_framework, $_PLUGINS, $_POST;

		cbimport( 'language.all' );
		cbimport( 'cb.tabs' );
		cbimport( 'cb.params' );

		$action					=	cbGetParam( $_REQUEST, 'action', null );

		if ( ! $uid ) {
			$uid				=	cbGetParam( $_POST, 'id' );
		}

		$row					=	new PluginTable();

		if ( $uid ) {
			// load the row from the db table
			$row->load( (int) $uid );

			// Check if user is a super user:
			if ( ! Application::MyUser()->isSuperAdmin() ) {
				// Check if user belongs to access:
				if ( ! Application::MyUser()->canViewAccessLevel( $row->viewaccesslevel ) ) {
					cbRedirect( $_CB_framework->backendViewUrl( 'showPlugins', false ), CBTxt::T( 'Unauthorized Access' ), 'error' );
				}
			}
		}

		// fail if checked out not by 'me'
		if ( $row->checked_out && ( $row->checked_out <> $_CB_framework->myId() ) ) {
			cbRedirect(
						$_CB_framework->backendViewUrl( 'showPlugins', false ),
						CBTxt::T( 'THE_PLUGIN_NAME_IS_CURRENTLY_BEING_EDITED_BY_ANOTHER_ADMINISTRATOR', 'The plugin [name] is currently being edited by another administrator', array( '[name]' => $row->name ) ),
						'error'
					  );
		}

		// get params values
		$_PLUGINS->loadPluginGroup( $row->type, array( (int) $row->id ), 0 );

		// xml file for plugin
		$element				=	null;

		if ( $uid ) {
			$element			=	$_PLUGINS->loadPluginXML( 'action', $action, $row->id );
		}

		if ( $element && ( $action === null ) ) {
			$adminActionsModel	=	$element->getChildByNameAttr( 'actions', 'ui', 'admin' );

			if ( $adminActionsModel ) {
				$defaultAction	=	$adminActionsModel->getChildByNameAttr( 'action', 'name', 'default' );
				$actionRequest	=	$defaultAction->attributes( 'request' );
				$actionAction	=	$defaultAction->attributes( 'action' );

				if ( ( $actionRequest === '' ) && ( $actionRequest === '' ) ) {
					$action		=	$actionAction;
				}
			}
		}

		if ( $element ) {
			$description		=	$element->getChildByNameAttributes( 'description' );
			$author				=	$element->getChildByNameAttributes( 'author' );
			$authorEmail		=	$element->getChildByNameAttributes( 'authorEmail' );
			$authorUrl			=	$element->getChildByNameAttributes( 'authorUrl' );
			$copyright			=	$element->getChildByNameAttributes( 'copyright' );
			$copyrightUrl		=	$element->getChildByNameAttributes( 'copyrightUrl' );
			$license			=	$element->getChildByNameAttributes( 'license' );
			$licenseUrl			=	$element->getChildByNameAttributes( 'licenseUrl' );
		} else {
			$description		=	null;
			$author				=	null;
			$authorEmail		=	null;
			$authorUrl			=	null;
			$copyright			=	null;
			$copyrightUrl		=	null;
			$license			=	null;
			$licenseUrl			=	null;
		}

		if ( ( $description !== null ) && ( $description !== false ) ) {
			$row->description	=	$description->data();
		} else {
			$row->description	=	'-';
		}

		if ( ( $author !== null ) && ( $author !== false ) ) {
			$row->author		=	$author->data();
		} else {
			$row->author		=	null;
		}

		if ( ( $authorEmail !== null ) && ( $authorEmail !== false ) ) {
			$row->authorEmail	=	$authorEmail->data();
		} else {
			$row->authorEmail	=	null;
		}

		if ( ( $authorUrl !== null ) && ( $authorUrl !== false ) ) {
			$row->authorUrl		=	$authorUrl->data();
		} else {
			$row->authorUrl		=	null;
		}

		if ( ( $copyright !== null ) && ( $copyright !== false ) ) {
			$row->copyright		=	$copyright->data();
		} else {
			$row->copyright		=	null;
		}

		if ( ( $copyrightUrl !== null ) && ( $copyrightUrl !== false ) ) {
			$row->copyrightUrl	=	$copyrightUrl->data();
		} else {
			$row->copyrightUrl	=	null;
		}

		if ( ( $license !== null ) && ( $license !== false ) ) {
			$row->license		=	$license->data();
		} else {
			$row->license		=	null;
		}

		if ( ( $licenseUrl !== null ) && ( $licenseUrl !== false ) ) {
			$row->licenseUrl	=	$licenseUrl->data();
		} else {
			$row->licenseUrl	=	null;
		}

		if ( $action !== null ) {
			$params				=	new Registry( $row->params );

			$this->editPluginView( $row, $option, $task, $uid, $action, $element, 'editPlugin', $params );
		}
	}

	/**
	 * Outputs legacy plugin views
	 *
	 * @deprecated 2.0 Use XML
	 *
	 * @param  PluginTable       $row
	 * @param  string            $option
	 * @param  string            $task
	 * @param  int               $uid
	 * @param  string            $action
	 * @param  SimpleXMLElement  $element
	 * @param  string            $mode
	 * @param  Registry          $pluginParams
	 * @return mixed|null
	 */
	public function editPluginView( $row, $option, $task, $uid, $action, $element, $mode, $pluginParams ) {
		global $_CB_framework, $_CB_database, $_PLUGINS;

		if ( ! $row->id ) {
			$_CB_framework->enqueueMessage( CBTxt::T( 'Plugin id not found.' ), 'error' );
			return null;
		}

		if ( ! $element ) {
			$_CB_framework->enqueueMessage( CBTxt::T( 'No plugin XML found.' ), 'error' );
			return null;
		}

		$adminHandlerModel				=	$element->getChildByNameAttr( 'handler', 'ui', 'admin' );

		if ( ( ! $adminHandlerModel ) || ( $row->element == 'cbpaidsubscriptions' ) ) {
			$adminActionsModel			=	$element->getChildByNameAttr( 'actions', 'ui', 'admin' );

			if ( $adminActionsModel ) {
				// New CB 2.0 method:
				$savedPluginId			=	$_PLUGINS->_loading;
				$_PLUGINS->_loading		=	(int) $row->id;

				/** @var ActionController $actionController */
				/** @see CBLib\AhaWow\Controller\ActionController::__construct() */
				$actionController		=	Application::DI()->get( 'CBLib\AhaWow\Controller\ActionController' );

				$actionController->setData( $row );

				$displayMode			=	( $mode == 'applyPlugin' ? 'apply' : ( $mode == 'savePlugin' ? 'save' : 'edit' ) );

				echo $actionController->drawView( $option, null, $element, $displayMode );

				$_PLUGINS->_loading		=	$savedPluginId;

				return null;
			} else {
				return null;
			}
		}

		$class							=	$adminHandlerModel->attributes( 'class' );

		if ( $class ) {
			if ( ! class_exists( $class ) ) {
				$_CB_framework->enqueueMessage( CBTxt::T( 'ADMIN_HANDLER_CLASS_CLASS_DOES_NOT_EXIST', 'Admin handler class [class] does not exist.', array( '[class]' => $class ) ), 'error' );
				return null;
			}

			$handler					=	new $class( $_CB_database );

			/** @var stdClass|CBController_plugin|cbpaidAdminView  $handler */
			return $handler->editPluginView( $row, $option, $task, $uid, $action, $element, $mode, $pluginParams );
		} else {
			// new method in CB 1.2.3:
			$args						=	array( &$row, $option, $task, $uid, $action, &$element, $mode, &$pluginParams );

			return $_PLUGINS->call( $row->id, 'editPluginView', 'get' . $row->element . 'Tab', $args, null, true );
		}
	}

	/**
	 * Outputs legacy plugin menu display
	 *
	 * @param string  $option
	 * @param int     $uid
	 * @deprecated 2.0 Use XML
	 */
	public function pluginMenu( /** @noinspection PhpUnusedParameterInspection */ $option, $uid ) {
		global $_CB_framework, $_PLUGINS, $_GET;

		if ( ! $uid ) {
			$_CB_framework->enqueueMessage( CBTxt::T( 'No plugin selected' ), 'error' );
			return;
		}

		cbimport( 'language.all' );
		cbimport( 'cb.tabs' );
		cbimport( 'cb.params' );

		$row			=	new PluginTable();

		// load the row from the db table
		$row->load( (int) $uid );

		// fail if checked out not by 'me'
		if ( $row->checked_out && ( $row->checked_out <> $_CB_framework->myId() ) ) {
			cbRedirect( $_CB_framework->backendViewUrl( 'showPlugins', false ), CBTxt::T( 'THE_PLUGIN_NAME_IS_CURRENTLY_BEING_EDITED_BY_ANOTHER_ADMINISTRATOR', 'The plugin [name] is currently being edited by another administrator', array( '[name]' => $row->name ) ), 'error' );
		}

		$basepath		=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $_PLUGINS->getPluginRelPath( $row ) . '/';
		$phpfile		=	$basepath . 'admin.' . $row->element . '.php';

		// see if there is an xml install file, must be same name as element
		if (file_exists( $phpfile )) {
			// get params values
			$_PLUGINS->loadPluginGroup( $row->type, array( (int) $row->id ), 0 );

			$menu		=	cbGetParam( $_REQUEST, 'menu' );
			$element	=	$_PLUGINS->loadPluginXML( 'menu', $menu, (int) $row->id ); // xml file for plugin

			$params		=	new cbParamsEditorController( $row->params, $element, $element, $row );

			if ( cbGetParam( $_GET, 'no_html', 0 ) != 1 ) {
				outputCbTemplate( 2 );
				outputCbJs( 2 );
				initToolTip( 2 );
			}

			/** @noinspection PhpIncludeInspection */
			require_once( $phpfile );

			$classname	=	$row->element . 'Admin';
			/** @noinspection SpellCheckingInspection */
			$adminClass	=	new $classname();

			/** @var stdClass|cbpaidsubscriptionsAdmin $adminClass */
			echo $adminClass->menu( $row, $menu, $params );
		} else {
			cbRedirect( $_CB_framework->backendViewUrl( 'showPlugins', false ), CBTxt::T( 'THE_PLUGIN_NAME_HAS_NO_ADMINISTRATOR_FILE_FILE', 'The plugin [name] has no administrator file [file]', array( '[name]' => $row->name, '[file]' => $phpfile . '-' .$uid  ) ), 'error' );
			return;
		}
	}
}
