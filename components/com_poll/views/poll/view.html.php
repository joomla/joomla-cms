<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Poll
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

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
		global $mainframe;

		$db 		=& JFactory::getDBO();
		$document	=& JFactory::getDocument();
		$pathway	=& $mainframe->getPathway();

		$poll_id = JRequest::getVar( 'id', 0, '', 'int' );

		$poll =& JTable::getInstance('poll', 'Table');
		$poll->load( $poll_id );

		// if id value is passed and poll not published then exit
		if ($poll->id > 0 && $poll->published != 1) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		// Adds parameter handling
		$params = $mainframe->getParams();

		//Set page title information
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	$poll->title);
			}
		} else {
			$params->set('page_title',	$poll->title);
		}
		$document->setTitle( $params->get( 'page_title' ) );

		//Set pathway information
		$pathway->addItem($poll->title, '');

		$params->def( 'show_page_title', 1 );
		$params->def( 'page_title', $poll->title );

		$first_vote = '';
		$last_vote 	= '';
		$votes		= '';

		// Check if there is a poll corresponding to id and if poll is published
		if ($poll->id > 0)
		{
			if (empty( $poll->title )) {
				$poll->id = 0;
				$poll->title = JText::_( 'Select Poll from the list' );
			}

			$query = 'SELECT MIN( date ) AS mindate, MAX( date ) AS maxdate'
				. ' FROM #__poll_date'
				. ' WHERE poll_id = '. (int) $poll->id;
			$db->setQuery( $query );
			$dates = $db->loadObject();

			if (isset( $dates->mindate )) {
				$first_vote = JHTML::_('date',  $dates->mindate, JText::_('DATE_FORMAT_LC2') );
				$last_vote 	= JHTML::_('date',  $dates->maxdate, JText::_('DATE_FORMAT_LC2') );
			}

			$query = 'SELECT a.id, a.text, a.hits, b.voters '
				. ' FROM #__poll_data AS a'
				. ' INNER JOIN #__polls AS b ON b.id = a.pollid'
				. ' WHERE a.pollid = '. (int) $poll->id
				. ' AND a.text <> ""'
				. ' ORDER BY a.hits DESC';
			$db->setQuery( $query );
			$votes = $db->loadObjectList();
		} else {
			$votes = array();
		}

		// list of polls for dropdown selection
		$query = 'SELECT id, title, alias'
			. ' FROM #__polls'
			. ' WHERE published = 1'
			. ' ORDER BY id'
		;
		$db->setQuery( $query );
		$pList = $db->loadObjectList();

		foreach ($pList as $k=>$p)
		{
			$pList[$k]->url = JRoute::_('index.php?option=com_poll&id='.$p->id.':'.$p->alias);
		}

		array_unshift( $pList, JHTML::_('select.option',  '', JText::_( 'Select Poll from the list' ), 'url', 'title' ));

		// dropdown output
		$lists = array();

		$lists['polls'] = JHTML::_('select.genericlist',   $pList, 'id',
			'class="inputbox" size="1" style="width:200px" onchange="if (this.options[selectedIndex].value != \'\') {document.location.href=this.options[selectedIndex].value}"',
 			'url', 'title',
 			JRoute::_('index.php?option=com_poll&id='.$poll->id.':'.$poll->alias)
 			);


		$graphwidth = 200;
		$barheight 	= 4;
		$maxcolors 	= 5;
		$barcolor 	= 0;
		$tabcnt 	= 0;
		$colorx 	= 0;

		$maxval		= isset($votes[0]) ? $votes[0]->hits : 0;
		$sumval		= isset($votes[0]) ? $votes[0]->voters : 0;

		$k = 0;
		for ($i = 0; $i < count( $votes ); $i++)
		{
			$vote =& $votes[$i];

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

		$this->assign('first_vote',	$first_vote);
		$this->assign('last_vote',	$last_vote);

		$this->assignRef('lists',	$lists);
		$this->assignRef('params',	$params);
		$this->assignRef('poll',	$poll);
		$this->assignRef('votes',	$votes);

		parent::display($tpl);
	}
}
?>
