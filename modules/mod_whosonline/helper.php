<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modWhosonlineHelper
{
	function render(&$params)
	{
		$db		=& JFactory::getDBO();

		$showmode = $params->get('showmode', 0);

		$output = '';

		// show online count
		if ($showmode == 0 || $showmode == 2)
		{
			$query = "SELECT guest, usertype, client_id" .
					"\n FROM #__session" .
					"\n WHERE client_id = 0";
			$db->setQuery($query);
			$sessions = $db->loadObjectList();

			// calculate number of guests and members
			$user_array = 0;
			$guest_array = 0;
			foreach ($sessions as $session)
			{
				// if guest increase guest count by 1
				if ($session->guest == 1 && !$session->usertype)
				{
					$guest_array ++;
				}
				// if member increase member count by 1
				if ($session->guest == 0)
				{
					$user_array ++;
				}
			}

			// check if any guest or member is on the site
			if ($guest_array != 0 || $user_array != 0)
			{
				$output .= JText::_('We have');
				$output .= '&nbsp;';

				// guest count handling
				if ($guest_array == 1)
				{
					// 1 guest only
					$output .= JText::sprintf('guest', $guest_array);
				}
				else
					if ($guest_array > 1)
					{
						// more than 1 guest
						$output .= JText::sprintf('guests', $guest_array);
					}

				// if there are guests and members online
				if ($guest_array != 0 && $user_array != 0)
				{
					$output .= '&nbsp;';
					$output .= JText::_('and');
					$output .= '&nbsp;';
				}

				// member count handling
				if ($user_array == 1)
				{
					// 1 member only
					$output .= JText::sprintf('member', $user_array);
				}
				else
					if ($user_array > 1)
					{
						// more than 1 member
						$output .= JText::sprintf('members', $user_array);
					}

				$output .= '&nbsp;';
				$output .= JText::_('online');
			}
		}

		// show online member names
		if ($showmode > 0)
		{
			$query = "SELECT DISTINCT a.username" .
					"\n FROM #__session AS a" .
					"\n WHERE a.guest = 0";
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if (count($rows))
			{
				// output
				$output .= '<ul>';
				foreach ($rows as $row)
				{
					$output .= '<li>';
					$output .= '<strong>';
					$output .= $row->username;
					$output .= '</strong>';
					$output .= '</li>';
				}
				$output .= '</ul>';
			}
		}

		echo $output;
	}
}
