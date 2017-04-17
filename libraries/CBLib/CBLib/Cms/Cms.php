<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 09.06.13 01:23 $
* @package ${NAMESPACE}
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Cms;

use CBLib\Application\ApplicationContainerInterface;

defined('CBLIB') or die();

/**
 * CBLib\Cms Class implementation
 * 
 */
abstract class Cms
{
	/**
	 * Returns the Cms object corresponding to the CMS running.
	 *
	 * @throws \LogicException
	 *
	 * @return CmsInterface
	 */
	public static function getGetCmsFunction( ) {
		return function ( ApplicationContainerInterface $di )
			{
				if ( ! defined( 'JVERSION' ) ) {
					throw new \LogicException( 'Unknown CMS', 500 );
				}

				if ( version_compare( JVERSION, '3.0', 'ge' ) ) {
					return new Joomla\Joomla3( $di );
				}

				if ( version_compare( JVERSION, '2.5', 'ge' ) ) {
					return new Joomla\Joomla2( $di );
				}

				throw new \LogicException( 'Unsupported Joomla version', 500 );
			};
	}
}
