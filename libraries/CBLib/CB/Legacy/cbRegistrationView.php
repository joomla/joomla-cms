<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 3:08 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbRegistrationView Class implementation
 * Registration Views Handler class
 */
class cbRegistrationView extends cbTemplateHandler
{
	/**
	 * User
	 * @var UserTable
	 */
	public $user;
	public $tabContent;
	public $regFormTag;
	public $introMessage;
	public $loginOrRegisterTitle;		// _LOGIN_REGISTER_TITLE
	public $registerTitle;				// _REGISTER_TITLE
	public $registerButton;				//	_UE_REGISTER
	public $moduleContent;
	public $topIcons;
	public $bottomIcons;
	public $conclusionMessage;
	public $formatting;
	public $triggerResults;

	/**
	 * Draws the registration page
	 *
	 * @param  UserTable   $user                  User object for registration
	 * @param  string      $tabContent            Content of registration tabs/fields
	 * @param  string      $regFormTag            Output at bottom of form
	 * @param  string      $introMessage          Introduction message
	 * @param  string      $loginOrRegisterTitle  Title for "login or register" to render
	 * @param  string      $registerTitle         Title for the registration part
	 * @param  string      $registerButton        Registration button rendering
	 * @param  string      $moduleContent         Login module content
	 * @param  string      $topIcons              Icons instructions at top
	 * @param  string      $bottomIcons           Icons instructions at bottom
	 * @param  string      $conclusionMessage     Concluding message
	 * @param  string      $formatting            Rendering method to call ('tabletrs' is default _render)
	 * @param  array|null  $triggerResults        Event Results to display between login form and Registration title
	 * @return string                             Rendered registration page
	 */
	function drawProfile( $user, $tabContent, $regFormTag, $introMessage, $loginOrRegisterTitle, $registerTitle,
						  $registerButton, $moduleContent, $topIcons, $bottomIcons, $conclusionMessage,
						  $formatting = 'tabletrs', $triggerResults = null )
	{
		$this->user						=	$user;
		$this->tabContent				=	$tabContent;
		$this->regFormTag				=	$regFormTag;
		$this->introMessage				=	$introMessage;
		$this->loginOrRegisterTitle		=	$loginOrRegisterTitle;
		$this->registerTitle			=	$registerTitle;
		$this->registerButton			=	$registerButton;
		$this->moduleContent			=	$moduleContent;
		$this->topIcons					=	$topIcons;
		$this->bottomIcons				=	$bottomIcons;
		$this->conclusionMessage		=	$conclusionMessage;
		$this->formatting				=	$formatting;
		$this->triggerResults			=	$triggerResults;

		return $this->draw( ( $formatting != 'tabletrs' ? ( $formatting == 'table' ? 'divs' : $formatting ) : '' ) );
	}
}
