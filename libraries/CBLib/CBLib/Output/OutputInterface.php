<?php
/**
 * CBLib, Community Builder Library(TM)
 *
 * @version       $Id: 7/5/14 7:32 PM $
 * @package       CBLib\Output
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

namespace CBLib\Output;

/**
 * CBLib\AhaWow\Output Class implementation
 *
 */
interface OutputInterface
{
	/**
	 * Appends output to this output object
	 *
	 * @param  string  $output
	 * @return void
	 */
	public function append( $output );
}
