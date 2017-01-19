<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('JDebugHelper', JPATH_PLUGINS . '/system/debug/helper.php');

$name               = $displayData['name'];
$log                = $displayData['log'];
$timings            = $displayData['timings'];
$callStacks         = $displayData['callStacks'];
$totalQueries       = $displayData['totalQueries'];
$sqlShowProfiles    = $displayData['sqlShowProfiles'];
$sqlShowProfileEach = $displayData['sqlShowProfileEach'];
$explains           = $displayData['explains'];
$queryTypes         = $displayData['queryTypes'];

if (!$log)
{
	return;
}

$selectQueryTypeTicker = array();
$otherQueryTypeTicker  = array();

$timing  = array();
$maxtime = 0;

if (isset($timings[0]))
{
	$startTime = $timings[0];
	$endTime   = $timings[count($timings) - 1];
	$totalBargraphTime = $endTime - $startTime;

	if ($totalBargraphTime > 0)
	{
		foreach ($log as $id => $query)
		{
			if (isset($timings[$id * 2 + 1]))
			{
				// Compute the query time: $timing[$k] = array( queryTime, timeBetweenQueries ).
				$timing[$id] = array(($timings[$id * 2 + 1] - $timings[$id * 2]) * 1000, $id > 0 ? ($timings[$id * 2] - $timings[$id * 2 - 1]) * 1000 : 0);
				$maxtime = max($maxtime, $timing[$id]['0']);
			}
		}
	}
}
else
{
	$startTime = null;
	$totalBargraphTime = 1;
}

$bars           = array();
$info           = array();
$totalQueryTime = 0;
$duplicates     = array();

foreach ($log as $id => $query)
{
	$did = md5($query);

	if (!isset($duplicates[$did]))
	{
		$duplicates[$did] = array();
	}

	$duplicates[$did][] = $id;

	if ($timings && isset($timings[$id * 2 + 1]))
	{
		// Compute the query time.
		$queryTime = ($timings[$id * 2 + 1] - $timings[$id * 2]) * 1000;
		$totalQueryTime += $queryTime;

		// Run an EXPLAIN EXTENDED query on the SQL query if possible.
		$hasWarnings = false;
		$hasWarningsInProfile = false;

		if (isset($explains[$id]))
		{
			$data = array('table' => $explains[$id], 'hasWarnings' => &$hasWarnings);
			$explain = JLayoutHelper::render('plugins.system.debug.tabletohtml', $data);
		}
		else
		{
			$explain = JText::sprintf('PLG_DEBUG_QUERY_EXPLAIN_NOT_POSSIBLE', htmlspecialchars($query));
		}

		// Run a SHOW PROFILE query.
		$profile = '';

		if (in_array($name, array('mysqli', 'mysql', 'pdomysql')))
		{
			if (isset($sqlShowProfileEach[$id]))
			{
				$data = array('table' => $sqlShowProfileEach[$id], 'hasWarnings' => &$hasWarningsInProfile);
				$profile = JLayoutHelper::render('plugins.system.debug.tabletohtml', $data);
			}
		}

		// How heavy should the string length count: 0 - 1.
		$ratio = 0.5;
		$timeScore = $queryTime / ((strlen($query) + 1) * $ratio) * 200;

		// Determine color of bargraph depending on query speed and presence of warnings in EXPLAIN.
		if ($timeScore > 10)
		{
			$barClass = 'bar-danger';
			$labelClass = 'label-important';
		}
		elseif ($hasWarnings || $timeScore > 5)
		{
			$barClass = 'bar-warning';
			$labelClass = 'label-warning';
		}
		else
		{
			$barClass = 'bar-success';
			$labelClass = 'label-success';
		}

		// Computes bargraph as follows: Position begin and end of the bar relatively to whole execution time.
		$prevBar = ($id && isset($bars[$id - 1])) ? $bars[$id - 1] : 0;

		$barPre = round($timing[$id][1] / ($totalBargraphTime * 10), 4);
		$barWidth = round($timing[$id][0] / ($totalBargraphTime * 10), 4);
		$minWidth = 0.3;

		if ($barWidth < $minWidth)
		{
			$barPre -= ($minWidth - $barWidth);

			if ($barPre < 0)
			{
				$minWidth += $barPre;
				$barPre = 0;
			}

			$barWidth = $minWidth;
		}

		$bars[$id] = (object) array(
			'class' => $barClass,
			'width' => $barWidth,
			'pre' => $barPre,
			'tip' => sprintf('%.2f&nbsp;ms', $queryTime)
		);
		$info[$id] = (object) array(
			'class' => $labelClass,
			'explain' => $explain,
			'profile' => $profile,
			'hasWarnings' => $hasWarnings
		);
	}
}

// Remove single queries from $duplicates.
$total_duplicates = 0;

foreach ($duplicates as $did => $dups)
{
	if (count($dups) < 2)
	{
		unset($duplicates[$did]);
	}
	else
	{
		$total_duplicates += count($dups);
	}
}

// Fix first bar width.
$minWidth = 0.3;

if ($bars[0]->width < $minWidth && isset($bars[1]))
{
	$bars[1]->pre -= ($minWidth - $bars[0]->width);

	if ($bars[1]->pre < 0)
	{
		$minWidth += $bars[1]->pre;
		$bars[1]->pre = 0;
	}

	$bars[0]->width = $minWidth;
}

$memoryUsageNow = memory_get_usage();
$list = array();

foreach ($log as $id => $query)
{
	// Start query type ticker additions.
	$fromStart = stripos($query, 'from');
	$whereStart = stripos($query, 'where', $fromStart);

	if ($whereStart === false)
	{
		$whereStart = stripos($query, 'order by', $fromStart);
	}

	if ($whereStart === false)
	{
		$whereStart = strlen($query) - 1;
	}

	$fromString = substr($query, 0, $whereStart);
	$fromString = str_replace("\t", " ", $fromString);
	$fromString = str_replace("\n", " ", $fromString);
	$fromString = trim($fromString);

	// Initialise the select/other query type counts the first time.
	if (!isset($selectQueryTypeTicker[$fromString]))
	{
		$selectQueryTypeTicker[$fromString] = 0;
	}

	if (!isset($otherQueryTypeTicker[$fromString]))
	{
		$otherQueryTypeTicker[$fromString] = 0;
	}

	// Increment the count.
	if (stripos($query, 'select') === 0)
	{
		$selectQueryTypeTicker[$fromString] = $selectQueryTypeTicker[$fromString] + 1;
		unset($otherQueryTypeTicker[$fromString]);
	}
	else
	{
		$otherQueryTypeTicker[$fromString] = $otherQueryTypeTicker[$fromString] + 1;
		unset($selectQueryTypeTicker[$fromString]);
	}

	$text = JDebugHelper::highlightQuery($query);

	if ($timings && isset($timings[$id * 2 + 1]))
	{
		// Compute the query time.
		$queryTime = ($timings[$id * 2 + 1] - $timings[$id * 2]) * 1000;

		// Timing
		// Formats the output for the query time with EXPLAIN query results as tooltip:
		$htmlTiming = '<div style="margin: 0 0 5px;"><span class="dbg-query-time">';
		$htmlTiming .= JText::sprintf(
				'PLG_DEBUG_QUERY_TIME',
				sprintf(
					'<span class="label %s">%.2f&nbsp;ms</span>',
					$info[$id]->class,
					$timing[$id]['0']
				)
			);

		if ($timing[$id]['1'])
		{
			$htmlTiming .= ' ' . JText::sprintf('PLG_DEBUG_QUERY_AFTER_LAST',
					sprintf('<span class="label label-default">%.2f&nbsp;ms</span>', $timing[$id]['1'])
				);
		}

		$htmlTiming .= '</span>';

		if (isset($callStacks[$id][0]['memory']))
		{
			$memoryUsed = $callStacks[$id][0]['memory'][1] - $callStacks[$id][0]['memory'][0];
			$memoryBeforeQuery = $callStacks[$id][0]['memory'][0];

			// Determine colour of query memory usage.
			if ($memoryUsed > 0.1 * $memoryUsageNow)
			{
				$labelClass = 'label-important';
			}
			elseif ($memoryUsed > 0.05 * $memoryUsageNow)
			{
				$labelClass = 'label-warning';
			}
			else
			{
				$labelClass = 'label-success';
			}

			$htmlTiming .= ' ' . '<span class="dbg-query-memory">' . JText::sprintf('PLG_DEBUG_MEMORY_USED_FOR_QUERY',
					sprintf('<span class="label ' . $labelClass . '">%.3f&nbsp;MB</span>', $memoryUsed / 1048576),
					sprintf('<span class="label label-default">%.3f&nbsp;MB</span>', $memoryBeforeQuery / 1048576)
				)
				. '</span>';

			if ($callStacks[$id][0]['memory'][2] !== null)
			{
				// Determine colour of number or results.
				$resultsReturned = $callStacks[$id][0]['memory'][2];

				if ($resultsReturned > 3000)
				{
					$labelClass = 'label-important';
				}
				elseif ($resultsReturned > 1000)
				{
					$labelClass = 'label-warning';
				}
				elseif ($resultsReturned == 0)
				{
					$labelClass = '';
				}
				else
				{
					$labelClass = 'label-success';
				}

				$htmlResultsReturned = '<span class="label ' . $labelClass . '">' . (int) $resultsReturned . '</span>';
				$htmlTiming .= ' <span class="dbg-query-rowsnumber">' . JText::sprintf('PLG_DEBUG_ROWS_RETURNED_BY_QUERY', $htmlResultsReturned) . '</span>';
			}
		}

		$htmlTiming .= '</div>';

		// Bar.
		$htmlBar = JLayoutHelper::render('plugins.system.debug.bars', array('bars' => $bars, 'class' => 'query', '$id' => $id));

		// Profile query.
		$title = JText::_('PLG_DEBUG_PROFILE');

		if (!$info[$id]->profile)
		{
			$title = '<span class="dbg-noprofile">' . $title . '</span>';
		}

		$htmlProfile = ($info[$id]->profile ? $info[$id]->profile : JText::_('PLG_DEBUG_NO_PROFILE'));

		$htmlAccordions = JHtml::_(
			'bootstrap.startAccordion', 'dbg_query_' . $id, array(
				'active' => ($info[$id]->hasWarnings ? ('dbg_query_explain_' . $id) : '')
			)
		);

		$htmlAccordions .= JHtml::_('bootstrap.addSlide', 'dbg_query_' . $id, JText::_('PLG_DEBUG_EXPLAIN'), 'dbg_query_explain_' . $id)
			. $info[$id]->explain
			. JHtml::_('bootstrap.endSlide');

		$htmlAccordions .= JHtml::_('bootstrap.addSlide', 'dbg_query_' . $id, $title, 'dbg_query_profile_' . $id)
			. $htmlProfile
			. JHtml::_('bootstrap.endSlide');

		// Call stack and back trace.
		if (isset($callStacks[$id]))
		{
			$htmlAccordions .= JHtml::_('bootstrap.addSlide', 'dbg_query_' . $id, JText::_('PLG_DEBUG_CALL_STACK'), 'dbg_query_callstack_' . $id)
				. JLayoutHelper::render('plugins.system.debug.callstack', array('callStack' => $callStacks[$id]))
				. JHtml::_('bootstrap.endSlide');
		}

		$htmlAccordions .= JHtml::_('bootstrap.endAccordion');

		$did = md5($query);

		if (isset($duplicates[$did]))
		{
			$dups = array();

			foreach ($duplicates[$did] as $dup)
			{
				if ($dup != $id)
				{
					$dups[] = '<a class="alert-link" href="#dbg-query-' . ($dup + 1) . '">#' . ($dup + 1) . '</a>';
				}
			}

			$htmlQuery = '<div class="alert alert-error">' . JText::_('PLG_DEBUG_QUERY_DUPLICATES') . ': ' . implode('&nbsp; ', $dups) . '</div>'
				. '<pre class="alert hasTooltip" title="' . JHtml::tooltipText('PLG_DEBUG_QUERY_DUPLICATES_FOUND') . '">' . $text . '</pre>';
		}
		else
		{
			$htmlQuery = '<pre>' . $text . '</pre>';
		}

		$list[] = '<a name="dbg-query-' . ($id + 1) . '"></a>'
			. $htmlTiming
			. $htmlBar
			. $htmlQuery
			. $htmlAccordions;
	}
	else
	{
		$list[] = '<pre>' . $text . '</pre>';
	}
}

$totalTime = 0;

foreach (JProfiler::getInstance('Application')->getMarks() as $mark)
{
	$totalTime += $mark->time;
}

if ($totalQueryTime > ($totalTime * 0.25))
{
	$labelClass = 'label-important';
}
elseif ($totalQueryTime < ($totalTime * 0.15))
{
	$labelClass = 'label-success';
}
else
{
	$labelClass = 'label-warning';
}

$html = array();

$html[] = '<h4>' . JText::sprintf('PLG_DEBUG_QUERIES_LOGGED', $totalQueries)
	. sprintf(' <span class="label ' . $labelClass . '">%.2f&nbsp;ms</span>', ($totalQueryTime)) . '</h4><br />';

if ($total_duplicates)
{
	$html[] = '<div class="alert alert-error">'
		. '<h4>' . JText::sprintf('PLG_DEBUG_QUERY_DUPLICATES_TOTAL_NUMBER', $total_duplicates) . '</h4>';

	foreach ($duplicates as $dups)
	{
		$links = array();

		foreach ($dups as $dup)
		{
			$links[] = '<a class="alert-link" href="#dbg-query-' . ($dup + 1) . '">#' . ($dup + 1) . '</a>';
		}

		$html[] = '<div>' . JText::sprintf('PLG_DEBUG_QUERY_DUPLICATES_NUMBER', count($links)) . ': ' . implode('&nbsp; ', $links) . '</div>';
	}

	$html[] = '</div>';
}

$html[] = '<ol><li>' . implode('<hr /></li><li>', $list) . '<hr /></li></ol>';

if (!$queryTypes)
{
	echo implode('', $html);

	return;
}

// Get the totals for the query types.
$totalSelectQueryTypes = count($selectQueryTypeTicker);
$totalOtherQueryTypes = count($otherQueryTypeTicker);
$totalQueryTypes = $totalSelectQueryTypes + $totalOtherQueryTypes;

$html[] = '<h4>' . JText::sprintf('PLG_DEBUG_QUERY_TYPES_LOGGED', $totalQueryTypes) . '</h4>';

if ($totalSelectQueryTypes)
{
	$html[] = '<h5>' . JText::_('PLG_DEBUG_SELECT_QUERIES') . '</h5>';

	arsort($selectQueryTypeTicker);

	$list = array();

	foreach ($selectQueryTypeTicker as $query => $occurrences)
	{
		$list[] = '<pre>'
			. JText::sprintf('PLG_DEBUG_QUERY_TYPE_AND_OCCURRENCES', JDebugHelper::highlightQuery($query), $occurrences)
			. '</pre>';
	}

	$html[] = '<ol><li>' . implode('</li><li>', $list) . '</li></ol>';
}

if ($totalOtherQueryTypes)
{
	$html[] = '<h5>' . JText::_('PLG_DEBUG_OTHER_QUERIES') . '</h5>';

	arsort($otherQueryTypeTicker);

	$list = array();

	foreach ($otherQueryTypeTicker as $query => $occurrences)
	{
		$list[] = '<pre>'
			. JText::sprintf('PLG_DEBUG_QUERY_TYPE_AND_OCCURRENCES', JDebugHelper::highlightQuery($query), $occurrences)
			. '</pre>';
	}

	$html[] = '<ol><li>' . implode('</li><li>', $list) . '</li></ol>';
}

echo implode('', $html);
