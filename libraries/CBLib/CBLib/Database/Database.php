<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 08.06.13 17:29 $
* @package CBLib\Database
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Database;

use CBLib\Cms\CmsInterface;
use CBLib\Database\Driver\CmsDatabaseDriver;
use CBLib\Database\Driver\MysqlDatabaseDriver;

defined('CBLIB') or die();

/**
 * CBLib\Database\Database Class implementation
 * 
 */
class Database {
	public static function createDatabaseDriver( CmsInterface $cms )
	{
		$cmsDatabaseDriver	=	$cms->getCmsDatabaseDriver();

		if ( $cmsDatabaseDriver )
		{
			$prefix			=	$cmsDatabaseDriver->getPrefix();
			$cmsRelease		=	$cms->getCmsVersion();

			return new CmsDatabaseDriver( $cmsDatabaseDriver, $prefix, $cmsRelease );
		}
		else
		{
			$connection		=	null;		//TODO LATER
			$prefix			=	$cms->getCfg( 'dbprefix' );

			return new MysqlDatabaseDriver( $connection, $prefix );
		}
	}
}
