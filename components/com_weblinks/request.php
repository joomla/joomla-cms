<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

function WeblinksBuildURL(&$ARRAY, &$params)
{
	$resolveNames = $params->get('realnames',0);

	// TODO: Resolve category names
	$parts = array();
	if(isset($ARRAY['view'])) {
		$parts[] = $ARRAY['view'];
	};

	if(isset($ARRAY['layout'])) {
		$parts[] = $ARRAY['layout'];
	};

	if(isset($ARRAY['id'])) {
		$parts[] = $ARRAY['id'];
	};

	if (isset( $ARRAY['limit'] ))
	{
		// Do all pages if limit = 0
		if ($ARRAY['limit'] == 0) {
			$parts[] = 'all';
		} else {
			$limit		= (int) $ARRAY['limit'];
			$limitstart	= (int) @$ARRAY['limitstart'];
			$page		= floor( $limitstart / $limit ) + 1;
			$parts[]	= 'page'.$page.':'.$limit;
		}
	}
	
	//unset the whole array
	$ARRAY = array();
	
	return $parts;
}

function WeblinksParseURL($ARRAY, &$params)
{
	// view is always the first element of the array
	$view	= array_shift($ARRAY);
	$nArray	= count($ARRAY);

	JRequest::setVar('view', $view, 'get');

	switch ($view)
	{
		case 'categories':
		{
			if (count($ARRAY))
			{
 				$variable = array_shift($ARRAY);
				JRequest::setVar('layout', $variable, 'get');
			}
		} break;

		case 'category':
		{
			if (count($ARRAY))
			{
 				$variable = array_shift($ARRAY);

				if(is_numeric($variable))
				{
					JRequest::setVar('id', $variable, 'get');
				}
				else
				{
					JRequest::setVar('layout', $variable, 'get');
					$variable = array_shift($ARRAY);
					JRequest::setVar('id', $variable, 'get');
				}
			}

		} break;

		case 'weblink':
		{
			if (count($ARRAY))
			{
 				$variable = array_shift($ARRAY);

				if(is_numeric($variable))
				{
					JRequest::setVar('id', $variable, 'get');
				}
				else
				{
					JRequest::setVar('layout', $variable, 'get');
					$variable = array_shift($ARRAY);
					JRequest::setVar('id', $variable, 'get');
				}
			}

			// Handle Pagination
			$last = @$ARRAY[$nArray-1];
			if ($last == 'all')
			{
				array_pop( $ARRAY );
				$nArray--;
				JRequest::setVar('limitstart', 0, 'get');
				JRequest::setVar('limit', 0, 'get');
				// if you want more than 1e6 on your page then you are nuts!
			}
			elseif (strpos( $last, 'page' ) === 0)
			{
				array_pop( $ARRAY );
				$nArray--;
				$pts		= explode( ':', $last );
				$limit		= @$pts[1];
				$limitstart	= (max( 1, intval( str_replace( 'page', '', $pts[0] ) ) ) - 1)  * $limit;
				JRequest::setVar('limit',$limit, 'get');
				JRequest::setVar('limitstart', $limitstart, 'get');
			}
		}
		break;

		default: break;
	}
}
?>