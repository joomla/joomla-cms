<?php
/**
 * @version		$Id: admin.modules.php 7609 2007-06-03 19:53:25Z jinx $
 * @package		Joomla
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * @package		Joomla
 * @subpackage	Modules
 */
class ModulesHelperXML
{
	function parseXMLModuleFile( &$rows  )
	{
		foreach ($rows as $i => $row)
		{
			if ($row->module == '')
			{
				$rows[$i]->name 	= 'custom';
				$rows[$i]->module 	= 'custom';
				$rows[$i]->descrip 	= 'Custom created module, using Module Manager `New` function';
			}
			else
			{
				$data = JApplicationHelper::parseXMLInstallFile( $row->path.DS.$row->file);

				if ( $data['type'] == 'module' )
				{
					$rows[$i]->name		= $data['name'];
					$rows[$i]->descrip	= $data['description'];
				}
			}
		}
	}
}