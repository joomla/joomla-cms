<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:35 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CBLib\Registry\Registry;
use CBLib\Registry\RegistryInterface;
use CB\Database\Table\FieldTable;
use CB\Database\Table\ListTable;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbUsersList Class implementation
 * Users list support class
 */
class cbUsersList
{
	/**
	 * Gets instance of cbUsersList for $listId
	 *
	 * @since 1.8
	 *
	 * @param  int          $listId  List id
	 * @return ListTable
	 */
	public static function getInstance( $listId )
	{
		global $_CB_framework;

		static $lists			=	array();

		$listId					=	(int) $listId;

		if ( ! isset( $lists[$listId] ) ) {
			$row				=	new ListTable();

			if ( ( ! $row->load( $listId ) ) || ( ( $_CB_framework->getUi() != 2 ) && ( $row->published != 1 ) ) ) {
				return null;
			}

			$lists[$listId]		=	$row;
		}

		return $lists[$listId];
	}

	/**
	 * Get the sort by query for $listId
	 *
	 * @param int $listId  The list id to parse
	 * @param int $userId  The user id to use for substitutions
	 * @param int $random  The current RAND seed
	 * @return string
	 */
	public static function getSorting( $listId, $userId = null, &$random )
	{
		global $_CB_database;

		$row								=	self::getInstance( $listId );

		if ( ! $row ) {
			return '';
		}

		if ( $userId ) {
			$cbUser							=	CBuser::getInstance( (int) $userId, false );
		} else {
			$cbUser							=	CBuser::getMyInstance();
		}

		if ( ! $random ) {
			$random							=	0;
		}

		$orderBy							=	'';
		$params								=	new Registry( $row->params );

		if ( $params->get( 'sort_mode', 0 ) ) {
			$sorting						=	$params->get( 'sort_advanced' );

			if ( $sorting ) {
				$orderBy					=	"\n ORDER BY " . $cbUser->replaceUserVars( $sorting, array( $_CB_database, 'getEscaped' ), false );
			}
		} else {
			$sorting						=	$params->get( 'sort_basic' );

			if ( $sorting ) {
				$sorts						=	array();

				foreach ( $sorting as $sort ) {
					$column					=	( isset( $sort['column'] ) ? $sort['column'] : null );
					$direction				=	( isset( $sort['direction'] ) ? $sort['direction'] : null );

					if ( $column && $direction ) {
						if ( $column == 'random' ) {
							if ( ! $random ) {
								$random		=	rand( 0, 32767 );
							}

							$sorts[]		=	'RAND(' . (int) $random . ') ' . $direction;
						} else {
							$sorts[]		=	$_CB_database->NameQuote( $column ) . ' ' . $direction;
						}
					}
				}

				if ( $sorts ) {
					$orderBy				=	"\n ORDER BY " . implode( ', ', $sorts );
				}
			}
		}

		return $orderBy;
	}

	/**
	 * Get the filtering query for $listId
	 *
	 * @param int $listId  The list id to parse
	 * @param int $userId  The user id to use for substitutions
	 * @return string
	 */
	public static function getFiltering( $listId, $userId = null )
	{
		global $_CB_database;

		$row								=	self::getInstance( $listId );

		if ( ! $row ) {
			return '';
		}

		if ( $userId ) {
			$cbUser							=	CBuser::getInstance( (int) $userId, false );
		} else {
			$cbUser							=	CBuser::getMyInstance();
		}

		$filterBy							=	'';
		$params								=	new Registry( $row->params );

		if ( $params->get( 'filter_mode', 0 ) ) {
			$filtering						=	$params->get( 'filter_advanced' );

			if ( $filtering ) {
				$filterBy					=	' AND ( ' . $cbUser->replaceUserVars( $filtering, array( $_CB_database, 'getEscaped' ), false ) . ' )';
			}
		} else {
			$filtering						=	$params->get( 'filter_basic' );

			if ( $filtering ) {
				$filters					=	array();

				foreach ( $filtering as $filter ) {
					$column					=	( isset( $filter['column'] ) ? $filter['column'] : null );
					$operator				=	( isset( $filter['operator'] ) ? $filter['operator'] : null );
					$value					=	( isset( $filter['value'] ) ? $filter['value'] : null );

					if ( $column && $operator ) {
						$column				=	$_CB_database->NameQuote( $column );
						$escapedValue		=	$cbUser->replaceUserVars( $value, array( $_CB_database, 'getEscaped' ), false );

						if ( in_array( $operator, array( 'IN', 'NOT IN||ISNULL' ) ) ) {
							$escapedValue	=	explode( ',', $escapedValue );
						} elseif ( in_array( $operator, array( 'LIKE', 'NOT LIKE||ISNULL' ) ) ) {
							$escapedValue	=	'%' . addcslashes( $escapedValue, '%_' ) . '%';
						}

						if ( is_array( $escapedValue ) ) {
							$escapedValue	=	$_CB_database->safeArrayOfStrings( $escapedValue );
						} else {
							$escapedValue	=	$_CB_database->Quote( $escapedValue );
						}

						if ( substr( $operator, -8 ) == '||ISNULL' ) {
							$operator		=	substr( $operator, 0, -8 );
							$isNull			=	true;
						} else {
							$isNull			=	false;
						}

						$basicFilter		=	$column . ' ' . $operator . ' ' . $escapedValue;

						if ( ( $operator === '<>' ) && ( $value === '' ) ) {
							// Users expect a filter of not equal to empty string to also not match null (no value):
							$basicFilter	=	'( ' . $basicFilter . ' AND ' . $column . ' IS NOT NULL )';
						} elseif ( $isNull || ( ( $operator === '=' ) && ( $value === '' ) ) ) {
							// Users expect a filter of equal to empty to also match null (no value) or in the case of ISNULL operator:
							$basicFilter	=	'( ' . $basicFilter . ' OR ' . $column . ' IS NULL )';
						}

						$filters[]			=	$basicFilter;
					}
				}

				if ( $filters ) {
					$filterBy				=	' AND ( ' . implode( ' AND ', $filters ) . ' )';
				}
			}
		}

		return $filterBy;
	}

	/**
	 * Get the field columns for $listId
	 *
	 * @param int $listId  The list id to parse
	 * @param int $userId  The user id to use for substitutions
	 * @return array
	 */
	public static function getColumns( $listId, $userId = null )
	{
		$row								=	self::getInstance( $listId );

		if ( ! $row ) {
			return '';
		}

		if ( $userId ) {
			$cbUser							=	CBuser::getInstance( (int) $userId, false );
		} else {
			$cbUser							=	CBuser::getMyInstance();
		}

		$columns							=	array();
		$params								=	new Registry( $row->params );
		$cols								=	$params->get( 'columns' );

		if ( $cols ) {
			foreach ( $cols as $i => $column ) {
				$colFields					=	array();

				if ( isset( $column['fields'] ) && $column['fields'] ) {
					foreach ( $column['fields'] as $colField ) {
						if ( isset( $colField['field'] ) && $colField['field'] ) {
							$colFields[]	=	array( 'fieldid' => $colField['field'], 'display' => ( isset( $colField['display'] ) ? (int) $colField['display'] : 4 ) );
						}
					}
				}

				$col						=	new stdClass();
				$col->fields				=	$colFields;
				$col->title					=	( isset( $column['title'] ) ? $column['title'] : null );
				$col->titleRendered			=	$cbUser->replaceUserVars( $col->title );
				$col->size					=	( isset( $column['size'] ) ? (int) $column['size'] : 3 );
				$col->cssclass				=	( isset( $column['cssclass'] ) ? $column['cssclass'] : null );

				$columns[$i]				=	$col;
			}
		}

		return $columns;
	}

	/**
	 * Draws Users list (ECHO)
	 *
	 * @param  int      $userId
	 * @param  int      $listId
	 * @param  array    $postData
	 * @return void
	 */
	public function drawUsersList( $userId, $listId, $postData )
	{
		global $_CB_database, $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user' );

		$searchData					=	cbGetParam( $postData, 'search' );
		$limitstart					=	(int) cbGetParam( $postData, 'limitstart' );
		$searchMode					=	(int) cbGetParam( $postData, 'searchmode', 0 );
		$random						=	(int) cbGetParam( $postData, 'rand', 0 );

		$cbUser						=	CBuser::getInstance( (int) $userId, false );
		$user						=	$cbUser->getUserData();

		$search						=	null;
		$input						=	array();
		$publishedLists				=	array();

		$query						=	'SELECT *'
									.	"\n FROM " .  $_CB_database->NameQuote( '#__comprofiler_lists' )
									.	"\n WHERE " .  $_CB_database->NameQuote( 'published' ) . " = 1"
									.	"\n AND " .  $_CB_database->NameQuote( 'viewaccesslevel' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
									.	"\n ORDER BY " .  $_CB_database->NameQuote( 'ordering' );
		$_CB_database->setQuery( $query );
		/** @var ListTable[] $userLists */
		$userLists					=	$_CB_database->loadObjectList( null, '\CB\Database\Table\ListTable', array( $_CB_database ) );

		if ( $userLists ) {
			foreach ( $userLists as $userList ) {
				$publishedLists[]	=	moscomprofilerHTML::makeOption( (int) $userList->listid, strip_tags( $cbUser->replaceUserVars( $userList->title, false, false ) ) );

				if ( ( ! $listId ) && $userList->default ) {
					$listId			=	(int) $userList->listid;
				}
			}

			if ( ! $listId ) {
				$listId				=	(int) $userLists[0]->listid;
			}
		}

		if ( ! $listId ) {
			echo CBTxt::Th( 'UE_NOLISTFOUND', 'There are no published user lists!' );
			return;
		}

		if ( $userLists ) {
			$input['plists']		=	moscomprofilerHTML::selectList( $publishedLists, 'listid', 'class="form-control input-block" onchange="this.form.submit();"', 'value', 'text', (int) $listId, 1 );
		}

		$row						=	self::getInstance( (int) $listId );

		if ( ! $row ) {
			echo CBTxt::Th( 'UE_LIST_DOES_NOT_EXIST', 'This list does not exist' );
			return;
		}

		if ( ! $cbUser->authoriseView( 'userslist', $row->listid ) ) {
			echo CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
			return;
		}

		$params						=	new Registry( $row->params );

		if ( $params->get( 'hotlink_protection', 0 ) == 1 ) {
			if ( ( $searchData !== null ) || $limitstart ) {
				cbSpoofCheck( 'userslist', 'GET' );
			}
		}

		$limit						=	(int) $params->get( 'list_limit', 30 );

		if ( ! $limit ) {
			$limit					=	30;
		}

		if ( $params->get( 'list_paging', 1 ) != 1 ) {
			$limitstart				=	0;
		}

		$isModerator				=	Application::MyUser()->isGlobalModerator();

		$_PLUGINS->trigger( 'onStartUsersList', array( &$listId, &$row, &$search, &$limitstart, &$limit ) );

		// Prepare query variables:
		$userGroupIds				=	explode( '|*|', $row->usergroupids );
		$orderBy					=	self::getSorting( $listId, $userId, $random );
		$filterBy					=	self::getFiltering( $listId, $userId );
		$columns					=	self::getColumns( $listId, $userId );

		// Grab all the fields the $user can access:
		$tabs						=	new cbTabs( 0, 1 );
		$fields						=	$tabs->_getTabFieldsDb( null, $user, 'list' );

		// Build the field SQL:
		$tableReferences			=	array( '#__comprofiler' => 'ue', '#__users' => 'u' );
		$searchableFields			=	array();
		$fieldsSQL					=	cbUsersList::getFieldsSQL( $columns, $fields, $tableReferences, $searchableFields, $params );

		$_PLUGINS->trigger( 'onAfterUsersListFieldsSql', array( &$columns, &$fields, &$tableReferences ) );

		// Build the internal joins and where statements best off list parameters:
		$tablesSQL								=	array();
		$joinsSQL								=	array();
		$tablesWhereSQL							=	array();

		if ( $isModerator ) {
			if ( ! $params->get( 'list_show_blocked', 0 ) ) {
				$tablesWhereSQL['block']		=	'u.block = 0';
			}

			if ( ! $params->get( 'list_show_banned', 1 ) ) {
				$tablesWhereSQL['banned']		=	'ue.banned = 0';
			}

			if ( ! $params->get( 'list_show_unapproved', 0 ) ) {
				$tablesWhereSQL['approved']		=	'ue.approved = 1';
			}

			if ( ! $params->get( 'list_show_unconfirmed', 0 ) ) {
				$tablesWhereSQL['confirmed']	=	'ue.confirmed = 1';
			}
		} else {
			$tablesWhereSQL						=	array(	'block' => 'u.block = 0',
															'approved' => 'ue.approved = 1',
															'confirmed' => 'ue.confirmed = 1',
															'banned' => 'ue.banned = 0'
														);
		}

		$joinsSQL[]								=	'JOIN #__user_usergroup_map g ON g.`user_id` = u.`id`';

		if ( $userGroupIds ) {
			$tablesWhereSQL['gid']				=	'g.group_id IN ' . $_CB_database->safeArrayOfIntegers( $userGroupIds );
		}

		foreach ( $tableReferences as $table => $name ) {
			if ( $name == 'u' ) {
				$tablesSQL[]					=	$table . ' ' . $name;
			} else {
				$joinsSQL[]						=	'JOIN ' . $table . ' ' . $name . ' ON ' . $name . '.`id` = u.`id`';
			}
		}

		// Build the search criteria:
		$searchValues				=	new stdClass();
		$searchesFromFields			=	$tabs->applySearchableContents( $searchableFields, $searchValues, $postData, $params->get( 'list_compare_types', 0 ) );
		$whereFields				=	$searchesFromFields->reduceSqlFormula( $tableReferences, $joinsSQL, true );

		if ( $whereFields ) {
			$tablesWhereSQL[]		=	'(' . $whereFields . ')';
		}

		$_PLUGINS->trigger( 'onBeforeUsersListBuildQuery', array( &$tablesSQL, &$joinsSQL, &$tablesWhereSQL ) );

		// Construct the FROM and WHERE for the userlist query:
		$queryFrom					=	"FROM " . implode( ', ', $tablesSQL )
									.	( count( $joinsSQL ) ? "\n " . implode( "\n ", $joinsSQL ) : '' )
									.	"\n WHERE " . implode( "\n AND ", $tablesWhereSQL )
									.	" " . $filterBy;

		$_PLUGINS->trigger( 'onBeforeUsersListQuery', array( &$queryFrom, 1, $listId ) ); // $ui = 1 (frontend)

		$errorMsg					=	null;

		// Checks if the list is being actively searched and it allows searching; otherwise reset back to normal:
		$searchCount				=	count( get_object_vars( $searchValues ) );

		if ( ( $params->get( 'list_search', 1 ) > 0 ) && $params->get( 'list_search_empty', 0 ) && ( ! $searchCount ) ) {
			$searchMode				=	1;
			$listAll				=	false;
		} else {
			$listAll				=	( $searchCount ? true : false );
		}

		if ( ( $searchMode == 0 ) || ( ( $searchMode == 1 ) && $searchCount ) || ( $searchMode == 2 ) ) {
			// Prepare the userlist count query for pagination:
			$_CB_database->setQuery( "SELECT COUNT( DISTINCT u.id ) " . $queryFrom );

			$total					=	$_CB_database->loadResult();

			if ( ( $limit > $total ) || ( $limitstart >= $total ) ) {
				$limitstart			=	0;
			}

			// Prepare the actual userlist query to build a list of users:
			$query					=	"SELECT DISTINCT ue.*, u.*, '' AS 'NA' " . ( $fieldsSQL ? ", " . $fieldsSQL . " " : '' ) . $queryFrom . " " . $orderBy;

			$_CB_database->setQuery( $query, (int) $limitstart, (int) $limit );
			/** @var UserTable[] $users */
			$users					=	$_CB_database->loadObjectList( null, '\CB\Database\Table\UserTable', array( $_CB_database ) );

			if ( ! $_CB_database->getErrorNum() ) {
				$profileLink		=	$params->get( 'allow_profilelink', 1 );

				// If users exist lets cache them and disable profile linking if necessary:
				if ( $users ) {
					foreach ( array_keys( $users ) as $k ) {
						// Add this user to cache:
						CBuser::setUserGetCBUserInstance( $users[$k] );

						if ( ! $profileLink ) {
							$users[$k]->set( '_allowProfileLink', 0 );
						}
					}
				}
			} else {
				$errorMsg			=	CBTxt::T( 'UE_ERROR_IN_QUERY_TURN_SITE_DEBUG_ON_TO_VIEW', 'There is an error in the database query. Site admin can turn site debug to on to view and fix the query.' );
			}

			if ( $searchCount ) {
				$search				=	'';
			} else {
				$search				=	null;
			}

			if ( ( $search === null ) && ( ( ( $searchMode == 1 ) && $searchCount ) || ( $searchMode == 2 ) ) ) {
				$search				=	'';
			}
		} else {
			$total					=	0;
			$users					=	array();

			if ( $search === null ) {
				$search				=	'';
			}
		}

		$pageNav					=	new cbPageNav( $total, $limitstart, $limit );

		HTML_comprofiler::usersList( $row, $users, $columns, $fields, $input, $search, $searchMode, $pageNav, $user, $searchableFields, $searchValues, $tabs, $errorMsg, $listAll, $random );
	}

	/**
	 * Creates the column references for the userlist query
	 * @static
	 *
	 * @param  array              $columns
	 * @param  FieldTable[]       $fields
	 * @param  array              $tables
	 * @param  array              $searchableFields
	 * @param  RegistryInterface  $params
	 * @return string
	 */
	public static function getFieldsSQL( &$columns, &$fields, &$tables, &$searchableFields, &$params )
	{
		$colRefs										=	array();
		$newTableIndex									=	0;
		$listSearch										=	(int) $params->get( 'list_search', 1 );

		foreach ( $columns as $i => $column ) {
			foreach ( $column->fields as $k => $colField ) {
				$fieldId								=	( isset( $colField['fieldid'] ) ? $colField['fieldid'] : null );

				if ( $fieldId && isset( $fields[$fieldId] ) ) {
					$field								=	$fields[$fieldId];

					if ( ! array_key_exists( $field->table, $tables ) ) {
						$newTableIndex++;
						$tables[$field->table]			=  't'.$newTableIndex;
					}

					if ( ( $tables[$field->table][0] != 'u' ) && ( $field->name != 'NA' ) ) {		// CB 1.1 table compatibility : TBD: remove after CB 1.2
						foreach ( $field->getTableColumns() as $col ) {
							$colRefs[$col]				=	$tables[$field->table] . '.' . $field->getDbo()->NameQuote( $col );
						}
					}

					if ( $field->searchable && ( $listSearch == 1 ) ) {
						$searchableFields[]				=	$fields[$fieldId];
					}

					$fields[$fieldId]->_listed		=	true;
				} else {
					// field unpublished or deleted but still in list: remove field from columns, so that we don't handle it:
					unset( $columns[$i]->fields[$k] );
				}
			}
		}

		if ( $listSearch == 2 ) {
			foreach ( $fields as $fieldId => $field ) {
				if ( $field->searchable ) {
					$searchableFields[]					=	$fields[$fieldId];
				}
			}
		}

		if ( $listSearch == 3 ) {
			$listSearchFields							=	explode( '|*|', $params->get( 'list_search_fields', null ) );

			if ( $listSearchFields ) foreach ( $fields as $fieldId => $field ) {
				if ( $field->searchable && in_array( $field->fieldid, $listSearchFields ) ) {
					$searchableFields[]					=	$fields[$fieldId];
				}
			}
		}

		return implode( ', ', $colRefs );
	}

	/**
	 * Outputs javascript for the advanced search feature on users lists
	 * To be called from renderer
	 *
	 * @param $search   null: show just search button, 'onlyactive': show also activated searches
	 */
	public static function outputAdvancedSearchJs( $search )
	{
		global $_CB_framework;

		$js				=	null;

		// Searchable fields appearing in the users list:
		// Search box:
		//TBD: display if there is a search criteria:
		if ( ( $search === null ) || ( $search == 'onlyactive' ) ) {
			$js			.=	"$( '.cbUserListsSearchTrigger' ).show();";

			if ( $search === null ) {
				$js		.=	"$( '.cbUserListSearch' ).hide();";
			} else {
				$js		.=	"var searching = 0;"
						.	"$( '.cbSearchCriteria select,.cbSearchCriteria input,.cbSearchCriteria textarea' ).each( function() {"
						.		"if ( ( $( this ).val() == '' ) || ( $( this ).closest( '.cb_form_line' ).find( '.cbSearchKind select' ).val() == '' ) ) {"
						.			"$( this ).closest( '.cb_form_line' ).hide();"
						.		"} else {"
						.			"searching++;"
						.			"$( this ).closest( '.cb_form_line' ).show();"
						.		"}"
						.	"});"
						.	"if ( searching > 0 ) {"
						.		"$( '.cbUserListSearch' ).show();"
						.		"$( '.cbUserListsSearchTrigger' ).hide();"
						.	"} else {"
						.		"$( '.cbUserListSearch .cb_form_line' ).show();"
						.	"}";
			}
		} else {
			$js		.=	"$( '.cbUserListSearch' ).show();"
					.	"$( '.cbUserListsSearchTrigger' ).hide();";
		}

		$js			.=	"$( '.cbUserListsSearchTrigger' ).click( function() {"
					.		( $search != 'onlyactive' ? "$( '.cbUserListSearch .cb_form_line' ).show();" : null )
					.		"$( '.cbUserListsSearchTrigger' ).hide( 'medium' );"
					.		"$( '.cbUserListSearch' ).slideDown( 'slow' );"
					.	"});"
					.	"$( '.cbUserlistCancel' ).click( function() {"
					.		"$( '.cbUserListsSearchTrigger' ).show( 'medium' );"
					.		"$( '.cbUserListSearch' ).slideUp( 'slow' );"
					.	"});"
					.	"$( '.cbSearchKind select' ).change( function() {"
					.		"var value = $( this ).val();"
					.		"var criteria = $( this ).parent().next( '.cbSearchCriteria' );"
					.		"if ( value == '' ) {"
					.			"criteria.slideUp();"
					.		"} else {"
					.			"if ( ( value == 'is' ) || ( value == 'isnot' ) ) {"
					.				"criteria.filter( '.cbSearchCriteriaSinglechoice' ).find( 'input[type=\"checkbox\"]' ).each( function() {"
					.					"$( this ).parent( 'label' ).removeClass( 'checkbox-inline' ).addClass( 'radio-inline' );"
					.					"$( this ).prop( 'type', 'radio' ).prop( 'name', $( this ).prop( 'name' ).substr( 0, $( this ).prop( 'name' ).indexOf( '[]' ) ) );"
					.				"});"
					.				"criteria.find( '.cbSelectChanged' ).removeAttr( 'multiple' ).removeClass( 'cbSelectChanged' );"
					.			"} else {"
					.				"criteria.filter( '.cbSearchCriteriaSinglechoice' ).find( 'input[type=\"radio\"]' ).each( function() {"
					.					"$( this ).parent( 'label' ).removeClass( 'radio-inline' ).addClass( 'checkbox-inline' );"
					.					"$( this ).prop( 'type', 'checkbox' ).prop( 'name', $( this ).prop( 'name' ) + '[]' );"
					.				"});"
					.				"criteria.find( 'select:not([multiple])' ).attr( 'multiple', 'multiple' ).addClass( 'cbSelectChanged' );"
					.			"}"
					.			"criteria.slideDown();"
					.		"}"
					.	"}).change();";

		$_CB_framework->outputCbJQuery( $js );
	}
}
