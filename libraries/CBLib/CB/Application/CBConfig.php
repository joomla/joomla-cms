<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/30/14 11:17 PM $
* @package CB\Application
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Application;

use CBLib\Application\Application;
use CBLib\Application\Config;
use CBLib\Database\DatabaseDriverInterface;

defined('CBLIB') or die();

/**
 * CB\Application\CBConfig Class implementation
 * 
 */
class CBConfig extends Config
{
	/**
	 * Config is ready for loading (this is only needed with legacy ueConfig support in installer
	 *
	 * @var bool
	 */
	private static $configReady = true;

	/**
	 * Returns the JSON-encoded string for the CB Configuration
	 *
	 * @param  DatabaseDriverInterface  $db  Database Driver
	 * @return null|string                   JSON-encoded string (or NULL if failed)
	 */
	public static function getConfig( DatabaseDriverInterface $db )
	{
		$db->setQuery( "SELECT " . $db->NameQuote( 'params' )
				.	"\n FROM " . $db->NameQuote( '#__comprofiler_plugin' )
				.	"\n WHERE " . $db->NameQuote( 'id' ) . " = 1");

		$json	=	$db->loadResult();

		if ( $json ) {
			return (array) json_decode( $json );
		}

		return array();
	}

	/**
	 * Legacy support function: Sets config ready status (for installer, to delay CB Config loading until database is set up)
	 *
	 * @param  boolean  $ready
	 * @return void
	 */
	public static function setCbConfigReadyToLoad( $ready )
	{
		self::$configReady	=	$ready;
	}

	/**
	 * Legacy support function: Loads the CB Config from database (if it is ready) and
	 * Sets the global $ueConfig
	 * It also loads the Language handler powering CBTxt.
	 *
	 * @param  boolean  $legacy  prepare legacy config params
	 * @return void
	 */
	public static function loadLegacyCBueConfig( $legacy = false ) {
		global $ueConfig;

		if ( self::$configReady ) {
			$version	=	$ueConfig['version'];

			$config		=	Application::Config();

			$ueConfig	=	$config->asArray();

			$ueConfig['version']	=	$version;

			// Legacy config options for B/C:
			if ( $legacy ) {
				if ( isset( $ueConfig['profile_viewaccesslevel'] ) ) {
					switch ( (int) $ueConfig['profile_viewaccesslevel'] ) {
						case 1: // Public
						case 5: // Guest
							$profileAccess			=	'1'; // Public
							break;
						case 3: // Special
							$profileAccess			=	'3'; // Author
							break;
						default:
							$profileAccess			=	'2'; // Registered
							break;
					}
				} else {
					$profileAccess					=	'2'; // Registered
				}

				if ( isset( $ueConfig['moderator_viewaccesslevel'] ) ) {
					switch ( (int) $ueConfig['moderator_viewaccesslevel'] ) {
						case 1: // Public
						case 5: // Guest
							$modAccess				=	'1'; // Public
							break;
						case 2: // Registered
							$modAccess				=	'2'; // Registered
							break;
						default:
							$modAccess				=	'6'; // Manager
							break;
					}
				} else {
					$modAccess						=	'6'; // Manager
				}

				$ueConfig['allow_profileviewbyGID']	=	$profileAccess; // "Allow Access To:"
				$ueConfig['imageApproverGid']		=	$modAccess; // "Moderator Groups"
				$ueConfig['reg_email_checker']		=	'0'; // "Ajax Email checker"
				$ueConfig['reg_toc_url']			=	''; // "URL to Terms &amp; Conditions"
				$ueConfig['num_per_page']			=	'30'; // "Users Per Page"
				$ueConfig['allow_profilelink']		=	'1'; // "Allow Link to Profile"
				$ueConfig['nesttabs']				=	'0'; // "Nest Tabs"
				$ueConfig['xhtmlComply']			=	'1'; // "W3C XHTML 1.0 Trans. compliance"
				$ueConfig['im_path']				=	'auto'; // "Path to ImageMagick"
				$ueConfig['netpbm_path']			=	'auto'; // "Path to NetPBM"
				$ueConfig['allowAvatar']			=	'1'; // "Image"
				$ueConfig['allowAvatarUpload']		=	'1'; // "Allow Image Upload"
				$ueConfig['allowAvatarGallery']		=	'1'; // "Use Image Gallery"
			}

			$languageParams			=	array( 'debugMode' => $config->get( 'translations_debug', 0 ) );

			if ( $languageParams['debugMode'] == 0 ) {
				$languageParams['translationsLogger']	=	null;
			}
		} else {
			$languageParams			=	array();
		}

		// Set Language as singleton and instantiate it now that Config is read:
		Application::DI()->get( 'Language', $languageParams );
	}
}
