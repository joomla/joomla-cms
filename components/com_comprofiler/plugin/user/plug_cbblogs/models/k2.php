<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Input\Get;
use CBLib\Registry\GetterInterface;
use CBLib\Application\Application;
use CBLib\Database\Table\OrderedTable;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CBLib\Database\Table\TableInterface;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

JTable::addIncludePath( JPATH_ROOT . '/administrator/components/com_k2/tables' );

class cbblogsBlogTable extends OrderedTable
{

	public function __construct( $db = null )
	{
		parent::__construct( $db, '#__k2_items', 'id' );
	}

	private function map()
	{
		$map	=	array( 'user' => 'created_by', 'category' => 'catid', 'blog_intro' => 'introtext', 'blog_full' => 'fulltext' );

		foreach ( $map as $to => $from ) {
			$this->set( $to, $this->get( $from ) );
		}
	}

	public function load( $id = null )
	{
		$key			=	$this->get( '_tbl_key' );

		if ( $id !== null ) {
			$this->set( $key, $id );
		}

		$id				=	$this->get( $key );

		if ( $id === null ) {
			return false;
		}

		$article		=	JTable::getInstance( 'K2Item', 'Table' );

		// Workaround to Joomla Table class forcing default access property
		$article->set( 'access', null );

		$article->load( (int) $id );

		foreach ( $article as $k => $v ) {
			$this->set( $k, $v );
		}

		$this->map();

		return true;
	}

	public function bind( $array, $ignore = '', $prefix = null )
	{
		global $_CB_framework;

		$bind				=	parent::bind( $array, $ignore, $prefix );

		if ( $bind ) {
			$plugin			=	cbblogsClass::getPlugin();
			$myId			=	$_CB_framework->myId();
			$isModerator	=	Application::MyUser()->isGlobalModerator();

			$this->set( 'created_by', (int) Get::get( $array, 'user', $this->get( 'created_by', $myId ) ), GetterInterface::INT );
			$this->set( 'title', Get::get( $array, 'title', $this->get( 'title' ) ), GetterInterface::STRING );
			$this->set( 'introtext', Get::get( $array, 'blog_intro', $this->get( 'introtext' ), GetterInterface::HTML ) );
			$this->set( 'fulltext', Get::get( $array, 'blog_full', $this->get( 'fulltext' ), GetterInterface::HTML ) );

			if ( $plugin->params->get( 'blog_category_config', 1 ) || $isModerator ) {
				$this->set( 'catid', (int) Get::get( $array, 'category', $this->get( 'catid', $plugin->params->get( 'blog_k2_category_default', null ) ), GetterInterface::INT ) );
			} else {
				$this->set( 'catid', (int) $this->get( 'catid', $plugin->params->get( 'blog_k2_category_default', null ) ) );
			}

			if ( ( ( ! $plugin->params->get( 'blog_approval', 0 ) ) && $plugin->params->get( 'blog_published_config', 1 ) ) || $isModerator ) {
				$this->set( 'published', (int) Get::get( $array, 'published', $this->get( 'published', $plugin->params->get( 'blog_published_default', 1 ) ), GetterInterface::INT ) );
			} else {
				$this->set( 'published', (int) $this->get( 'published', ( $plugin->params->get( 'blog_approval', 0 ) ? 0 : $plugin->params->get( 'blog_published_default', 1 ) ) ) );
			}

			if ( $plugin->params->get( 'blog_access_config', 1 ) || $isModerator ) {
				$this->set( 'access', (int) Get::get( $array, 'access', $this->get( 'access', $plugin->params->get( 'blog_access_default', 1 ) ), GetterInterface::INT ) );
			} else {
				$this->set( 'access', (int) $this->get( 'access', $plugin->params->get( 'blog_access_default', 1 ) ) );
			}

			$this->set( 'ordering', (int) $this->get( 'ordering', 1 ) );

			$this->map();
		}

		return $bind;
	}

	public function check()
	{
		if ( $this->get( 'title' ) == '' ) {
			$this->setError( CBTxt::T( 'Title not specified!' ) );

			return false;
		} elseif ( ! $this->get( 'created_by' ) ) {
			$this->setError( CBTxt::T( 'User not specified!' ) );

			return false;
		} elseif ( $this->get( 'created_by' ) && ( ! CBuser::getUserDataInstance( (int) $this->get( 'created_by' ) )->id ) ) {
			$this->setError( CBTxt::T( 'User specified does not exist!' ) );

			return false;
		} elseif ( $this->get( 'access' ) === '' ) {
			$this->setError( CBTxt::T( 'Access not specified!' ) );

			return false;
		} elseif ( ! $this->get( 'catid' ) ) {
			$this->setError( CBTxt::T( 'Category not specified!' ) );

			return false;
		} elseif ( ! in_array( $this->get( 'catid' ), cbblogsModel::getCategoriesList( true ) ) ) {
			$this->setError( CBTxt::T( 'Category not allowed!' ) );

			return false;
		}

		return true;
	}

	public function store( $updateNulls = false )
	{
		global $_CB_framework, $_PLUGINS;

		$plugin				=	cbblogsClass::getPlugin();
		$user				=	CBuser::getMyUserDataInstance();

		$id					=	$this->get( $this->get( '_tbl_key' ) );
		$article			=	JTable::getInstance( 'K2Item', 'Table' );

		if ( ! $article->load( (int) $id ) ) {
			return false;
		}

		if ( ! $article->bind( (array) $this ) ) {
			return false;
		}

		$new				=	( (int) $id ? false : true );
		$table				=	JTable::getInstance( 'K2Item', 'Table' );

		$article->set( 'alias', $this->getTitleAlias( $article->get( 'title' ) ) );

		$alias				=	$article->get( 'alias' );

		while ( $table->load( array( 'alias' => $alias, 'catid' => $article->get( 'catid' ) ) ) ) {
			$matches		=	null;

			if ( preg_match( '#-(\d+)$#', $alias, $matches ) ) {
				$alias		=	preg_replace( '#-(\d+)$#', '-' . ( $matches[1] + 1 ) . '', $alias );
			} else {
				$alias		.=	'-2';
			}
		}

		$article->set( 'alias', $alias );

		if ( $article->get( 'params' ) == '' ) {
			$article->set( 'params', '{"catItemTitle":"","catItemTitleLinked":"","catItemFeaturedNotice":"","catItemAuthor":"","catItemDateCreated":"","catItemRating":"","catItemImage":"","catItemIntroText":"","catItemExtraFields":"","catItemHits":"","catItemCategory":"","catItemTags":"","catItemAttachments":"","catItemAttachmentsCounter":"","catItemVideo":"","catItemVideoWidth":"","catItemVideoHeight":"","catItemAudioWidth":"","catItemAudioHeight":"","catItemVideoAutoPlay":"","catItemImageGallery":"","catItemDateModified":"","catItemReadMore":"","catItemCommentsAnchor":"","catItemK2Plugins":"","itemDateCreated":"","itemTitle":"","itemFeaturedNotice":"","itemAuthor":"","itemFontResizer":"","itemPrintButton":"","itemEmailButton":"","itemSocialButton":"","itemVideoAnchor":"","itemImageGalleryAnchor":"","itemCommentsAnchor":"","itemRating":"","itemImage":"","itemImgSize":"","itemImageMainCaption":"","itemImageMainCredits":"","itemIntroText":"","itemFullText":"","itemExtraFields":"","itemDateModified":"","itemHits":"","itemCategory":"","itemTags":"","itemAttachments":"","itemAttachmentsCounter":"","itemVideo":"","itemVideoWidth":"","itemVideoHeight":"","itemAudioWidth":"","itemAudioHeight":"","itemVideoAutoPlay":"","itemVideoCaption":"","itemVideoCredits":"","itemImageGallery":"","itemNavigation":"","itemComments":"","itemTwitterButton":"","itemFacebookButton":"","itemGooglePlusOneButton":"","itemAuthorBlock":"","itemAuthorImage":"","itemAuthorDescription":"","itemAuthorURL":"","itemAuthorEmail":"","itemAuthorLatest":"","itemAuthorLatestLimit":"","itemRelated":"","itemRelatedLimit":"","itemRelatedTitle":"","itemRelatedCategory":"","itemRelatedImageSize":"","itemRelatedIntrotext":"","itemRelatedFulltext":"","itemRelatedAuthor":"","itemRelatedMedia":"","itemRelatedImageGallery":"","itemK2Plugins":""}' );
		}

		if ( $article->get( 'metadata' ) == '' ) {
			$article->set( 'metadata', "robots=\nauthor=" );
		}

		if ( $article->get( 'language' ) == '' ) {
			$article->set( 'language', '*' );
		}

		if ( ! $new ) {
			$article->set( 'modified', $_CB_framework->getUTCDate() );
			$article->set( 'modified_by', (int) $user->get( 'id' ) );

			$_PLUGINS->trigger( 'cbblogs_onBeforeUpdateBlog', array( &$this, &$article, $user, $plugin ) );
		} else {
			$article->set( 'created', $_CB_framework->getUTCDate() );

			$_PLUGINS->trigger( 'cbblogs_onBeforeCreateBlog', array( &$this, &$article, $user, $plugin ) );
		}

		if ( ! $article->store( $updateNulls ) ) {
			return false;
		}

		$article->reorder( $this->_db->NameQuote( 'catid' ) . ' = ' . (int) $article->get( 'catid' ) );

		if ( ! $new ) {
			$_PLUGINS->trigger( 'cbblogs_onAfterUpdateBlog', array( $this, $article, $user, $plugin ) );
		} else {
			$_PLUGINS->trigger( 'cbblogs_onAfterCreateBlog', array( $this, $article, $user, $plugin ) );
		}

		return true;
	}

	/**
	 * Autogenerates an URL-compatible title-alias for a title
	 *
	 * @param  string  $title  Title
	 * @return string          URL-compatible Alias corresponding to title
	 */
	private static function getTitleAlias( $title )
	{
		$alias	=	str_replace( '-', ' ', $title );
		$alias	=	trim( cbIsoUtf_strtolower( $alias ) );
		$alias	=	preg_replace( '/(\s|[^A-Za-z0-9\-])+/', '-', $alias );
		$alias	=	trim( $alias, '-' );

		return $alias;
	}

	public function delete( $id = null )
	{
		global $_PLUGINS;

		$plugin		=	cbblogsClass::getPlugin();
		$user		=	CBuser::getMyUserDataInstance();

		$key		=	$this->get( '_tbl_key' );

		if ( $id !== null ) {
			$this->set( $key, $id );
		}

		$id			=	$this->get( $key );
		$article	=	JTable::getInstance( 'K2Item', 'Table' );

		if ( ! $article->load( (int) $id ) ) {
			return false;
		}

		$_PLUGINS->trigger( 'cbblogs_onBeforeDeleteBlog', array( &$this, &$article, $user, $plugin ) );

		if ( ! $article->delete( (int) $id ) ) {
			return false;
		}

		$_PLUGINS->trigger( 'cbblogs_onAfterDeleteBlog', array( $this, $article, $user, $plugin ) );

		$article->reorder( $this->_db->NameQuote( 'catid' ) . ' = ' . (int) $article->get( 'catid' ) );

		return true;
	}

	public function getCategory( )
	{
		static $cache	=	array();

		$id				=	(int) $this->get( 'catid' );

		if ( ! isset( $cache[$id] ) ) {
			$category	=	new cbblogsCategoryTable( $this->_db );

			$category->load( $id );

			$cache[$id]	=	$category;
		}

		return $cache[$id];
	}
}

class cbblogsCategoryTable extends OrderedTable
{

	public function __construct( $db = null )
	{
		parent::__construct( $db, '#__k2_categories', 'id' );
	}

	public function load( $id = null )
	{
		$key			=	$this->get( '_tbl_key' );

		if ( $id !== null ) {
			$this->set( $key, $id );
		}

		$id				=	$this->get( $key );

		if ( $id === null ) {
			return false;
		}

		$category		=	JTable::getInstance( 'K2Category', 'Table' );

		if ( $category->load( (int) $id ) ) {
			foreach ( $category as $k => $v ) {
				$this->set( $k, $v );
			}

			return true;
		}

		return false;
	}
}

class cbblogsModel
{
	/**
	 * @param  string       $where
	 * @param  UserTable    $viewer
	 * @param  UserTable    $user
	 * @param  PluginTable  $plugin
	 * @return int
	 */
	static public function getBlogsTotal( $where, $viewer, $user, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_database;

		$categories			=	cbblogsModel::getCategoriesList( true );

		$total				=	0;

		if ( $categories ) {
			$query		=	'SELECT COUNT(*)'
						.	"\n FROM " . $_CB_database->NameQuote( '#__k2_items' ) . " AS a"
						.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__k2_categories' ) . " AS b"
						.	' ON b.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'catid' )
						.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS c"
						.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'created_by' )
						.	"\n WHERE a." . $_CB_database->NameQuote( 'catid' ) . " IN ( " . implode( ',', $categories ) . " )"
						.	"\n AND a." . $_CB_database->NameQuote( 'created_by' ) . " = " . (int) $user->get( 'id' )
						.	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ? "\n AND a." . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
						.	( ! Application::Cms()->getClientId() ? "\n AND a." . $_CB_database->NameQuote( 'trash' ) . " = 0" : null )
						.	"\n AND a." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
						.	$where;

			$_CB_database->setQuery( $query );

			$total		=	$_CB_database->loadResult();
		}

		return $total;
	}

	/**
	 * @param  int[]             $paging
	 * @param  string            $where
	 * @param  UserTable         $viewer
	 * @param  UserTable         $user
	 * @param  PluginTable       $plugin
	 * @return cbblogsBlogTable[]
	 */
	static public function getBlogs( $paging, $where, $viewer, $user, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_database;

		$categories		=	cbblogsModel::getCategoriesList( true );

		$blogs			=	array();

		if ( $categories ) {
			$query		=	'SELECT a.*'
						.	', a.' . $_CB_database->NameQuote( 'created_by' ) . ' AS user'
						.	', a.' . $_CB_database->NameQuote( 'introtext' ) . ' AS blog_intro'
						.	', a.' . $_CB_database->NameQuote( 'fulltext' ) . ' AS blog_full'
						.	', b.' . $_CB_database->NameQuote( 'name' ) . ' AS category'
						.	', b.' . $_CB_database->NameQuote( 'published' ) . ' AS category_published'
						.	', b.' . $_CB_database->NameQuote( 'alias' ) . ' AS category_alias'
						.	"\n FROM " . $_CB_database->NameQuote( '#__k2_items' ) . " AS a"
						.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__k2_categories' ) . " AS b"
						.	' ON b.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'catid' )
						.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS c"
						.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'created_by' )
						.	"\n WHERE a." . $_CB_database->NameQuote( 'catid' ) . " IN ( " . implode( ',', $categories ) . " )"
						.	"\n AND a." . $_CB_database->NameQuote( 'created_by' ) . " = " . (int) $user->get( 'id' )
						.	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ? "\n AND a." . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
						.	( ! Application::Cms()->getClientId() ? "\n AND a." . $_CB_database->NameQuote( 'trash' ) . " = 0" : null )
						.	"\n AND a." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
						.	$where
						.	"\n ORDER BY a." . $_CB_database->NameQuote( 'created' ) . " DESC";

			if ( $paging ) {
				$_CB_database->setQuery( $query, $paging[0], $paging[1] );
			} else {
				$_CB_database->setQuery( $query );
			}

			$blogs		=	$_CB_database->loadObjectList( null, 'cbblogsBlogTable', array( $_CB_database ) );
		}

		return $blogs;
	}

	/**
	 * @param bool $raw
	 * @return array
	 */
	static public function getCategoriesList( $raw = false )
	{
		global$_CB_framework;

		static $cache					=	null;

		if ( ! isset( $cache ) ) {
			$plugin						=	cbblogsClass::getPlugin();
			$sectionid					=	$plugin->params->get( 'blog_k2_section', null );

			/** @noinspection PhpIncludeInspection */
			require_once( $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_k2/models/categories.php' );

			/** @noinspection PhpUndefinedClassInspection */
			$categories					=	new K2ModelCategories();

			/** @noinspection PhpUndefinedMethodInspection */
			$cache						=	$categories->categoriesTree( null, true, true);
			$remove						=	array();

			if ( $sectionid ) {
				$section				=	JTable::getInstance( 'K2Category', 'Table' );

				$section->load( (int) $sectionid );

				/** @noinspection PhpUndefinedMethodInspection */
				$children				=	$categories->categoriesTree( $section, true, true );

				if ( $children ) foreach ( $children as $child ) {
					$remove[]			=	$child->value;
				}

				$remove[]				=	$sectionid;
			}

			foreach ( $cache as $k => $row ) {
				if ( in_array( $row->value, $remove ) ) {
					unset( $cache[$k] );
				} else {
					$cache[$k]->text	=	preg_replace( '/(?:- ){0,3}/', '', $cache[$k]->text, 1 );
				}
			}

			$cache						=	array_values( $cache );
		}

		$rows							=	$cache;

		if ( $rows ) {
			if ( $raw === true ) {
				$categories				=	array();

				foreach ( $rows as $row ) {
					$categories[]		=	(int) $row->value;
				}

				$rows					=	$categories;
			}
		} else {
			$rows						=	array();
		}

		return $rows;
	}

	/**
	 * @param  int|TableInterface  $row
	 * @param  bool                $htmlspecialchars
	 * @param  string              $type
	 * @return string
	 */
	static public function getUrl( $row, $htmlspecialchars = true, $type = 'article' )
	{
		global $_CB_framework;

		$plugin				=	cbblogsClass::getPlugin();

		if ( is_integer( $row ) ) {
			$rowId			=	$row;

			$row			=	new cbblogsBlogTable();

			$row->load( (int) $rowId );
		}

		$category			=	$row->getCategory();

		/** @noinspection PhpIncludeInspection */
		require_once ( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_k2/helpers/route.php' );

		$categorySlug		=	$row->get( 'catid' ) . ( $category->get( 'alias' ) ? ':' . $category->get( 'alias' ) : null );
		$articleSlug		=	$row->get( 'id' ) . ( $row->get( 'alias' ) ? ':' . $row->get( 'alias' ) : null );

		switch ( $type ) {
			case 'section':
				/** @noinspection PhpUndefinedClassInspection */
				$url		=	K2HelperRoute::getCategoryRoute( $plugin->params->get( 'blog_section', null ) );
				break;
			case 'category':
				/** @noinspection PhpUndefinedClassInspection */
				$url		=	K2HelperRoute::getCategoryRoute( $categorySlug );
				break;
			case 'article':
			default:
			/** @noinspection PhpUndefinedClassInspection */
				$url		=	K2HelperRoute::getItemRoute( $articleSlug, $categorySlug );
				break;
		}

		if ( ! stristr( $url, 'Itemid' ) ) {
			$url			=	$_CB_framework->getCfg( 'live_site' ) . '/' . $url;
		} else {
			$url			=	JRoute::_( $url, false );
		}

		if ( $url ) {
			if ( $htmlspecialchars ) {
				$url		=	htmlspecialchars( $url );
			}
		}

		return $url;
	}
}
