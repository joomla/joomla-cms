<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 7/7/14 2:25 PM $
* @package CBLib\Legacy
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Legacy;

use CBLib\Core\AutoLoader;

defined('CBLIB') or die();

/**
 * CBLib\Legacy\LegacyLoader Class implementation
 */
class LegacyLoader
{
	protected static $autoloaded	=	false;

	/**
	 * Constructor
	 */
	public function __construct( )
	{
		if ( self::$autoloaded ) {
			return;
		}

		self::$autoloaded			=	true;

		AutoLoader::registerMap( '/^((?:cb|(?:mos)?comprofiler)[^\\\\]+|imgToolBox|PclZip|Archive_Tar|PEAR|TOOLBAR_usersextras)$/i', 'CB/Legacy/$1.php' );
		// Fix for CB 1.9 plugins dblookupfield and autowelcome:
		AutoLoader::registerClass( 'CBUser', 'CB/Legacy/CBuser.php' );
		// Fix for old CB modules:
		AutoLoader::registerClass( 'cbUser', 'CB/Legacy/CBuser.php' );

		// We alias these classes for faster loading and also so that instanceof is true:
		class_alias( '\CB\Database\Table\ComprofilerTable',	'moscomprofiler' );
		class_alias( '\CB\Database\Table\FieldTable',		'moscomprofilerFields' );
		class_alias( '\CB\Database\Table\FieldValueTable',	'moscomprofilerFieldValues' );
		class_alias( '\CB\Database\Table\ListTable',		'moscomprofilerLists' );
		class_alias( '\CB\Database\Table\MemberTable',		'moscomprofilerMember' );
		class_alias( '\CB\Database\Table\PluginTable',		'moscomprofilerPlugin' );
		class_alias( '\CB\Database\Table\TabTable',			'moscomprofilerTabs' );
		class_alias( '\CB\Database\Table\UserTable',		'moscomprofilerUser' );
		class_alias( '\CB\Database\Table\UserReportTable',	'moscomprofilerUserReport' );
	}
}
