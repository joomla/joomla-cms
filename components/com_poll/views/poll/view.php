<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Poll
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Poll component
 *
 * @static
 * @package		Joomla
 * @subpackage	Poll
 * @since 1.0
 */
class PollViewPoll extends JView
{
	function display($tpl = null)
	{
		$graphwidth = 200;
		$barheight 	= 4;
		$maxcolors 	= 5;
		$barcolor 	= 0;
		$tabcnt 	= 0;
		$colorx 	= 0;

		$maxval		= isset($this->votes[0]) ? $this->votes[0]->hits : 0;
		$sumval		= isset($this->votes[0]) ? $this->votes[0]->voters : 0;

		$k = 0;
		for ($i = 0; $i < count( $this->votes ); $i++)
		{
			$vote =& $this->votes[$i];

			if ($maxval > 0 && $sumval > 0)
			{
				$vote->width	= ceil( $vote->hits * $graphwidth / $maxval );
				$vote->percent = round( 100 * $vote->hits / $sumval, 1 );
			}
			else
			{
				$vote->width	= 0;
				$vote->percent	= 0;
			}

			$vote->class = '';
			if ($barcolor == 0)
			{
				if ($colorx < $maxcolors) {
					$colorx = ++$colorx;
				} else {
					$colorx = 1;
				}
				$vote->class = "polls_color_".$colorx;
			} else {
				$vote->class = "polls_color_".$barcolor;
			}

			$vote->barheight = $barheight;

			$vote->odd		= $k;
			$vote->count	= $i;
			$k = 1 - $k;
		}

		parent::display($tpl);
	}
}
?>
