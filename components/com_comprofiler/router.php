<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

function comprofilerRouterAlias( $title )
{
	return trim( preg_replace( '/_+/', '-', preg_replace( '/\W+/', '', str_replace( ' ', '_', str_replace( '_', '', trim( strtolower( $title ) ) ) ) ) ) );
}

function comprofilerBuildRoute( &$query )
{
	$segments										=	array();

	// We don't use view; lets see if it's present and task is missing:
	if ( isset( $query['view'] ) && ( ! isset( $query['task'] ) ) ) {
		$query['task']								=	$query['view'];

		unset( $query['view'] );
	}

	if ( isset( $query['task'] ) ) {
		$query['task']								=	strtolower( $query['task'] );

		$segments[]									=	$query['task'];

		switch ( $query['task'] ) {
			case 'userprofile':
				if ( isset( $query['user'] ) ) {
					if ( ( $query['user'] !== null ) && ( $query['user'] !== "" ) ) {
						if ( is_numeric( $query['user'] ) ) {
							$database				=	JFactory::getDBO();

							$sql					=	'SELECT' . $database->quoteName( 'username' )
													.	"\n FROM " . $database->quoteName( '#__users' )
													.	"\n WHERE " . $database->quoteName( 'id' ) . " = ". (int) $query['user'];
							$database->setQuery( $sql, 0, 1 );
							$username				=	$database->loadResult();

							// Ensure the username doesn't contain reservered : or , character and is not numeric:
							if ( $username && ( ! preg_match( '/[:,]/', $username ) ) && ( ! is_numeric( $username ) ) ) {
								// Joomla HTACCESS doesn't treat periods correctly so lets convert them to comma as chances of comma in username are slim:
								$query['user']		=	rawurlencode( str_replace( '.', ',', $username ) );
							}
						}

						$segments[]					=	$query['user'];
					}

					unset( $query['user'] );
				}
				break;
			case 'userslist':
				if ( isset( $query['listid'] ) ) {
					if ( ( $query['listid'] !== null ) && ( $query['listid'] !== "" ) ) {
						if ( is_numeric( $query['listid'] ) ) {
							$database				=	JFactory::getDBO();

							$sql					=	'SELECT' . $database->quoteName( 'title' )
													.	"\n FROM " . $database->quoteName( '#__comprofiler_lists' )
													.	"\n WHERE " . $database->quoteName( 'listid' ) . " = ". (int) $query['listid'];
							$database->setQuery( $sql, 0, 1 );
							$listTitle				=	$database->loadResult();

							if ( $listTitle ) {
								$query['listid']	=	(int) $query['listid'] . ':' . comprofilerRouterAlias( $listTitle );
							}
						}

						$segments[]					=	$query['listid'];
					}

					unset( $query['listid'] );
				}

				if ( isset( $query['searchmode'] ) ) {
					if ( $query['searchmode'] ) {
						if ( $query['searchmode'] == 1 ) {
							$segments[]				=	'search';
						} else {
							$segments[]				=	'searching';
						}
					}

					unset( $query['searchmode'] );
				}
				break;
		}

		unset( $query['task'] );
	}

	return $segments;
}

function comprofilerParseRoute( $segments )
{
	$vars										=	array();
	$count										=	count( $segments );

	if ( $count > 0 ) {
		$vars['view']							=	strtolower( $segments[0] );

		switch ( $vars['view'] ) {
			case 'userprofile':
				if ( $count > 1 ) {
					// Joomla converts all - to :, but that's ok as chances of : in username is slim
					// We also convert . to , as Joomla does not handle periods well in HTACCESS, but that's ok as chances of , in username is slim
					$user						=	str_replace( array( ':', ',' ), array( '-', '.' ), $segments[1] );

					if ( ! is_numeric( $user ) ) {
						$database				=	JFactory::getDBO();

						$sql					=	'SELECT' . $database->quoteName( 'id' )
												.	"\n FROM " . $database->quoteName( '#__users' )
												.	"\n WHERE " . $database->quoteName( 'username' ) . " = ". $database->Quote( rawurldecode( $user ) );
						$database->setQuery( $sql, 0, 1 );
						$user					=	(int) $database->loadResult();

						// New rewritting couldn't find a user so lets try the old method encase the URL is bookmarked:
						if ( ! $user ) {
							$user				=	str_replace( array( ':', '_' ), array( '-', '.' ), $segments[1] );

							$sql				=	'SELECT' . $database->quoteName( 'id' )
												.	"\n FROM " . $database->quoteName( '#__users' )
												.	"\n WHERE " . $database->quoteName( 'username' ) . " = ". $database->Quote( $user );
							$database->setQuery( $sql, 0, 1 );
							$user				=	(int) $database->loadResult();
						}
					}

					$vars['user']				=	(int) $user;
				}
				break;
			case 'userslist':
				if ( $count > 1 ) {
					$listId						=	$segments[1];

					if ( ! is_numeric( $listId ) ) {
						$listPair				=	explode( ':', $listId, 2 );

						if ( count( $listPair ) > 1 ) {
							$listId				=	(int) $listPair[0];
						} else {
							// New rewritting couldn't find a userlist so lets try the old method encase the URL is bookmarked:
							$database			=	JFactory::getDBO();

							$sql				=	'SELECT' . $database->quoteName( 'listid' )
												.	"\n FROM " . $database->quoteName( '#__comprofiler_lists' )
												.	"\n WHERE " . $database->quoteName( 'title' ) . " = ". $database->Quote( $listId );
							$database->setQuery( $sql, 0, 1 );
							$listId				=	(int) $database->loadResult();
						}
					}

					$vars['listid']				=	(int) $listId;

					if ( $count > 2 ) {
						if ( $segments[2] == 'search' ) {
							$vars['searchmode']	=	1;
						} elseif ( $segments[2] == 'searching' ) {
							$vars['searchmode']	=	2;
						}
					}
				}
				break;
		}
	}

	return $vars;
}
