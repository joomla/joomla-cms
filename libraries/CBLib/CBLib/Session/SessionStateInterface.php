<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 8/24/14 11:37 PM $
* @package CBLib\Session
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Session;

use CBLib\Registry\ParamsInterface;

defined('CBLIB') or die();

interface SessionStateInterface extends ParamsInterface
{
	/*
	 * Sets the domain for $this SessionState
	 *
	 * @param  string  $domain  (can be hierarchical, separated with '.')
	 * @return void
	 */
	public function stateIsForDomain( $domain );
}
