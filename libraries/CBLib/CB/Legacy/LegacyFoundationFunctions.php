<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 7/8/14 5:34 PM $
* @package CB\Legacy
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Legacy
{
	use CB\Application\CBConfig;
	use CBLib\Application\Application;
	use CBLib\Application\ApplicationContainerInterface;

	use JFactory;
	use CBframework;
	use /** @noinspection PhpDeprecationInspection */ CBACL;

	defined('CBLIB') or die();

	/**
	 * CB\Legacy\LegacyFoundationFunctions Class implementation
	 *
	 */
	class LegacyFoundationFunctions
	{
		/**
		 * Checks that the constructor is executed only once
		 * @var boolean
		 */
		private static $loaded	=	false;

		/**
		 * Constructor (do not call directly, use DI to call it)
		 *
		 * Code in here was previously in plugin.foundation.php
		 *
		 * @param  ApplicationContainerInterface  $di  (This is injected by DI)
		 */
		function __construct( ApplicationContainerInterface $di )
		{
			if ( self::$loaded ) {
				return;
			}

			self::$loaded		=	true;

			define( '_CB_JQUERY_VERSION', '1.11.2' );		// IMPORTANT: when changing version here also change in the 2 XML installation files

			/**
			 * CB GLOBALS and initializations
			 */

			global $mainframe;

			$mainframe			=	JFactory::getApplication();
			/** @noinspection PhpDeprecationInspection */
			$acl				=	JFactory::getACL();
			$sefFunc			=	array( 'JRoute', '_' );
			$getVarFunction		=	array( 'JRequest', 'getVar' );
			$Jdocument			=	JFactory::getDocument();

			if ( $Jdocument->getType() == 'html' ) {
				$getDocFunction	=	array( 'JFactory', 'getDocument' );
			} else {
				$getDocFunction	=	false;
			}

			$editor				=	JFactory::getEditor();
			$editorDisplay		=	array(	'display' => array( 'call' => array( $editor , 'display' ), 'args' => 'noid' ),
											'save'	  => array( 'call' => array( $editor , 'save' ),    'args' => 'noid' ),
											'returns' => true
			);
			$aclParams			=	array(	'canEditUsers'				=>	array( 'com_comprofiler', 'core.edit', 'users', null ),
											 'canBlockUsers'			=>	array( 'com_comprofiler', 'core.edit.state', 'users', null ),
											 'canReceiveAdminEmails'	=>	array( 'com_comprofiler', 'core.admin', 'users', null ),
											 'canEditOwnContent'		=>	array( 'com_content', 'core.edit.own', 'users', null, 'content', 'own' ),
											 'canAddAllContent'	 		=>	array( 'com_content', 'core.create', 'users', null, 'content', 'all' ),
											 'canEditAllContent' 		=>	array( 'com_content', 'core.edit', 'users', null, 'content', 'all' ),
											 'canPublishContent'		=>	array( 'com_content', 'core.edit.state', 'users', null, 'content', 'all' ),
											 'canInstallPlugins'		=>	array( 'com_installer', 'core.manage', 'users', null ),
											 'canManageUsers'			=>	array( 'com_comprofiler', 'core.manage', 'users', null )
			);

			/**
			 * CB framework
			 * @global CBframework $_CB_framework
			 */
			global $_CB_framework;
			$_CB_framework			=	new CBframework( $mainframe, $aclParams, $sefFunc, array( 'option' => 'com_comprofiler' ), $getVarFunction, $getDocFunction, $editorDisplay );

			/** @see CBACL */
			$_CB_framework->acl		=	$di->get( 'CBACL', array( 'acl' => $acl ) );

			$di->set( 'CBFramework', $_CB_framework, true );

			/**
			 * CB Config
			 * @deprecated 2.0, use Application::Config() to get the Configuration Registry object
			 * @see Application::Config()
			 * @var array
			 */
			global $ueConfig;

			$ueConfig['version']		=	CBLIB;

			// This doesn't load if yet if in installer, as config database table may not yet be created:
			CBConfig::loadLegacyCBueConfig( true );

			// Lazy-loads later the language files:
			// This is useful if CBLib is loaded too early by plugins before language selection happened in Joomla (solves bug #5360).
			$di->get( 'Language' )->addLanguageFile(
				function() use ( $di ) {
					if ( $di->getCms()->getClientId() === 0 ) {
						cbimport( 'language.front' );
					} else {
						cbimport( 'language.all' );
					}
				}
			);

			define( '_CB_SPOOFCHECKS', ( isset( $ueConfig['enableSpoofCheck'] ) && $ueConfig['enableSpoofCheck'] ) ? 1 : 0 );
		}
	}
}

/**
 * LEGACY FUNCTIONS FROM plugin.foundation.php :
 * =============================================
 */

namespace
{
	use CB\Legacy\LegacyComprofilerFunctions;
	use CBLib\Application\Application;
	use CBLib\Input\Get;
	use CBLib\Language\CBTxt;
	use CBLib\Registry\GetterInterface;
	use CB\Database\Table\UserTable;

//	use JUser;
//	use JVersion;


	/**
	 * CB 2.0 Legacy functions (still usable until CB 3.0)
	 */

	/**
	 * CB Functions
	 */

	/**
	 * maps view to task as CB does not use view
	 */
	function cbMapViewToTask() {
		if ( isset( $_GET['task'] ) ) {
			// Task exists in GET; override view to maintain B/C:
			$_GET['view']		=	$_GET['task'];
		} elseif ( isset( $_GET['view'] ) ) {
			// View exists in GET, but task doesn't:
			$_GET['task']		=	$_GET['view'];
		}

		if ( isset( $_POST['task'] ) ) {
			// Task exists in POST; override view to maintain B/C:
			$_POST['view']		=	$_POST['task'];
		} elseif ( isset( $_POST['view'] ) ) {
			// View exists in POST, but task doesn't:
			$_POST['task']		=	$_POST['view'];
		}

		if ( isset( $_REQUEST['task'] ) ) {
			// Task exists in REQUEST; override view to maintain B/C:
			$_REQUEST['view']	=	$_REQUEST['task'];
		} elseif ( isset( $_REQUEST['view'] ) ) {
			// View exists in REQUEST, but task doesn't:
			$_REQUEST['task']	=	$_REQUEST['view'];
		}
	}

	/**
	 * Gets CB $task Itemid or by default userprofile Itemid
	 *
	 * @param bool $htmlspecialchars TRUE if should return "&amp:Itemid...." instead of "&Itemid..." (with FALSE as default), === 0 if return only int
	 * @param string $task task/view  e.g. 'userslist'
	 * @param null|string $additional append additional string to end of URL for deeper URL matching (note: this is unchecked)
	 * @return null|string
	 */
	function getCBprofileItemid( $htmlspecialchars = false, $task = 'userprofile', $additional = null ) {
		global $_CB_database, $_CB_framework;

		static $cacheItemids									=	array();

		if ( ! isset( $cacheItemids[$task][$additional] ) ) {
			if ( class_exists( 'CB\Database\Table\UserTable', false ) ) {
				$viewLevels										=	Application::MyUser()->getAuthorisedViewLevels();
			} else {
				// Compute View Level using CMS without loading cb.table and cb.database if they are not already loaded (e.g. when using this function in modules):
				$viewLevels										=	JUser::getInstance()->getAuthorisedViewLevels();
			}

			$queryAccess										=	"\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1"
																.	"\n AND " . $_CB_database->NameQuote( 'access' ) . " IN ( " . implode( ',', cbArrayToInts( $viewLevels ) ) . " )";
			if ( checkJversion() >= 2 ) {
				$queryAccess									.=	"\n AND " . $_CB_database->NameQuote( 'language' ) . " IN ( " . $_CB_database->Quote( $_CB_framework->getCfg( 'lang_tag' ) ) . ", " . $_CB_database->Quote( '*' ) . ", " . $_CB_database->Quote( '' ) . " )";
			}

			// Try to find an itemid for the supplied task/view with additional parameter parsing included:
			if ( ( $task !== 'userprofile' ) && is_string( $task ) ) {
				// Check the current active menu item first to avoid an unnecessary query:
				$Itemid											=	$_CB_framework->itemid( 'option=com_comprofiler&view=' . $task . $additional );

				if ( ! $Itemid ) {
					$query										=	'SELECT ' . $_CB_database->NameQuote( 'id' )
																.	"\n FROM " . $_CB_database->NameQuote( '#__menu' )
																.	"\n WHERE ( " . $_CB_database->NameQuote( 'link' ) . " LIKE " . $_CB_database->Quote( 'index.php?option=com_comprofiler&task=' . $_CB_database->getEscaped( $task, true ) . $additional . '%', false )
																.	' OR ' . $_CB_database->NameQuote( 'link' ) . ' LIKE ' . $_CB_database->Quote( 'index.php?option=com_comprofiler&view=' . $_CB_database->getEscaped( $task, true ) . $additional . '%', false ) . ' )'
																.	$queryAccess;
					$_CB_database->setQuery( $query );
					$Itemid										=	(int) $_CB_database->loadResult();
				}

				// If additional was specified and no itemid found then lets see if a more top level itemid is available for same task/view:
				if ( ( ! $Itemid ) && $additional ) {
					// Check the current active menu item first to avoid an unnecessary query:
					if ( ! isset( $cacheItemids[$task][null] ) ) {
						$cacheItemids[$task][null]				=	$_CB_framework->itemid( 'option=com_comprofiler&view=' . $task );

						if ( ! $cacheItemids[$task][null] ) {
							$query								=	'SELECT ' . $_CB_database->NameQuote( 'id' )
																.	"\n FROM " . $_CB_database->NameQuote( '#__menu' )
																.	"\n WHERE ( " . $_CB_database->NameQuote( 'link' ) . " LIKE " . $_CB_database->Quote( 'index.php?option=com_comprofiler&task=' . $_CB_database->getEscaped( $task, true ) . '%', false )
																.	' OR ' . $_CB_database->NameQuote( 'link' ) . ' LIKE ' . $_CB_database->Quote( 'index.php?option=com_comprofiler&view=' . $_CB_database->getEscaped( $task, true ) . '%', false ) . ' )'
																.	$queryAccess;
							$_CB_database->setQuery( $query );
							$cacheItemids[$task][null]			=	(int) $_CB_database->loadResult();
						}
					}

					$Itemid										=	$cacheItemids[$task][null];
				}
			} else {
				$Itemid											=	null;
			}

			if ( ( $task === 'userprofile' ) || ( ! $Itemid ) ) {
				// $task used to be a boolean before CB 1.2.3 but with no effect:
				if ( ! isset( $cacheItemids['userprofile'][null] ) ) {
					// Check the current active menu item first to avoid an unnecessary query:
					$cacheItemids['userprofile'][null]			=	$_CB_framework->itemid( 'option=com_comprofiler&view=userprofile' );

					if ( ! $cacheItemids['userprofile'][null] ) {
						$query									=	'SELECT ' . $_CB_database->NameQuote( 'id' )
																.	"\n FROM " . $_CB_database->NameQuote( '#__menu' )
																.	"\n WHERE ( " . $_CB_database->NameQuote( 'link' ) . " LIKE " . $_CB_database->Quote( 'index.php?option=com_comprofiler' )
																.	' OR ' . $_CB_database->NameQuote( 'link' ) . ' LIKE ' . $_CB_database->Quote( 'index.php?option=com_comprofiler&task=userprofile' )
																.	' OR ' . $_CB_database->NameQuote( 'link' ) . ' LIKE ' . $_CB_database->Quote( 'index.php?option=com_comprofiler&view=userprofile' ) . ' )'
																.	$queryAccess;
						$_CB_database->setQuery( $query );
						$cacheItemids['userprofile'][null]		=	(int) $_CB_database->loadResult();
					}
				}

				$Itemid											=	$cacheItemids['userprofile'][null];

				// if no user profile, try getting itemid of the default list:
				if ( ! $Itemid ) {
					if ( ! isset( $cacheItemids['userslist'][null] ) ) {
						// Check the current active menu item first to avoid an unnecessary query:
						$cacheItemids['userslist'][null]		=	$_CB_framework->itemid( 'option=com_comprofiler&view=userslist' );

						if ( ! $cacheItemids['userslist'][null] ) {
							$query								=	'SELECT ' . $_CB_database->NameQuote( 'id' )
																.	"\n FROM " . $_CB_database->NameQuote( '#__menu' )
																.	"\n WHERE ( " . $_CB_database->NameQuote( 'link' ) . " LIKE " . $_CB_database->Quote( 'index.php?option=com_comprofiler&task=userslist' )
																.	' OR ' . $_CB_database->NameQuote( 'link' ) . ' LIKE ' . $_CB_database->Quote( 'index.php?option=com_comprofiler&view=userslist' ) . ' )'
																.	$queryAccess;
							$_CB_database->setQuery( $query );
							$cacheItemids['userslist'][null]	=	(int) $_CB_database->loadResult();
						}
					}

					$Itemid										=	$cacheItemids['userslist'][null];
				}
			}

			$cacheItemids[$task][$additional]					=	$Itemid;
		}

		if ( $cacheItemids[$task][$additional] ) {
			if ( is_bool( $htmlspecialchars ) ) {
				return ( $htmlspecialchars ? "&amp;" : "&") . "Itemid=" . $cacheItemids[$task][$additional];
			} else {
				return $cacheItemids[$task][$additional];
			}
		} else {
			return null;
		}
	}

	/**
	 * Includes CB library
	 * --- usage: cbimport('cb.xml.simplexml');
	 *
	 * @param  string  $lib
	 * @return void
	 */
	function cbimport( $lib ) {
		global $_CB_framework;

		static $imported			=	array();
		static $tmpClasses			=	array( 'cb.html', 'cb.tabs', 'cb.field', 'cb.calendar', 'cb.connection', 'cb.notification' );

		if ( ! isset( $imported[$lib] ) ) {
			$liblow					=	strtolower( $lib );
			$pathAr					=	explode( '.', $liblow );

			if ( $pathAr[0] == 'language' ) {
				$langPath			=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/language';
				$langTag			=	$_CB_framework->getCfg( 'lang_tag' );

				if ( ! isset( $imported['language.front'] ) ) {
					$imported['language.front']	=	true;

					CBTxt::import( $langPath, $langTag, 'language.php' );
				}

				if ( ( $pathAr[1] == 'all' ) && ( ! isset( $imported['language.admin'] ) ) ) {
					$imported['language.admin']	=	true;

					CBTxt::import( $langPath, $langTag, 'admin_language.php' );
				}

			} elseif ( $lib == 'cb.plugins' ) {
				// this part is temporary until we refactor those 2 files into the corresponding CB libraries:
				/** @noinspection PhpIncludeInspection */
				require_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.class.php' );
			} elseif ( in_array( $lib, $tmpClasses ) ) {
				// We need to make absolute sure we have our translations before we load our plugins and classes:
				if ( $_CB_framework->getUi() == 1 ) {
					$loadLang		=	'language.front';
				} else {
					$loadLang		=	'language.all';
				}

				if ( ! isset( $imported[$loadLang] ) ) {
					cbimport( $loadLang );
				}

				// this part is temporary until we refactor those 2 files into the corresponding CB libraries:
				if ( ! isset( $imported['cb.plugins'] ) ) {
					$imported['cb.plugins']	=	true;

					/** @noinspection PhpIncludeInspection */
					require_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.class.php' );
				}

				if ( ! isset( $imported['class'] ) ) {
					$imported['class']	=	true;

					/** @noinspection PhpIncludeInspection */
					require_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_comprofiler/comprofiler.class.php' );

					new LegacyComprofilerFunctions();

				}
			} elseif ( $lib == 'cb.imgtoolbox' ) {
				// this part is temporary until we refactor those 2 files into the corresponding CB libraries:
				/** @noinspection PhpIncludeInspection */
				require_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_comprofiler/imgToolbox.class.php' );
			} elseif ( $lib == 'cb.snoopy' ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_comprofiler/Snoopy.class.php' );
			} else {
				array_pop( $pathAr );

				$filepath		=	implode( '/', $pathAr ) . (count( $pathAr ) ? '/' : '' ) . $liblow . '.php';

				/** @noinspection PhpIncludeInspection */
				require_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_comprofiler/library/' . $filepath );
			}

			$imported[$lib]		=	true;
		}
	}

	/**
	 * Sanitizes an array of (int) as REFERENCE
	 *
	 * @param  array $array  in/out
	 * @return array
	 */
	function & cbArrayToInts( &$array ) {
		foreach ( $array as $k => $v ) {
			$array[$k]	=	(int) $v;
		}
		return $array;
	}

	/**
	 * Sanitizes an array to array of (int) as RETURN
	 *
	 * @param  array $array  in ONLY
	 * @return array
	 */
	function cbToArrayOfInt( $array ) {
		return cbArrayToInts( $array );
	}

	/**
	 * Does the opposite of htmlspecialchars()
	 *
	 * @param  string  $text
	 * @return string
	 */
	function cbUnHtmlspecialchars( $text ) {
		return str_replace( array( "&amp;", "&quot;", "&#039;", "&lt;", "&gt;" ), array( "&", "\"", "'", "<", ">" ), $text );
	}

	/**
	 * String based find and replace that is case insensitive and works on php4 too
	 * same as PHP5 str_ireplace()
	 *
	 * @param  string  $search   value to look for
	 * @param  string  $replace  value to replace with
	 * @param  string  $subject  text to be searched
	 * @return string            with text searched and replaced
	 */
	function cbstr_ireplace( $search, $replace, $subject ) {
		if ( function_exists('str_ireplace') ) {
			return str_ireplace($search,$replace,$subject);		// php 5 only
		}
		$srchlen = strlen($search);    // lenght of searched string
		$result  = "";

		while ( true == ( $find = stristr( $subject, $search ) ) ) {	// find $search text in $subject - case insensitiv
			$srchtxt = substr($find,0,$srchlen);    			// get new case-sensitively-correct search text
			$pos	 = strpos( $subject, $srchtxt );			// stripos is php5 only...
			$result	 .= substr( $subject, 0, $pos ) . $replace;	// replace found case insensitive search text with $replace
			$subject = substr( $subject, $pos + $srchlen );
		}
		return $result . $subject;
	}

	/**
	 * Translates text strings from CB and core cms ('_UE_....') into current language
	 *
	 * @deprecated 2.0 : Use \CBLib\Language\CBTxt::T()
	 * @see        \CBLib\Language\CBTxt::T()
	 *
	 * @param  string  $text
	 * @return string
	 */
	function getLangDefinition( $text )
	{
		return CBTxt::T( $text );
	}

	/**
	 * Check Mambo/Joomla/others version for API
	 *
	 * @param  string  $info  'api', 'product', 'release', 'j3.0+', or custom values (e.g. 2.5+, >3.0, =1.5)
	 * @return mixed          'api'     : API version: =0 = mambo 4.5.0-4.5.3+Joomla 1.0.x, =1 = Joomla! 1.1, >1 newever ones: maybe compatible, <0: -1: Mambo 4.6
	 *                        'product' : product name
	 *                        'release' : php-style release number
	 */
	function checkJversion( $info = 'api' ) {
		static $version						=	array();

		if ( isset( $version[$info] ) ) {
			return $version[$info];
		}

		if ( class_exists( 'JVersion' ) ) {
			$VO								=	new JVersion();
		} else {
			global $_VERSION;

			if ( $_VERSION ) {
				$VO							=	$_VERSION;
			} else {
				trigger_error( 'Unable to determine CMS version.', E_USER_ERROR );
				die();
			}
		}

		switch ( $info ) {
			case 'api':
				$cms_version				=	substr( $VO->RELEASE, 0, 3 );

				if ( $VO->PRODUCT == 'Mambo' ) {
					if ( strcasecmp( $cms_version, '4.6' ) < 0 ) {
						$version[$info]		=	0;
					} else {
						$version[$info]		=	-1;
					}
				} elseif ( $VO->PRODUCT == 'Elxis' ) {
					$version[$info]			=	0;
				} elseif ( $VO->PRODUCT == 'MiaCMS' ) {
					$version[$info]			=	-1;
				} elseif ( ( $VO->PRODUCT == 'Joomla!' ) || ( $VO->PRODUCT == 'Accessible Joomla!' ) ) {
					if ( strcasecmp( $cms_version, '1.6' ) >= 0 ) {
						$version[$info]		=	2;
					} elseif ( strcasecmp( $cms_version, '1.5' ) == 0 ) {
						$version[$info]		=	1;
					} else {
						$version[$info]		=	0;
					}
				} else {
					$version[$info]			=	0;
				}
				break;
			case 'product':
				$version[$info]				=	$VO->PRODUCT;
				break;
			case 'release':
				$version[$info]				=	$VO->RELEASE . '.' . $VO->DEV_LEVEL;
				break;
			case 'version':
				$version[$info]				=	substr( $VO->RELEASE, 0, 3 );
				break;
			case 'dev_level':
				$version[$info]				=	$VO->DEV_LEVEL;
				break;
			default:
				$versionCompare				=	strcasecmp( $VO->RELEASE . '.' . $VO->DEV_LEVEL, preg_replace( '/[^.\d]/i', '', $info ) );

				if ( $VO->PRODUCT == 'Joomla!' ) {
					if ( strpos( $info, '-' ) !== false ) {
						$version[$info]		=	( $versionCompare <= 0 );
					} elseif ( strpos( $info, '+' ) !== false ) {
						$version[$info]		=	( $versionCompare >= 0 );
					} elseif ( strpos( $info, '>' ) !== false ) {
						$version[$info]		=	( $versionCompare > 0 );
					} elseif ( strpos( $info, '<' ) !== false ) {
						$version[$info]		=	( $versionCompare < 0 );
					} else {
						$version[$info]		=	( $versionCompare == 0 );
					}
				} else {
					$version[$info]			=	false;
				}
				break;
		}

		return $version[$info];
	}

	define( "_CB_NOTRIM", 0x0001 );
	define( "_CB_ALLOWRAW", 0x0004 );
	/**
	 * Utility function to return a value from a named array or a specified default.
	 * TO CONTRARY OF MAMBO AND JOOMLA mos Get Param:
	 * 1) DOES NOT MODIFY ORIGINAL ARRAY
	 * 2) Does sanitize ints
	 * 3) Does return default array() for a default value array(0) which indicates sanitizing an array of ints.
	 *
	 * @param  array   $arr   A named array
	 * @param  string  $name  The key to search for
	 * @param  mixed   $def   The default value to give if no key found
	 * @param  int     $mask  An options mask: _MOS_NOTRIM prevents trim, _MOS_ALLOWHTML allows safe html, _MOS_ALLOWRAW allows raw input
	 * @return string|array
	 */
	function cbGetParam( &$arr, $name, $def=null, $mask=0 ) {
		if ( isset( $arr[$name] ) ) {
			if ( is_array( $arr[$name] ) ) {
				$ret			=	array();
				foreach ( array_keys( $arr[$name] ) as $k ) {
					$ret[$k]	=	cbGetParam( $arr[$name], $k, $def, $mask);
					if ( $def === array( 0 ) ) {
						$ret[$k] =	(int) $ret[$k];
					}
				}
			} else {
				$ret			=	$arr[$name];
				if ( is_string( $ret ) ) {
					if ( ! ( $mask & _CB_NOTRIM ) ) {
						$ret	=	trim( $ret );
					}

					if ( ! ( $mask & _CB_ALLOWRAW ) ) {
						$ret	=	Get::clean( $ret, GetterInterface::STRING );
					}

					if ( is_int( $def ) ) {
						$ret	=	(int) $ret;
					} elseif ( is_float( $def ) ) {
						$ret	=	(float) $ret;
					} elseif ( !  get_magic_quotes_gpc() ) {
						$ret	=	addslashes( $ret );
					}
				}
			}
			return $ret;
		} elseif ( false !== ( $firstSeparator = strpos( $name, '[' )  ) ) {
			// html-input-name-encoded array selection, e.g. a[b][c]
			$indexes			=	null;
			$mainArrName		=	substr( $name, 0, $firstSeparator );
			$count				=	preg_match_all( '/\\[([^\\[\\]]+)\\]/', substr( $name, $firstSeparator ), $indexes );
			if ( isset( $arr[$mainArrName] ) && ( $count > 0 ) ) {
				$a				=	$arr[$mainArrName];
				for ( $i = 0; $i < ( $count - 1 ); $i++ ) {
					if ( ! isset( $a[$indexes[1][$i]] ) ) {
						$a		=	null;
						break;
					}
					$a			=	$a[$indexes[1][$i]];
				}
			} else {
				$a				=	null;
				$i				=	null;
			}
			if ( $a !== null ) {
				return cbGetParam( $a, $indexes[1][$i], $def, $mask );
			}
		}
		if ( $def === array( 0 ) ) {
			return array();
		}
		return $def;
	}

	/**
	 * Redirects browser to new $url with a $message .
	 * No return from this function !
	 *
	 * @param  string  $url
	 * @param  string  $message
	 * @param  string  $messageType  'message', 'error'
	 */
	function cbRedirect( $url, $message = '', $messageType = 'message' ) {
		global $_CB_framework, $_CB_database;

		if ( ( $_CB_framework->getUi() == 1 ) && checkJversion( '3.4.3-' ) ) {
			switch( $messageType ) {
				case 'message':
					$messageType	=	'success';
					break;
				case 'error':
					$messageType	=	'danger';
					break;
				case 'notice':
					$messageType	=	'warning';
					break;
			}
		} elseif ( ( $_CB_framework->getUi() == 2 ) || checkJversion( '2.0+' ) ) {
			switch( $messageType ) {
				case 'success':
					$messageType	=	'message';
					break;
				case 'danger':
					$messageType	=	'error';
					break;
				case 'warning':
					$messageType	=	'notice';
					break;
			}
		}

		if ( ( $_CB_framework->getCfg( 'debug' ) > 0 ) && ( ob_get_length() || ( $_CB_framework->getCfg( 'debug' ) > 1 ) ) ) {
			$outputBufferLength		=	ob_get_length();
			$ticker					=	$_CB_database->getCount();
			$log					=	$_CB_database->getLog();
			echo '<br /><br /><strong>Site Debug mode: CB redirection';
			if ( $message ) {
				echo ' with ' . $messageType . ' "' . $message . '"';
			}
			if ( $outputBufferLength ) {
				echo ' <strong>without empty output</strong>';
			}
			echo "<br /><p><em>During its normal operations Community Builder often redirects you between pages and this causes potentially interesting debug information to be missed. "
				. "When your site is in debug mode (global Joomla config is site debug ON), some of these automatic redirects are disabled. "
				. "This is a normal feature of the debug mode and does not directly mean that you have any problems.</em></p>"
				. '</strong>Click this link to proceed with the next page (in non-debug mode this is automatic): ';
			echo '<a href="' . $url . '">' . htmlspecialchars( $url ) . '</a><br /><br /><hr />';

			echo $ticker . ' queries executed'
				. '<pre>';
			foreach ( $log as $k => $sql ) {
				echo $k + 1 . "\n" . htmlspecialchars( $sql ) . '<hr />';
			}
			echo '</hr>'
				. '</hr>POST: ';
			var_export( $_POST );
			echo '</pre>';
			die();
		} else {
			$_CB_framework->redirect( $url, $message, $messageType );
		}
	}

	/**
	 * stripslashes() string or nested array of strings
	 *
	 * @param  string|array  with slashes
	 * @return string|array  without slashes
	 */
	function cbStripslashes( $value ) {
		if ( is_string( $value ) ) {
			$striped				=	stripslashes( $value );
		} else {
			if ( is_array( $value ) ) {
				$striped			=	array();
				foreach ( array_keys( $value ) as $k ) {
					$striped[$k]	=	cbStripslashes( $value[$k] );
				}
			} else {
				$striped			=	$value;
			}
		}
		return $striped;
	}

	/**
	 * Returns full path to template directory, as live URL (live_site, by default), absolute directory path
	 *
	 * @param  string    $output        'live_site' (with trailing /), 'absolute_path' (without trailing /), 'dir' name only (depreciated was: int  DEPRECIATED: info for backwards-compatibility: user interface : 1: frontend, 2: backend (not used anymore)
	 * @param  string    $templateName  null: according to settings, string: name of template (directory)
	 * @param  null|int  $ui            null: according to location, 1: frontend, 2: backend
	 * @return string                   Template directory path with trailing '/'
	 */
	function selectTemplate( $output = 'live_site', $templateName = null, $ui = null ) {
		global $_CB_framework, $ueConfig;

		if ( $ui === null ) {
			$ui					=	$_CB_framework->getUi();
		} else {
			$ui					=	(int) $ui;
		}

		if ( $templateName == null ) {
			if ( $ui == 1 ) {
				$templateName	=	$ueConfig['templatedir'];
			} else {
				$templateName	=	'default';
			}
		}

		if ( $output == 'dir' ) {
			return $templateName;
		} elseif ( $output == 'absolute_path' ) {
			return $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/templates/' . $templateName;
		} elseif ( $output == 'relative_path' ) {
			return '/components/com_comprofiler/plugin/templates/' . $templateName;
		} else {
			return ( $ui == 2 ? '..' : $_CB_framework->getCfg( 'live_site' ) ) . '/components/com_comprofiler/plugin/templates/' . $templateName . '/';
		}
	}

	/**
	 * Generates a random salted anti-spoofing string, or if $string and $secret are provided, uses them to generate the hash
	 *
	 * @param  string  $string  [optional] existing string
	 * @param  string  $secret  [optional] existing secret
	 * @return string           anti-spoofing string
	 */
	function cbSpoofString( $string = null, $secret = null ) {
		global $_CB_framework;

		$date			=	date( 'dmY' );
		if ( $string === null ) {
			$salt		=	array();
			$salt[0]	=	mt_rand( 1, 2147483647 );
			$salt[1]	=	mt_rand( 1, 2147483647 );		// 2 * 31 bits random
		} else {
			$salt		=	sscanf( $string, 'cbm_%08x_%08x_%s' );
			if ( $string != sprintf( 'cbm_%08x_%08x_%s', $salt[0], $salt[1], md5( $salt[0] . $date . $_CB_framework->getUi() . $_CB_framework->getCfg( 'db' ) . $_CB_framework->getCfg('secret') . $secret . $salt[1] ) ) ) {
				$date	=	date( 'dmY', time() - 64800 );	// 18 extra-hours of grace after midnight.
			}
		}
		return sprintf( 'cbm_%08x_%08x_%s', $salt[0], $salt[1], md5( $salt[0] . $date . $_CB_framework->getUi() . $_CB_framework->getCfg( 'db' ) . $_CB_framework->getCfg('secret') . $secret . $salt[1] ) );
	}

	/**
	 * Gives the name of the anti-spoofing input field
	 *
	 * @return string
	 */
	function cbSpoofField() {
		return 'cbsecuritym3';
	}

	/**
	 * Computes and returns an antifspoofing additional input tag
	 *
	 * @param  string  $secret         Secret key for the anti-spoofing input
	 * @param  string  $cbSpoofString  [optional] Anti-spoofing string
	 * @return string                  HTML "<input type="hidden...\n" tag
	 */
	function cbGetSpoofInputTag( $secret = null, $cbSpoofString = null ) {
		if ( $cbSpoofString === null ) {
			$cbSpoofString		=	cbSpoofString( null, $secret );
		}
		return "<input type=\"hidden\" name=\"" . cbSpoofField() . "\" value=\"" .  $cbSpoofString . "\" />\n";
	}

	/**
	 * Checks for atack-vector strings
	 *
	 * @param  $array
	 * @param  $badStrings
	 */
	function _cbjosSpoofCheck($array, $badStrings) {
		foreach ($array as $v) {
			foreach ($badStrings as $v2) {
				if (is_array($v)) {
					_cbjosSpoofCheck($v, $badStrings);
				} else if (strpos( $v, $v2 ) !== false) {
					header( "HTTP/1.0 403 Forbidden" );
					exit( CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
				}
			}
		}
		unset( $v, $v2, $badStrings );
	}

	/**
	 * Checks spoof value and other spoofing and injection tricks
	 *
	 * @param  string   $secret   extra-hashing value for this particular spoofCheck
	 * @param  string   $var      'POST', 'GET', 'REQUEST'
	 * @param  int      $mode     1: exits with script to display error and go back, 2: returns true or false.
	 * @return boolean  or exit   If $mode = 2 : returns false if session expired.
	 */
	function cbSpoofCheck( $secret = null, $var = 'POST', $mode = 1 ) {
		global $_POST, $_GET, $_REQUEST;

		if ( _CB_SPOOFCHECKS ) {
			if ( $var == 'GET' ) {
				$validateValue 	=	cbGetParam( $_GET,     cbSpoofField(), '' );
			} elseif ( $var == 'REQUEST' ) {
				$validateValue 	=	cbGetParam( $_REQUEST, cbSpoofField(), '' );
			} else {
				$validateValue 	=	cbGetParam( $_POST,    cbSpoofField(), '' );
			}
			if ( ( ! $validateValue ) || ( $validateValue != cbSpoofString( $validateValue, $secret ) ) ) {
				if ( $mode == 2 ) {
					return false;
				}
				_cbExpiredSessionJSterminate( 200 );
				exit;
			}
		}
		// First, make sure the form was posted from a browser.
		// For basic web-forms, we don't care about anything
		// other than requests from a browser:
		if (!isset( $_SERVER['HTTP_USER_AGENT'] )) {
			header( 'HTTP/1.0 403 Forbidden' );
			exit( CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
		}

		// Make sure the form was indeed POST'ed:
		//  (requires your html form to use: action="post")
		if (!$_SERVER['REQUEST_METHOD'] == 'POST' ) {
			header( 'HTTP/1.0 403 Forbidden' );
			exit( CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
		}

		// Attempt to defend against header injections:
		$badStrings = array(
			'Content-Type:',
			'MIME-Version:',
			'Content-Transfer-Encoding:',
			'bcc:',
			'cc:'
		);

		// Loop through each POST'ed value and test if it contains
		// one of the $badStrings:
		_cbjosSpoofCheck( $_POST, $badStrings );

		// Made it past spammer test, free up some memory
		// and continue rest of script:
		unset( $badStrings );
		return true;
	}
	function _cbExpiredSessionJSterminate( $code = 403 ) {
		if ( $code == 403 ) {
			header( 'HTTP/1.0 403 Forbidden' );
		}
		echo "<script type=\"text/javascript\">alert('"
			. addslashes(
				CBTxt::T( 'UE_SESSION_EXPIRED', 'Session expired or cookies are not enabled in your browser. Please press "reload page" in your browser, and enable cookies in your browser.' )
				. ' '
				. CBTxt::T( 'UE_PLEASE_REFRESH', 'Please refresh/reload page before filling-in.' )
			)
			. "'); window.history.go(-1);</script> \n";
		exit;
	}

	/**
	 * CB Classes : Moved to CBLib/CB/Compatibility for auto-loading
	 */

	/**
	 * Converts an URL to an absolute URI with SEF format
	 *
	 * @param  string   $string        The relative URL
	 * @param  boolean  $htmlSpecials  TRUE (default): apply htmlspecialchars to sefed URL, FALSE: don't.
	 * @param  string   $format        'html', 'component', 'raw', 'rawrel'		(added in CB 1.2.3)
	 * @param  int      $ssl           1 force HTTPS, 0 leave as is, -1 for HTTP		(added in CB 1.10.0)
	 * @return string                  The absolute URL (relative if rawrel)
	 */
	function cbSef( $string, $htmlSpecials = true, $format = 'html', $ssl = 0 ) {
		global $_CB_framework;
		return $_CB_framework->cbSef( $string, $htmlSpecials, $format, $ssl );
	}

	/**
	 * Displays "Not authorized", and if not logged-in "you need to login"
	 *
	 */
	function cbNotAuth( $enqueue = false ) {
		global $_CB_framework;

		if ( $enqueue ) {
			$_CB_framework->enqueueMessage(
				CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) . ( $_CB_framework->myId() < 1 ? ' ' .  CBTxt::T( 'You need to log in.' ) : null ),
				'error'
			);
		} else {
			$return			=	'<div class="cbNotAuth cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
				.		'<div class="error">' . CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) . '</div>';

			if ( $_CB_framework->myId() < 1 ) {
				$return		.=		'<div class="error">' . CBTxt::Th( 'UE_DO_LOGIN', 'You need to log in.' ) . '</div>';
			}

			$return			.=	'</div>';

			echo $return;
		}
	}
}
