<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 3:04 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbProfileView Class implementation
 * Profile Views Handler class
 */
class cbProfileView extends cbTemplateHandler
{
	/**
	 * User
	 * @var UserTable
	 */
	public $user;
	/**
	 * Array of rendered tabs
	 * @var array of string
	 */
	public $userViewTabs;
	public $tabContent;
	public $submitValue;
	public $cancelValue;
	public $bottomIcons;
	public $topIcons;

	/**
	 * Draws the profile
	 *
	 * @param  UserTable  $user
	 * @param  array      $userViewTabs   Array of rendered tabs
	 * @return string                     Rendered profile
	 */
	public function drawProfile( $user, $userViewTabs )
	{
		$this->user				=	$user;
		$this->userViewTabs		=	$userViewTabs;

		return $this->draw( 'Profile' );
	}

	/**
	 * Draws the profile edit
	 *
	 * @param  UserTable  $user
	 * @param  string     $tabContent
	 * @param  string     $submitValue
	 * @param  string     $cancelValue
	 * @param  string     $bottomIcons
	 * @param  string     $topIcons
	 * @return string                    Rendered profile
	 */
	public function drawEditProfile( $user, $tabContent, $submitValue, $cancelValue, $bottomIcons, $topIcons = null )
	{
		$this->user				=	$user;
		$this->tabContent		=	$tabContent;
		$this->submitValue		=	$submitValue;
		$this->cancelValue		=	$cancelValue;
		$this->bottomIcons		=	$bottomIcons;
		$this->topIcons			=	$topIcons;

		return $this->draw( 'Edit' );
	}
}
