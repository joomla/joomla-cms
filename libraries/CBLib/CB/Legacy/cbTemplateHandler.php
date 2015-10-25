<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 3:02 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


defined('CBLIB') or die();

/**
 * cbTemplateHandler Class implementation
 * Templates and Views Handler class
 */
abstract class cbTemplateHandler extends cbPluginHandler
{
	/**
	 * Draws the layout part for $part
	 *
	 * @param  string  $part  Layout part to render
	 * @return string         HTML output
	 */
	public function draw( $part = '' )
	{
		$method		=	'_render' . $part;

		ob_start();

		$this->$method();

		$ret = ob_get_contents();
		ob_end_clean();

		return $ret;
	}
}
