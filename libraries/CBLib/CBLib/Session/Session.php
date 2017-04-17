<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/7/14 11:05 AM $
* @package CBLib\Session
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Session;

use CBLib\Registry\ParametersStore;

defined('CBLIB') or die();

/**
 * CBLib\Session\Session Class implementation
 * 
 */
class Session extends ParametersStore implements SessionInterface
{
	/**
	 * Constructor
	 *
	 * @param  array  $paramsValues  Session values
	 */
	public function __construct( &$paramsValues = null )
	{
		if ( $paramsValues === null ) {
			global $_SESSION;
			$this->setAsReferenceToArray( $_SESSION );
			return;
		}

		$this->setAsReferenceToArray( $paramsValues );
	}
}
