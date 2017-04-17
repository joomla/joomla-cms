<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\FieldTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onAfterUserUpdate', 'syncUser', 'cbforumsModel' );
$_PLUGINS->registerFunction( 'onAfterUpdateUser', 'syncUser', 'cbforumsModel' );
$_PLUGINS->registerFunction( 'onAfterUserRegistration', 'syncUser', 'cbforumsModel' );
$_PLUGINS->registerFunction( 'onAfterNewUser', 'syncUser', 'cbforumsModel' );
$_PLUGINS->registerFunction( 'forumSideProfile', 'getSidebar', 'cbforumsModel' );

/**
 * Class cbforumsModel
 * CB Forums Model for Kunena
 */
class cbforumsModel extends cbPluginHandler
{
	/**
	 * @param  UserTable    $viewer  Viewing User
	 * @param  UserTable    $user    Viewed at User
	 * @param  TabTable     $tab     Current Tab
	 * @param  PluginTable  $plugin  Current Plugin
	 * @return string                HTML
	 */
	static public function getPosts( $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_CB_database;

		if ( ! class_exists( 'KunenaForumMessageHelper' ) ) {
			return CBTxt::T( 'Kunena not installed, enabled, or failed to load.' );
		}

		$exclude				=	$plugin->params->get( 'forum_exclude', null );

		if ( $exclude ) {
			$exclude			=	explode( '|*|', $exclude );

			cbArrayToInts( $exclude );

			$exclude			=	implode( ',', $exclude );
		}

		cbimport( 'cb.pagination' );
		cbforumsClass::getTemplate( 'tab_posts' );

		$limit					=	(int) $tab->params->get( 'tab_posts_limit', 15 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'tab_posts_limitstart{com_comprofiler}', 'tab_posts_limitstart' );
		$filterSearch			=	$_CB_framework->getUserStateFromRequest( 'tab_posts_search{com_comprofiler}', 'tab_posts_search' );
		$where					=	array();

		if ( isset( $filterSearch ) && ( $filterSearch != '' ) ) {
			$where[]			=	'( m.' . $_CB_database->NameQuote( 'subject' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
								.	' OR t.' . $_CB_database->NameQuote( 'message' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false ) . ' )';
		}

		$searching				=	( count( $where ) ? true : false );

		if ( $exclude ) {
			$where[]			=	'( m.' . $_CB_database->NameQuote( 'catid' ) . ' NOT IN ( ' . $exclude . ' ) )';
		}

		$params					=	array(	'user' => (int) $user->id,
											'starttime' => -1,
											'where' => ( count( $where ) ? implode( ' AND ', $where ) : null )
										);

		$posts					=	KunenaForumMessageHelper::getLatestMessages( false, 0, 0, $params );
		$total					=	array_shift( $posts );

		if ( $total <= $limitstart ) {
			$limitstart			=	0;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'tab_posts_' );

		if ( $tab->params->get( 'tab_posts_paging', 1 ) ) {
			$posts				=	KunenaForumMessageHelper::getLatestMessages( false, (int) $pageNav->limitstart, (int) $pageNav->limit, $params );
			$posts				=	array_pop( $posts );
		} else {
			$posts				=	array_pop( $posts );
		}

		$rows					=	array();

		/** @var KunenaForumMessage[] $posts */
		if ( $posts ) foreach ( $posts as $post ) {
			$row				=	new stdClass;
			$row->id			=	$post->id;
			$row->subject		=	$post->subject;
			$row->message		=	$post->message;
			$row->date			=	$post->time;
			$row->url			=	$post->getUrl();
			$row->category_id	=	$post->getCategory()->id;
			$row->category_name	=	$post->getCategory()->name;
			$row->category_url	=	$post->getCategory()->getUrl();

			$rows[]				=	$row;
		}

		$input					=	array();
		$input['search']		=	'<input type="text" name="tab_posts_search" value="' . htmlspecialchars( $filterSearch ) . '" onchange="document.forumPostsForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Posts...' ) ) . '" class="form-control" />';

		return HTML_cbforumsTabPosts::showPosts( $rows, $pageNav, $searching, $input, $viewer, $user, $tab, $plugin );
	}

	/**
	 * View Forum Favorites
	 *
	 * @param  UserTable    $viewer  Viewing User
	 * @param  UserTable    $user    Viewed at User
	 * @param  TabTable     $tab     Current Tab
	 * @param  PluginTable  $plugin  Current Plugin
	 * @return string                HTML
	 */
	static public function getFavorites( $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_CB_database;

		if ( ! class_exists( 'KunenaForumTopicHelper' ) ) {
			return CBTxt::T( 'Kunena not installed, enabled, or failed to load.' );
		}

		cbimport( 'cb.pagination' );
		cbforumsClass::getTemplate( 'tab_favs' );

		$limit					=	(int) $tab->params->get( 'tab_favs_limit', 15 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'tab_favs_limitstart{com_comprofiler}', 'tab_favs_limitstart' );
		$filterSearch			=	$_CB_framework->getUserStateFromRequest( 'tab_favs_search{com_comprofiler}', 'tab_favs_search' );
		$where					=	array();

		if ( isset( $filterSearch ) && ( $filterSearch != '' ) ) {
			$where[]			=	'( tt.' . $_CB_database->NameQuote( 'subject' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
								.	' OR tt.' . $_CB_database->NameQuote( 'first_post_message' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false ) . ' )';
		}

		$searching				=	( count( $where ) ? true : false );

		$params					=	array(	'user' => (int) $user->id,
											'favorited' => true,
											'starttime' => -1,
											'where' => ( count( $where ) ? ' AND ' . implode( ' AND ', $where ) : null )
										);

		$topics					=	KunenaForumTopicHelper::getLatestTopics( false, 0, 0, $params );
		$total					=	array_shift( $topics );

		if ( $total <= $limitstart ) {
			$limitstart			=	0;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'tab_favs_' );

		if ( $tab->params->get( 'tab_favs_paging', 1 ) ) {
			$topics				=	KunenaForumTopicHelper::getLatestTopics( false, (int) $pageNav->limitstart, (int) $pageNav->limit, $params );
			$topics				=	array_pop( $topics );
		} else {
			$topics				=	array_pop( $topics );
		}

		$rows					=	array();

		/** @var KunenaForumTopic[] $topics */
		if ( $topics ) foreach ( $topics as $topic ) {
			$row				=	new stdClass;
			$row->id			=	$topic->id;
			$row->subject		=	$topic->subject;
			$row->message		=	$topic->first_post_message;
			$row->date			=	$topic->first_post_time;
			$row->url			=	$topic->getUrl();
			$row->category_id	=	$topic->getCategory()->id;
			$row->category_name	=	$topic->getCategory()->name;
			$row->category_url	=	$topic->getCategory()->getUrl();

			$rows[]				=	$row;
		}

		$input					=	array();
		$input['search']		=	'<input type="text" name="tab_favs_search" value="' . htmlspecialchars( $filterSearch ) . '" onchange="document.forumFavsForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Favorites...' ) ) . '" class="form-control" />';

		return HTML_cbforumsTabFavs::showFavorites( $rows, $pageNav, $searching, $input, $viewer, $user, $tab, $plugin );
	}

	/**
	 * View Forum Subscriptions
	 *
	 * @param  UserTable    $viewer  Viewing User
	 * @param  UserTable    $user    Viewed at User
	 * @param  TabTable     $tab     Current Tab
	 * @param  PluginTable  $plugin  Current Plugin
	 * @return string                HTML
	 */
	static public function getSubscriptions( $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_CB_database;

		if ( ! class_exists( 'KunenaForumTopicHelper' ) ) {
			return CBTxt::T( 'Kunena not installed, enabled, or failed to load.' );
		}

		cbimport( 'cb.pagination' );
		cbforumsClass::getTemplate( 'tab_subs' );

		$limit					=	(int) $tab->params->get( 'tab_subs_limit', 15 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'tab_subs_limitstart{com_comprofiler}', 'tab_subs_limitstart' );
		$filterSearch			=	$_CB_framework->getUserStateFromRequest( 'tab_subs_search{com_comprofiler}', 'tab_subs_search' );
		$where					=	array();

		if ( isset( $filterSearch ) && ( $filterSearch != '' ) ) {
			$where[]			=	'( tt.' . $_CB_database->NameQuote( 'subject' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
								.	' OR tt.' . $_CB_database->NameQuote( 'first_post_message' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false ) . ' )';
		}

		$searching				=	( count( $where ) ? true : false );

		$params					=	array(	'user' => (int) $user->id,
											'subscribed' => true,
											'starttime' => -1,
											'where' => ( count( $where ) ? ' AND ' . implode( ' AND ', $where ) : null )
										);

		$topics					=	KunenaForumTopicHelper::getLatestTopics( false, 0, 0, $params );
		$total					=	array_shift( $topics );

		if ( $total <= $limitstart ) {
			$limitstart			=	0;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'tab_subs_' );

		if ( $tab->params->get( 'tab_subs_paging', 1 ) ) {
			$topics				=	KunenaForumTopicHelper::getLatestTopics( false, (int) $pageNav->limitstart, (int) $pageNav->limit, $params );
			$topics				=	array_pop( $topics );
		} else {
			$topics				=	array_pop( $topics );
		}

		$rows					=	array();

		/** @var KunenaForumTopic[] $topics */
		if ( $topics ) foreach ( $topics as $topic ) {
			$row				=	new stdClass;
			$row->id			=	$topic->id;
			$row->subject		=	$topic->subject;
			$row->message		=	$topic->first_post_message;
			$row->date			=	$topic->first_post_time;
			$row->url			=	$topic->getUrl();
			$row->category_id	=	$topic->getCategory()->id;
			$row->category_name	=	$topic->getCategory()->name;
			$row->category_url	=	$topic->getCategory()->getUrl();

			$rows[]				=	$row;
		}

		$input					=	array();
		$input['search']		=	'<input type="text" name="tab_subs_search" value="' . htmlspecialchars( $filterSearch ) . '" onchange="document.forumSubsForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Subscriptions...' ) ) . '" class="form-control" />';

		return HTML_cbforumsTabSubs::showSubscriptions( $rows, $pageNav, $searching, $input, $viewer, $user, $tab, $plugin );
	}

	/**
	 * View Forum Category Subscriptions
	 *
	 * @param  UserTable    $viewer  Viewing User
	 * @param  UserTable    $user    Viewed at User
	 * @param  TabTable     $tab     Current Tab
	 * @param  PluginTable  $plugin  Current Plugin
	 * @return string|boolean        HTML or FALSE
	 */
	static public function getCategorySubscriptions( $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_CB_database;

		if ( ! class_exists( 'KunenaForumCategoryHelper' ) ) {
			return CBTxt::T( 'Kunena not installed, enabled, or failed to load.' );
		}

		cbimport( 'cb.pagination' );
		cbforumsClass::getTemplate( 'tab_subs_cats' );

		$limit					=	(int) $tab->params->get( 'tab_subs_limit', 15 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'tab_subs_cats_limitstart{com_comprofiler}', 'tab_subs_cats_limitstart' );
		$filterSearch			=	$_CB_framework->getUserStateFromRequest( 'tab_subs_cats_search{com_comprofiler}', 'tab_subs_cats_search' );
		$where					=	array();

		if ( isset( $filterSearch ) && ( $filterSearch != '' ) ) {
			$where[]			=	'( c.' . $_CB_database->NameQuote( 'name' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false ) . ' )';
		}

		$searching				=	( count( $where ) ? true : false );

		$params					=	array( 'where' => ( count( $where ) ? ' AND ' . implode( ' AND ', $where ) : null ) );

		$categories				=	KunenaForumCategoryHelper::getLatestSubscriptions( (int) $user->id, 0, 0, $params );
		$total					=	array_shift( $categories );

		if ( $total <= $limitstart ) {
			$limitstart			=	0;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'tab_subs_cats_' );

		if ( $tab->params->get( 'tab_subs_paging', 1 ) ) {
			$categories			=	KunenaForumCategoryHelper::getLatestSubscriptions( (int) $user->id, (int) $pageNav->limitstart, (int) $pageNav->limit, $params );
			$categories			=	array_pop( $categories );
		} else {
			$categories			=	array_pop( $categories );
		}

		$rows					=	array();

		/** @var KunenaForumCategory[] $categories */
		if ( $categories ) foreach ( $categories as $category ) {
			$row				=	new stdClass;
			$row->id			=	$category->id;
			$row->category_id	=	$category->id;
			$row->category_name	=	$category->name;
			$row->category_url	=	$category->getUrl();

			$rows[]				=	$row;
		}

		$input					=	array();
		$input['search']		=	'<input type="text" name="tab_subs_cats_search" value="' . htmlspecialchars( $filterSearch ) . '" onchange="document.forumCatSubsForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Category Subscriptions...' ) ) . '" class="form-control" />';

		if ( ( ! $rows ) && ( ! $searching ) ) {
			return false;
		} else {
			return HTML_cbforumsTabCatSubs::showCategorySubscriptions( $rows, $pageNav, $searching, $input, $viewer, $user, $tab, $plugin );
		}
	}

	/**
	 * Un-favorite a post
	 *
	 * @param  string|int   $postid  Forum Post id
	 * @param  UserTable    $user    Viewed at User
	 * @param  PluginTable  $plugin  Current Plugin
	 * @return boolean               Result
	 */
	static public function unFavorite( $postid, $user, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		if ( ! class_exists( 'KunenaForumTopicHelper' ) ) {
			return false;
		}

		if ( $postid == 'all' ) {
			$ids	=	array_keys( array_pop( KunenaForumTopicHelper::getLatestTopics( false, 0, 0, array( 'user' => (int) $user->id, 'favorited' => true ) ) ) );
		} else {
			$ids	=	array( (int) $postid );
		}

		if ( ( ! $ids ) || ( ! KunenaForumTopicHelper::favorite( $ids, 0, (int) $user->id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Un-subscribe from a topic
	 *
	 * @param  string|int   $postid  Forum Post id
	 * @param  UserTable    $user    Viewed at User
	 * @param  PluginTable  $plugin  Current Plugin
	 * @return boolean               Result
	 */
	static public function unSubscribe( $postid, $user, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		if ( ! class_exists( 'KunenaForumTopicHelper' ) ) {
			return false;
		}

		if ( $postid == 'all' ) {
			$topics	=	KunenaForumTopicHelper::getLatestTopics( false, 0, 0, array( 'user' => (int) $user->id, 'subscribed' => true ) );
			$ids	=	array_keys( array_pop( $topics ) );
		} else {
			$ids	=	array( (int) $postid );
		}

		if ( ( ! $ids ) || ( ! KunenaForumTopicHelper::subscribe( $ids, 0, (int) $user->id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Un-subscribe from a category
	 *
	 * @param  string|int   $catid   Forum Category id
	 * @param  UserTable    $user    Viewed at User
	 * @param  PluginTable  $plugin  Current Plugin
	 * @return boolean               Result
	 */
	static public function unSubscribeCategory( $catid, $user, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		if ( ! class_exists( 'KunenaForumCategoryHelper' ) ) {
			return false;
		}

		if ( $catid == 'all' ) {
			$categories	=	KunenaForumCategoryHelper::getLatestSubscriptions( (int) $user->id, 0, 0 );
			$ids		=	array_keys( array_pop( $categories ) );
		} else {
			$ids		=	array( (int) $catid );
		}

		if ( ( ! $ids ) || ( ! KunenaForumCategoryHelper::subscribe( $ids, 0, (int) $user->id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get item id for forum
	 *
	 * @param  boolean      $htmlspecialchars
	 * @return string|null
	 */
	static public function getItemid( $htmlspecialchars = false )
	{
		if ( ! class_exists( 'KunenaRoute' ) ) {
			return null;
		}

		static $Itemid	=	null;

		if ( ! isset( $Itemid ) ) {
			$Itemid		=	KunenaRoute::getItemID();
		}

		if ( $Itemid ) {
			if ( is_bool( $htmlspecialchars ) ) {
				return ( $htmlspecialchars ? '&amp;' : '&' ) . 'Itemid=' . (int) $Itemid;
			} else {
				return $Itemid;
			}
		}

		return null;
	}

	/**
	 * Gets an URL to a post or a category
	 *
	 * @param  int|null  $forum  Forum category
	 * @param  int|null  $post   Forum post
	 * @return null|string       URL
	 */
	static public function getForumURL( $forum = null, $post = null )
	{
		if ( ( ! class_exists( 'KunenaForumTopic' ) ) || ( ! class_exists( 'KunenaForumMessage' ) ) ) {
			return null;
		}

		if ( $post ) {
			$url		=	KunenaForumTopic::getInstance( (int) $post )->getUrl();

			if ( ! $url ) {
				$url	=	KunenaForumMessage::getInstance( (int) $post )->getUrl();
			}
		} else {
			$url		=	cbforumsModel::getCategory( (int) $forum )->getUrl();
		}

		return $url;
	}

	/**
	 * @param  null|int  $user_id
	 * @return array
	 */
	static public function getAllowedCategories( $user_id )
	{
		global $_CB_framework;

		if ( ! class_exists( 'KunenaAccess' ) ) {
			return array();
		}

		if ( $user_id === null ) {
			$user_id			=	$_CB_framework->myId();
		}

		$cache					=	array();

		if ( ! isset( $cache[$user_id] ) ) {
			$cache[$user_id]	=	KunenaAccess::getInstance()->getAllowedCategories( (int) $user_id );
		}

		return $cache[$user_id];
	}

	/**
	 * @param  int  $catid
	 * @return KunenaForumCategory|null
	 */
	static public function getCategory( $catid )
	{
		if ( ! class_exists( 'KunenaForumCategoryHelper' ) ) {
			return null;
		}

		static $cache		=	array();

		if ( ! isset( $cache[$catid] ) ) {
			$cache[$catid]	=	KunenaForumCategoryHelper::get( (int) $catid );
		}

		return $cache[$catid];
	}

	/**
	 * @return array
	 */
	static public function getBoards( )
	{
		if ( ! class_exists( 'KunenaForumCategoryHelper' ) ) {
			return array();
		}

		$rows				=	KunenaForumCategoryHelper::getChildren( 0, 10 );
		$categories			=	array();

		if ( $rows ) foreach ( $rows as $row ) {
			$categories[]	=	moscomprofilerHTML::makeOption( $row->id, str_repeat( '- ', $row->level + 1  ) . ' ' . $row->name );
		}

		return $categories;
	}

	/**
	 * @param  UserTable  $user
	 * @return int
	 */
	static public function getUserPosts( $user )
	{
		if ( ! class_exists( 'KunenaUser' ) ) {
			return 0;
		}

		$value					=	0;

		if ( $user->get( 'id' ) ) {
			$forumUser			=	KunenaUser::getInstance( (int) $user->get( 'id' ) );

			if ( $forumUser ) {
				$value			=	(int) $forumUser->get( 'posts' );
			}
		}

		return $value;
	}

	/**
	 * @param  UserTable  $user
	 * @return int
	 */
	static public function getUserKarma( $user )
	{
		if ( ! class_exists( 'KunenaUser' ) ) {
			return 0;
		}

		$value					=	0;

		if ( $user->get( 'id' ) ) {
			$forumUser			=	KunenaUser::getInstance( (int) $user->get( 'id' ) );

			if ( $forumUser ) {
				$value			=	(int) $forumUser->get( 'karma' );
			}
		}

		return $value;
	}

	/**
	 * @param  UserTable  $user
	 * @param  bool       $showTitle
	 * @param  bool       $showImage
	 * @return string|null
	 */
	static public function getUserRank( $user, $showTitle = true, $showImage = true )
	{
		global $_CB_framework;

		if ( ! class_exists( 'KunenaUser' ) ) {
			return null;
		}

		$value					=	null;

		if ( $user->get( 'id' ) ) {
			$forumUser			=	KunenaUser::getInstance( (int) $user->get( 'id' ) );

			if ( $forumUser ) {
				$userRank		=	$forumUser->getRank();

				if ( ! $userRank ) {
					return null;
				}

				$title			=	$userRank->rank_title;

				if ( $showTitle ) {
					$value		.=	'<div>' . $title . '</div>';
				}

				if ( $showImage && class_exists( 'KunenaTemplate' ) ) {
					$template	=	KunenaTemplate::getInstance();
					$value		.=	'<div><img src="' . $_CB_framework->getCfg( 'live_site' ) . '/' . $template->getRankPath( $userRank->rank_image ) . '" alt="' . htmlspecialchars( $title ) . '" border="0" /></div>';
				}

				if ( ! $value ) {
					$value		=	$forumUser->rank;
				}
			}
		}

		return $value;
	}

	/**
	 * @param  UserTable  $user
	 * @return int
	 */
	static public function getUserThankYous( $user )
	{
		if ( ! class_exists( 'KunenaUser' ) ) {
			return 0;
		}

		$value					=	0;

		if ( $user->get( 'id' ) ) {
			$forumUser			=	KunenaUser::getInstance( (int) $user->get( 'id' ) );

			if ( $forumUser ) {
				$value			=	(int) $forumUser->get( 'thankyou' );
			}
		}

		return $value;
	}

	/**
	 * @param  UserTable  $user
	 */
	public function syncUser( $user )
	{
		global $_CB_framework;

		if ( ! class_exists( 'KunenaUser' ) ) {
			return;
		}

		$exists							=	KunenaUser::getInstance( (int) $user->id );

		if ( $exists ) {
			$plugin						=	cbforumsClass::getPlugin();
			$updated					=	false;
			$fields						=	array(	'ordering', 'viewtype', 'signature', 'personaltext',
													'gender', 'birthdate', 'location', 'icq',
													'aim', 'yim', 'msn', 'skype',
													'twitter', 'facebook', 'gtalk', 'myspace',
													'linkedin', 'delicious', 'friendfeed', 'digg',
													'blogspot', 'flickr', 'bebo', 'website',
													'email', 'online'
												);

			foreach ( $fields as $field ) {
				$cbField				=	$plugin->params->get( 'k20_' . $field, null );

				if ( $cbField && isset( $user->$cbField ) ) {
					$value				=	$user->get( $cbField );

					// Convert legacy values for B/C:
					switch ( $value ) {
						case '_UE_ORDERING_OLDEST':
						case 'Oldest':
							$value		=	0;
							break;
						case '_UE_ORDERING_LATEST':
						case 'Latest':
							$value		=	1;
							break;
						case '_UE_VIEWTYPE_FLAT':
						case 'Flat':
							$value		=	'flat';
							break;
						case '_UE_VIEWTYPE_THREADED':
						case 'Threaded':
							$value		=	'threaded';
							break;
						case '_UE_MALE':
						case 'Male':
							$value		=	1;
							break;
						case '_UE_FEMALE':
						case 'Female':
							$value		=	2;
							break;
						case '_UE_HIDE':
						case '_UE_NO':
						case 'Hide':
						case 'No':
							$value		=	0;
							break;
						case '_UE_SHOW':
						case '_UE_YES':
						case 'Show':
						case 'Yes':
							$value		=	1;
							break;
					}

					// Convert the field name and/or value to Kunena compatible:
					switch ( $field ) {
						case 'birthdate':
							if ( $value && ( ! in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00' ) ) ) ) {
								$value	=	$_CB_framework->getUTCDate( 'Y-m-d', $value );
							} else {
								$value	=	'0000-00-00';
							}
							break;
						case 'viewtype':
							$field		=	'view';
							break;
						case 'email':
							$field		=	'hideEmail';
							break;
						case 'online':
							$field		=	'showOnline';
							break;
						case 'personaltext':
							$field		=	'personalText';
							break;
					}

					// If the field is website then set both values in Kunena as needed; otherwise do normal set:
					if ( $field == 'website' ) {
						$web			=	explode( '|*|', $value );

						if ( count( $web ) > 1 ) {
							$webName	=	( isset( $web[0] ) ? $web[0] : null );
							$webUrl		=	( isset( $web[1] ) ? $web[1] : null );
						} else {
							$webName	=	null;
							$webUrl		=	( isset( $web[0] ) ? $web[0] : null );
						}

						if ( $webName != $exists->get( 'websitename' ) ) {
							$exists->set( 'websitename', $webName );

							$updated	=	true;
						}

						if ( $webUrl != $exists->get( 'websiteurl' ) ) {
							$exists->set( 'websiteurl', $webUrl );

							$updated	=	true;
						}
					} else {
						if ( $value != $exists->get( $field ) ) {
							$exists->set( $field, $value );

							$updated	=	true;
						}
					}
				}
			}

			if ( $updated ) {
				if ( ! $exists->save() ) {
					trigger_error( CBTxt::T( 'FORUMS_SYNC_USER_ERROR', '[element] - syncUser SQL Error: [error]', array( '[element]' => $plugin->element, '[error]' => $exists->getError() ) ), E_USER_WARNING );
				}
			}
		}
	}

	/**
	 * @param  string  $component
	 * @param  object  $view
	 * @param  int     $userId
	 * @param  array   $params
	 * @return string|null
	 */
	public function getSidebar( /** @noinspection PhpUnusedParameterInspection */ $component, $view, $userId, $params )
	{
		if ( isset( $params['userprofile'] ) ) {
			$cbUser			=	CBuser::getInstance( (int) $userId, false );
			$user			=	$cbUser->getUserData();
			$plugin			=	cbforumsClass::getPlugin();
			$userprofile	=	$params['userprofile'];

			if ( $user->id && $userprofile->userid ) {
				$display	=	$plugin->params->get( 'k20_sidebar_reg', null );
			} elseif ( ( ! $user->id ) && $userprofile->userid ) {
				$display	=	$plugin->params->get( 'k20_sidebar_del', null );
			} elseif ( ( ! $user->id ) && ( ! $userprofile->userid ) ) {
				$display	=	$plugin->params->get( 'k20_sidebar_anon', null );
			} else {
				$display	=	null;
			}

			if ( $display ) {
				$extras		=	array(	'karmaplus' => $view->userkarma_plus,
										'karmaminus' => $view->userkarma_minus
									);

				return $cbUser->replaceUserVars( $display, false, true, $extras );
			}
		}
		return null;
	}
}
