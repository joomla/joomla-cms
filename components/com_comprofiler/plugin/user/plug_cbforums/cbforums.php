<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\FieldTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );
$_PLUGINS->registerUserFieldTypes( array( 'forumstats' => 'cbforumsField' ) );
$_PLUGINS->registerUserFieldParams();

/**
 * Class cbforumsClass
 * Internal class for static functions
 */
abstract class cbforumsClass
{
	/**
	 * Gets the model description for CB Forums
	 *
	 * @param  int|null  $forum
	 * @param  boolean   $include
	 * @return stdClass
	 */
	static public function getModel( $forum = null, $include = true )
	{
		global $_CB_framework;

		static $cache					=	array();

		if ( $forum ) {
			$forum						=	(int) $forum;
		}

		if ( ! isset( $cache[$forum] ) ) {
			$plugin						=	cbforumsClass::getPlugin();
			$path						=	$_CB_framework->getCfg( 'absolute_path' );

			if ( ! $forum ) {
				$forum					=	$plugin->params->get( 'forum_model', 1 );
			}

			$model						=	new stdClass();

			if ( in_array( $forum, array( 1, 3, 4, 5, 6 ) ) && file_exists( $path . '/administrator/components/com_kunena/api.php' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $path . '/administrator/components/com_kunena/api.php' );

				if ( class_exists( 'KunenaForum' ) ) {
					KunenaForum::setup();
				}

				$model->file			=	$plugin->absPath . '/models/kunena20.php';
				$model->detected		=	( $forum == 6 ? CBTxt::T( 'Kunena 3.x' ) : CBTxt::T( 'Kunena 2.x' ) );
				$model->type			=	( $forum == 6 ? 6 : 5 );
			} else {
				$model->file			=	null;
				$model->detected		=	CBTxt::T( 'None' );
				$model->type			=	0;
			}

			if ( $include && $model->file ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $model->file );

				$model->class			=	new cbforumsModel();
			}

			$cache[$forum]				=	$model;
		}

		return $cache[$forum];
	}

	/**
	 * Gets the plugin
	 *
	 * @todo   Do not change system objects, nor extend attributes to it.
	 *
	 * @return  PluginTable
	 */
	static public function getPlugin( )
	{
		global $_PLUGINS;

		static $plugin					=	null;

		if ( ! isset( $plugin ) ) {
			$plugin						=	$_PLUGINS->getLoadedPlugin( 'user', 'cbforums' );

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
			$plugin								=	cbforumsClass::getPlugin();
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

	/**
	 * Cleans the post from BBCodes
	 *
	 * @param  string    $text    BBCode-text
	 * @param  int|null  $length  Maximum length of string
	 * @return string             Cleaned text, BBCode-free
	 */
	static public function cleanPost( $text, $length = null )
	{
		$text		=	preg_replace( '!:(pinch|whistle|evil|side|kiss|blush|silly|cheer|woohoo|rolleyes|money|lol|dry|huh|blink|ohmy|unsure|mad|angry|laugh):!', '', $text ); //Smilies
		$text		=	preg_replace( '!((\[b\])*\w+ wrote:(\[\/b\])*\s+)*(?s)(\[quote\])(.*?)(\[/quote\])!', '...', $text ); //Quotes
		$text		=	preg_replace( '!(?s)(\[code(.*?)\])(.*?)(\[/code(.*?)\])!', '...', $text ); //Code
		$text		=	preg_replace( '!(?s)(\[i\])(.*?)(\[\/i\])!', '\2', $text ); //Italic
		$text		=	preg_replace( '!(?s)(\[u\])(.*?)(\[\/u\])!', '\2', $text ); //Underline
		$text		=	preg_replace( '!(?s)(\[b\])(.*?)(\[\/b\])!', '\2', $text ); //Bold
		$text		=	preg_replace( '!(?s)(\[strike\])(.*?)(\[\/strike\])!', '\2', $text ); //Strike
		$text		=	preg_replace( '!(?s)(\[sub\])(.*?)(\[\/sub\])!', '\2', $text ); //Subscript
		$text		=	preg_replace( '!(?s)(\[sup\])(.*?)(\[\/sup\])!', '\2', $text ); //Superscript
		$text		=	preg_replace( '!(?s)(\[ul\])(.*?)(\[\/ul\])!', '\2', $text ); //Unodered List
		$text		=	preg_replace( '!(?s)(\[ol\])(.*?)(\[\/ol\])!', '\2', $text ); //Ordered List
		$text		=	preg_replace( '!(?s)(\[li\])(.*?)(\[\/li\])!', '\2', $text ); //List Item
		$text		=	preg_replace( '!(?s)(\[size(.*?)\])(.*?)(\[\/size\])!', '\3', $text ); //Font Size
		$text		=	preg_replace( '!(?s)(\[color(.*?)\])(.*?)(\[\/color\])!', '\3', $text ); //Font Color
		$text		=	preg_replace( '!(?s)(\[img(.*?)\])(.*?)(\[\/img\])!', '...', $text ); //Image
		$text		=	preg_replace( '!(?s)(\[video(.*?)\])(.*?)(\[\/video\])!', '...', $text ); //Video
		$text		=	preg_replace( '!(?s)(\[hide(.*?)\])(.*?)(\[\/hide\])!', '...', $text ); //Hidden
		$text		=	preg_replace( '!(?s)(\[ebay(.*?)\])(.*?)(\[\/ebay\])!', '...', $text ); //Ebay Item
		$text		=	preg_replace( '!(?s)(\[file(.*?)\])(.*?)(\[\/file\])!', '...', $text ); //File
		$text		=	preg_replace( '!(?s)(\[attachment(.*?)\])(.*?)(\[\/attachment\])!', '...', $text ); //Attachment
		$text		=	preg_replace( '!(?s)(\[spoiler(.*?)\])(.*?)(\[\/spoiler\])!', '...', $text ); //Spoiler
		$text		=	preg_replace( '!(?s)(\[url(.*?)\])(.*?)(\[\/url\])!', '...', $text ); //URL
		$text		=	preg_replace( '!(?s)(\[confidential(.*?)\])(.*?)(\[\/confidential\])!', '', $text ); //Confidential
		$text		=	preg_replace( '%[[/!]*?[^[\]]*?\]%', '', $text ); //Remaining Tags
		$text		=	preg_replace( '/(\.\.\.\s*){2,}/', '... ', $text ); //Remove Duplicate Replacements
		$text		=	strip_tags( $text );
		$text		=	stripslashes( $text );

		if ( $length && ( strlen( $text ) > $length ) ) {
			$text	=	trim( substr( $text, 0, $length ) ) . '...';
			$text	=	preg_replace( '/(\.\.\.\s*){2,}/', '... ', $text ); //Remove Duplicate Replacements
		}

		$text		=	trim( $text );

		return $text;
	}
}


// TODO: Check why this is outside of a class: Such code should never be running at file-level but always inside a class function:
cbforumsClass::getModel();

/**
 * Class cbforumsTab
 * Tab for CB Forum
 */
class cbforumsTab extends cbTabHandler
{
	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  TabTable        $tab   The tab database entry
	 * @param  UserTable       $user  The user being displayed
	 * @param  int             $ui    1 for front-end, 2 for back-end
	 * @return string|boolean         Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework;

		$model						=	cbforumsClass::getModel( null, false );

		if ( ! $model->file ) {
			return CBTxt::T( 'No supported forum model found!' );
		}

		outputCbJs( 1 );
		outputCbTemplate( 1 );

		$plugin						=	cbforumsClass::getPlugin();
		$viewer						=&	CBuser::getUserDataInstance( $_CB_framework->myId() );
		$message					=	null;

		cbforumsClass::getTemplate( 'tab' );

		if ( $user->get( 'id' ) == $_CB_framework->myId() ) {
			$profileUrl				=	cbSef( 'index.php?option=com_comprofiler&tab=' . (int) $tab->tabid, false );

			if ( $this->params->get( 'tab_favs_display', 1 ) ) {
				$unfavorite			=	cbGetParam( $_REQUEST, 'forums_unfav', null );

				if ( $unfavorite ) {
					if ( cbforumsModel::unFavorite( $unfavorite, $user, $plugin ) ) {
						cbRedirect( $profileUrl, CBTxt::T( 'Favorite deleted successfully!' ) );
					} else {
						cbRedirect( $profileUrl, CBTxt::T( 'Favorite failed to delete.' ), 'error' );
					}
				}
			}

			if ( $this->params->get( 'tab_subs_display', 1 ) ) {
				$unsubscribePost	=	cbGetParam( $_REQUEST, 'forums_unsub', null );

				if ( $unsubscribePost ) {
					if ( cbforumsModel::unSubscribe( $unsubscribePost, $user, $plugin ) ) {
						cbRedirect( $profileUrl, CBTxt::T( 'Subscription deleted successfully!' ) );
					} else {
						cbRedirect( $profileUrl, CBTxt::T( 'Subscription failed to delete.' ), 'error' );
					}
				}

				$unsubscribeCat		=	cbGetParam( $_REQUEST, 'forums_unsubcat', null );

				if ( $unsubscribeCat ) {
					if ( cbforumsModel::unSubscribeCategory( $unsubscribeCat, $user, $plugin ) ) {
						cbRedirect( $profileUrl, CBTxt::T( 'Category subscription deleted successfully!' ) );
					} else {
						cbRedirect( $profileUrl, CBTxt::T( 'Category subscription failed to delete.' ), 'error' );
					}
				}
			}
		}

		$tab->params				=	$this->params;

		$class						=	$plugin->params->get( 'general_class', null );

		$return						=	'<div id="cbForums" class="cbForums' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
									.		'<div id="cbForumsInner" class="cbForumsInner">'
									.			HTML_cbforumsTab::showTab( $viewer, $user, $tab, $plugin )
									.		'</div>'
									.	'</div>';

		return $return;
	}
}

class cbforumsField extends cbFieldHandler {

	/**
	 * Formatter:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output               'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting           'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string      $reason               'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getFieldRow( &$field, &$user, $output, $formatting, $reason, $list_compare_types )
	{
		if ( ! cbforumsClass::getModel( null, false )->file ) {
			return null;
		} else {
			return parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );
		}
	}

	/**
	 * Legacy Field Strings:
	 * CBTxt::T( '_UE_FORUM_FORUMRANKING', 'Forum Ranking' );
	 * CBTxt::T( '_UE_FORUM_TOTALPOSTS', 'Total Posts' );
	 * CBTxt::T( '_UE_FORUM_KARMA', 'Karma' );
	 *
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		if ( ! cbforumsClass::getModel( null, false )->file ) {
			return null;
		}

		$forumStatus			=	$field->params->get( 'forumStatus', 'posts' );

		switch ( $forumStatus ) {
			case 'karma':
				$value			=	cbforumsModel::getUserKarma( $user );
				break;
			case 'rank':
				$rankTitle		=	$field->params->get( 'forumRankTitle', 1 );
				$rankImage		=	$field->params->get( 'forumRankImage', 1 );

				$value			=	cbforumsModel::getUserRank( $user, $rankTitle, $rankImage );
				break;
			case 'thankyou':
				$value			=	cbforumsModel::getUserThankYous( $user );
				break;
			default:
				$value			=	cbforumsModel::getUserPosts( $user );
				break;
		}

		switch ( $output ) {
			case 'html':
			case 'rss':
				return $this->formatFieldValueLayout( $value, $reason, $field, $user );
				break;
			case 'htmledit':
				return null;
				break;
			default:
				return $this->_formatFieldOutput( $field->get( 'name' ), $value, $output, false );
				break;
		}
	}
}
