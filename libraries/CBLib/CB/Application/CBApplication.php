<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/30/14 11:49 PM $
* @package CB\Application
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Application;

use CBLib\Application\ApplicationContainerInterface;
use CBLib\Core\AutoLoader;
use CBLib\Core\CBLib;
use CB\Legacy\LegacyLoader;

include_once dirname( dirname( __DIR__ ) ) . '/CBLib/Core/CBLib.php';

defined('CBLIB') or die();

/**
 * CB\Application\CBApplication Class implementation
 * 
 */
class CBApplication
{
	/**
	 * Initialization function
	 *
	 * @return ApplicationContainerInterface
	 */
	public static function init( )
	{
		AutoLoader::setup();
		new LegacyLoader();

		return CBLib::createApplication(
			'Web',
			/* This parameter is the Input parameter, and we use the query as input: */
			null,
			/* This parameter is the Application Config parameter, where we pass the CB Config loader: */
			function ( ApplicationContainerInterface $app )
			{
				return CBConfig::getConfig( $app->getDatabase() );
			}
		);

		// done in plugin.foundation.php for now:
		// Application::DI->get( 'LegacyFoundationFunctions' );
	}
}
