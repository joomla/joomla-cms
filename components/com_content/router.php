<?php
/**
* @version		$Id: sef.php 5747 2006-11-12 21:49:30Z louis $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

function ContentBuildRoute(&$ARRAY)
{
	$resolveNames = 0;

	// TODO: Resolve category names
	$parts = array();
	if(isset($ARRAY['view'])) {
		$parts[] = $ARRAY['view'];
	};

	if(isset($ARRAY['layout'])) {
		$parts[] = $ARRAY['layout'];
	};

	if(isset($ARRAY['catid'])) {
		$parts[] = $ARRAY['catid'];
	};

	if(isset($ARRAY['id'])) {
		if(!$resolveNames) {
			$parts[] = (int)$ARRAY['id'];
		} else {
			$parts[] = $ARRAY['id'];
		}
	};

	if(isset($ARRAY['year'])) {
		$parts[] = $ARRAY['year'];
	};

	if(isset($ARRAY['month'])) {
		$parts[] = $ARRAY['month'];
	};

	//unset the whole array
	$ARRAY = array();

	return $parts;
}

function ContentParseRoute($ARRAY)
{
	// view is always the first element of the array
	$view = array_shift($ARRAY);
	JRequest::setVar('view', $view, 'get');

	$next = array_shift($ARRAY);

	switch($view)
	{
		case 'article'  :
		case 'category' :
		case 'section'  :
		{
			if(is_numeric((int)$next) && ((int)$next != 0)) {
				JRequest::setVar('id', (int)$next, 'get');
			}
			else
			{
				JRequest::setVar('layout', $next, 'get');
				JRequest::setVar('id', (int)array_shift($ARRAY), 'get');
			}
		} break;

		case 'archive'   :
		{
			if(is_numeric((int)$next) && ((int)$next != 0)) {
				JRequest::setVar('year', $next, 'get');
				JRequest::setVar('month', array_shift($ARRAY), 'get');
			}
			else
			{
				JRequest::setVar('layout', $next, 'get');
				JRequest::setVar('year', array_shift($ARRAY), 'get');
				JRequest::setVar('month', array_shift($ARRAY), 'get');
			}
		} break;
	}
}
?>