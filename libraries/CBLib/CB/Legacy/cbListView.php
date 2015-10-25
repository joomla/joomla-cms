<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 3:14 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbListView Class implementation
 * Lists Views Handler class
 */
class cbListView extends cbTemplateHandler
{
	public $lists;
	public $listId;
	public $total;
	public $totalIsAllUsers;
	public $searchTabContent;
	public $searchResultDisplaying;
	public $ue_base_url;
	public $listTitleHtml;
	public $listDescription;
	public $searchCriteriaTitleHtml;
	public $searchResultsTitleHtml;
	public $allowListAll;
	public $allowListSelector;
	public $searchCollapsed;
	/**
	 * User
	 * @var UserTable[]
	 */
	public $users;
	/**
	 * Array of the columns for titles
	 * @var stdClass[]
	 */
	public $columns;
	/**
	 * Array of rendered cells fields
	 * @var array of array of array of array of string
	 */
	public $tableContent;
	/**
	 * If links to profiles from the list are allowed
	 * @var boolean
	 */
	public $allowProfileLink;
	public $layout;
	public $gridHeight;
	public $gridWidth;
	public $searchMode;

	/**
	 * Draws the list head
	 *
	 * @param  string[]  $lists
	 * @param  int       $listId
	 * @param  int       $total
	 * @param  boolean   $totalIsAllUsers
	 * @param  string    $searchTabContent
	 * @param  boolean   $searchResultDisplaying
	 * @param  string    $ue_base_url
	 * @param  string    $listTitleHtml
	 * @param  string    $listDescription
	 * @param  string    $searchCriteriaTitleHtml
	 * @param  string    $searchResultsTitleHtml
	 * @param  boolean   $allowListAll
	 * @param  boolean   $allowListSelector
	 * @param  boolean   $searchCollapsed
	 * @param  int       $searchMode
	 * @return string
	 */
	function drawListHead( $lists, $listId, $total, $totalIsAllUsers, $searchTabContent, $searchResultDisplaying,
						   $ue_base_url, $listTitleHtml, $listDescription, $searchCriteriaTitleHtml, $searchResultsTitleHtml,
						   $allowListAll = true, $allowListSelector = true, $searchCollapsed = false, $searchMode = 0 )
	{
		$this->lists					=	$lists;
		$this->listId					=	$listId;
		$this->total					=	$total;
		$this->totalIsAllUsers			=	$totalIsAllUsers;
		$this->searchTabContent			=	$searchTabContent;
		$this->searchResultDisplaying	=	$searchResultDisplaying;
		$this->ue_base_url				=	$ue_base_url;
		$this->listTitleHtml			=	$listTitleHtml;
		$this->listDescription			=	$listDescription;
		$this->searchCriteriaTitleHtml	=	$searchCriteriaTitleHtml;
		$this->searchResultsTitleHtml	=	$searchResultsTitleHtml;
		$this->allowListAll				=	$allowListAll;
		$this->allowListSelector		=	$allowListSelector;
		$this->searchCollapsed			=	$searchCollapsed;
		$this->searchMode				=	$searchMode;
		return $this->draw( 'Head' );
	}

	/**
	 * Draws the list body
	 *
	 * @param  UserTable[]  $users
	 * @param  array        $columns
	 * @param  array        $tableContent
	 * @param  int          $listId
	 * @param  boolean      $allowProfileLink
	 * @param  string       $layout
	 * @param  int          $gridHeight
	 * @param  int          $gridWidth
	 * @param  int          $searchMode
	 * @return string
	 */
	function drawListBody( $users, $columns, $tableContent, $listId, $allowProfileLink, $layout = 'grid', $gridHeight = 200, $gridWidth = 200, $searchMode = 0 )
	{
		$this->users					=	$users;
		$this->columns					=	$columns;
		$this->tableContent				=	$tableContent;
		$this->listId					=	$listId;
		$this->allowProfileLink			=	$allowProfileLink;
		$this->layout					=	$layout;
		$this->gridHeight				=	(int) $gridHeight;
		$this->gridWidth				=	(int) $gridWidth;
		$this->searchMode				=	$searchMode;

		return $this->draw( 'Body' );
	}
}
