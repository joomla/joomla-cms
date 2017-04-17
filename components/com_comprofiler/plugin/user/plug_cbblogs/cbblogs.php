<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Input\Get;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CBLib\Registry\RegistryInterface;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );
$_PLUGINS->registerFunction( 'onAfterDeleteUser', 'deleteBlogs', 'cbblogsPlugin' );

/**
 * Class cbblogsClass
 * Internal class for static functions
 */
class cbblogsClass
{
	/**
	 * Gets the model description for CB articles
	 *
	 * @param  int|null  $cms
	 * @param  boolean   $include
	 * @return stdClass
	 */
	static public function getModel( $cms = null, $include = true )
	{
		global $_CB_framework;

		static $cache				=	array();

		if ( $cms ) {
			$cms					=	(int) $cms;
		}

		if ( ! isset( $cache[$cms] ) ) {
			$plugin					=	cbblogsClass::getPlugin();

			if ( ! $cms ) {
				$cms				=	$plugin->params->get( 'blog_model', 2 );
			}

			$model					=	new stdClass();

			if ( in_array( $cms, array( 1, 5, 7 ) ) && checkJversion() >= 2 ) {
				$cms				=	( checkJversion( '3.0+' ) ? 7 : 5 );

				$model->file		=	$plugin->absPath . '/models/joomla25.php';
				$model->detected	=	( $cms == 7 ? CBTxt::T( 'Joomla 3.x' ) : CBTxt::T( 'Joomla 2.x' ) );
				$model->type		=	$cms;
			} elseif ( in_array( $cms, array( 1, 6 ) ) && is_dir( $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_k2' ) && class_exists( 'K2Model' ) ) {
				$model->file		=	$plugin->absPath . '/models/k2.php';
				$model->detected	=	CBTxt::T( 'K2' );
				$model->type		=	6;
			} else {
				$model->file		=	$plugin->absPath . '/models/internal.php';
				$model->detected	=	CBTxt::T( 'Internal' );
				$model->type		=	2;
			}

			if ( $include ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $model->file );

				$model->class		=	new cbblogsModel();
			}

			$cache[$cms]			=	$model;
		}

		return $cache[$cms];
	}

	/**
	 * Returns the currently loaded mode type and prepares data
	 * Used by Backend XML only
	 *
	 * @param  string             $value
	 * @param  RegistryInterface  $pluginParams
	 * @return int
	 */
	public function getModelType( $value, $pluginParams )
	{
		$categories					=	cbblogsClass::getModel()->class->getCategoriesList( true );

		if ( $categories ) {
			$pregCategories			=	array();

			foreach ( $categories as $category ) {
				$pregCategories[]	=	preg_quote( $category );
			}

			$pluginParams->set( 'catids', '^' . implode( '|', $pregCategories ) . '$' );
		} else {
			$pluginParams->set( 'catids', '^.+$' );
		}

		return cbblogsClass::getModel()->type;
	}

	/**
	 * Returns array of K2 list options
	 * Used by Backend XML only
	 *
	 * @return array
	 */
	public function getK2Options( )
	{
		global $_CB_framework;

		$options			=	array();

		if ( is_dir( $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_k2' ) && class_exists( 'K2Model' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once( $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_k2/models/categories.php' );

			/** @noinspection PhpUndefinedClassInspection */
			$categories		=	new K2ModelCategories();

			/** @noinspection PhpUndefinedMethodInspection */
			$options		=	array_merge( $options, $categories->categoriesTree( null, true, true ) );
		}

		return $options;
	}

	/**
	 * Gets the plugin
	 *
	 * @todo   Do not change system objects, nor extend attributes to it.
	 *
	 * @return PluginTable
	 */
	static public function getPlugin( )
	{
		global $_PLUGINS;

		static $plugin					=	null;

		if ( ! isset( $plugin ) ) {
			$plugin						=	$_PLUGINS->getLoadedPlugin( 'user', 'cbblogs' );

			if ( $plugin !== null ) {
				$plugin->relPath		=	$_PLUGINS->getPluginRelPath( $plugin );
				$plugin->livePath		=	$_PLUGINS->getPluginLivePath( $plugin );
				$plugin->absPath		=	$_PLUGINS->getPluginPath( $plugin );
				$plugin->xml			=	$_PLUGINS->getPluginXmlPath( $plugin );
				$plugin->params			=	$_PLUGINS->getPluginParams( $plugin );
			}
		}

		return $plugin;
	}

	/**
	 * Loads the relevant template
	 *
	 * @param  null|array  $files
	 * @param  boolean     $loadGlobal
	 * @param  boolean     $loadHeader
	 */
	static public function getTemplate( $files = null, $loadGlobal = true, $loadHeader = true )
	{
		global $_CB_framework;

		static $tmpl							=	array();

		if ( ! $files ) {
			$files								=	array();
		} elseif ( ! is_array( $files ) ) {
			$files								=	array( $files );
		}

		$id										=	md5( serialize( array( $files, $loadGlobal, $loadHeader ) ) );

		if ( ! isset( $tmpl[$id] ) ) {
			$plugin								=	cbblogsClass::getPlugin();
			$template							=	$plugin->params->get( 'general_template', 'default' );
			$paths								=	array( 'global_css' => null, 'php' => null, 'css' => null, 'js' => null, 'override_css' => null );

			foreach ( $files as $file ) {
				$file							=	preg_replace( '/[^-a-zA-Z0-9_]/', '', $file );
				$globalCss						=	'/templates/' . $template . '/template.css';
				$overrideCss					=	'/templates/' . $template . '/override.css';

				if ( $file ) {
					$php						=	$plugin->absPath . '/templates/' . $template . '/' . $file . '.php';
					$css						=	'/templates/' . $template . '/' . $file . '.css';
					$js							=	'/templates/' . $template . '/' . $file . '.js';
				} else {
					$php						=	null;
					$css						=	null;
					$js							=	null;
				}

				if ( $loadGlobal && $loadHeader ) {
					if ( ! file_exists( $plugin->absPath . $globalCss ) ) {
						$globalCss				=	'/templates/default/template.css';
					}

					if ( file_exists( $plugin->absPath . $globalCss ) ) {
						$_CB_framework->document->addHeadStyleSheet( $plugin->livePath . $globalCss );

						$paths['global_css']	=	$plugin->livePath . $globalCss;
					}
				}

				if ( $file ) {
					if ( ! file_exists( $php ) ) {
						$php					=	$plugin->absPath . '/templates/default/' . $file . '.php';
					}

					if ( file_exists( $php ) ) {
						/** @noinspection PhpIncludeInspection */
						require_once( $php );

						$paths['php']			=	$php;
					}

					if ( $loadHeader ) {
						if ( ! file_exists( $plugin->absPath . $css ) ) {
							$css				=	'/templates/default/' . $file . '.css';
						}

						if ( file_exists( $plugin->absPath . $css ) ) {
							$_CB_framework->document->addHeadStyleSheet( $plugin->livePath . $css );

							$paths['css']		=	$plugin->livePath . $css;
						}

						if ( ! file_exists( $plugin->absPath . $js ) ) {
							$js					=	'/templates/default/' . $file . '.js';
						}

						if ( file_exists( $plugin->absPath . $js ) ) {
							$_CB_framework->document->addHeadScriptUrl( $plugin->livePath . $js );

							$paths['js']		=	$plugin->livePath . $js;
						}
					}
				}

				if ( $loadGlobal && $loadHeader ) {
					if ( file_exists( $plugin->absPath . $overrideCss ) ) {
						$_CB_framework->document->addHeadStyleSheet( $plugin->livePath . $overrideCss );

						$paths['override_css']	=	$plugin->livePath . $overrideCss;
					}
				}
			}

			$tmpl[$id]							=	$paths;
		}
	}
}

// TODO: Check why this is outside of a class: Such code should never be running at file-level but always inside a class function:
cbblogsClass::getModel();

/**
 * Class cbblogsTab
 * Tab for CB Blogs
 */
class cbblogsTab extends cbTabHandler
{
	/**
	 * Labeller for title:
	 * Returns a profile view tab title
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getTabTitle( $tab, $user, $ui, $postdata )
	{
		$plugin		=	cbblogsClass::getPlugin();
		$viewer		=	CBuser::getMyUserDataInstance();
		$total		=	cbblogsModel::getBlogsTotal( null, $viewer, $user, $plugin );

		return parent::getTabTitle( $tab, $user, $ui, $postdata ) . ' <span class="badge badge-default">' . (int) $total . '</span>';
	}

	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  \CB\Database\Table\TabTable   $tab       the tab database entry
	 * @param  \CB\Database\Table\UserTable  $user      the user being displayed
	 * @param  int                           $ui        1 for front-end, 2 for back-end
	 * @return string|boolean                           Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework, $_CB_database;

		outputCbJs( 1 );
		outputCbTemplate( 1 );
		cbimport( 'cb.pagination' );

		$plugin				=	cbblogsClass::getPlugin();
		$model				=	cbblogsClass::getModel();
		$viewer				=	CBuser::getMyUserDataInstance();

		cbblogsClass::getTemplate( 'tab' );

		$limit				=	(int) $this->params->get( 'tab_limit', 15 );
		$limitstart			=	$_CB_framework->getUserStateFromRequest( 'tab_blogs_limitstart{com_comprofiler}', 'tab_blogs_limitstart' );
		$filterSearch		=	$_CB_framework->getUserStateFromRequest( 'tab_blogs_search{com_comprofiler}', 'tab_blogs_search' );
		$where				=	null;

		if ( isset( $filterSearch ) && ( $filterSearch != '' ) ) {
			if ( $model->type != 2 ) {
				$where		.=	"\n AND ( a." . $_CB_database->NameQuote( 'title' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
							.	" OR a." . $_CB_database->NameQuote( 'introtext' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
							.	" OR a." . $_CB_database->NameQuote( 'fulltext' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false ) . " )";
			} else {
				$where		.=	"\n AND ( a." . $_CB_database->NameQuote( 'title' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
							.	" OR a." . $_CB_database->NameQuote( 'blog_intro' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
							.	" OR a." . $_CB_database->NameQuote( 'blog_full' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false ) . " )";
			}
		}

		$searching			=	( $where ? true : false );

		$total				=	cbblogsModel::getBlogsTotal( $where, $viewer, $user, $plugin );

		if ( $total <= $limitstart ) {
			$limitstart		=	0;
		}

		$pageNav			=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'tab_blogs_' );

		$rows				=	cbblogsModel::getBlogs( ( $this->params->get( 'tab_paging', 1 ) ? array( $pageNav->limitstart, $pageNav->limit ) : null ), $where, $viewer, $user, $plugin );

		$input				=	array();
		$input['search']	=	'<input type="text" name="tab_blogs_search" value="' . htmlspecialchars( $filterSearch ) . '" onchange="document.blogForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Blogs...' ) ) . '" class="form-control" />';

		$tab->params		=	$this->params;

		$class				=	$plugin->params->get( 'general_class', null );

		$return				=	'<div id="cbBlogs" class="cbBlogs' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
							.		'<div id="cbBlogsInner" class="cbBlogsInner">'
							.			HTML_cbblogsTab::showBlogTab( $rows, $pageNav, $searching, $input, $viewer, $user, $model, $tab, $plugin )
							.		'</div>'
							.	'</div>';

		return $return;
	}
}

class cbblogsPlugin extends cbPluginHandler
{
	/**
	 * @param  UserTable  $user
	 * @param  int        $status
	 */
	public function deleteBlogs( $user, /** @noinspection PhpUnusedParameterInspection */ $status )
	{
		$plugin			=	cbblogsClass::getPlugin();

		if ( $plugin->params->get( 'blog_delete', 1 ) ) {
			cbblogsClass::getModel();

			$blogs		=	cbblogsModel::getBlogs( null, null, $user, $user, $plugin );

			if ( $blogs ) foreach ( $blogs as $blog ) {
				$blog->delete();
			}
		}
	}
}
