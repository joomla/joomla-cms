<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:13 AM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\Driver\CmsDatabaseDriver;

defined('CBLIB') or die();

/**
 * CBdatabase Class implementation
 * Here for potential backwards compatibility reason only
 * @deprecated 2.0 use CBLib\Database\DatabaseDriverInterface through DI instead
 * @see \CBLib\Database\DatabaseDriverInterface
 */
class CBdatabase extends CmsDatabaseDriver
{
	/**
	 * Database object constructor
	 *
	 * @param  object|\JDatabase|\JDatabaseDriver  $cmsDatabase
	 */
	public function __construct( $cmsDatabase )
	{
		global $_CB_framework;

		parent::__construct( $cmsDatabase, $_CB_framework->getCfg( 'dbprefix' ), checkJversion( 'release' ) );
	}
}
