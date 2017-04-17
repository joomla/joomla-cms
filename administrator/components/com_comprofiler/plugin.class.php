<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/** @noinspection PhpIncludeInspection */
include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';

cbimport( 'cb.database' );

/**
 * class cbPluginHandler, class cbFieldHandler, class cbSqlQueryPart, class cbFieldParamsHandler, class cbTabParamsHandler,
 * class cbTabHandler, class cbTemplateHandler, class cbProfileView, class cbRegistrationView, class cbListView and class cbPMSHandler
 * are now in libraries/CBLib/CB/Legacy folder
 */


global $_PLUGINS;
/** @global cbPluginHandler $_PLUGINS */
$_PLUGINS = new cbPluginHandler();
