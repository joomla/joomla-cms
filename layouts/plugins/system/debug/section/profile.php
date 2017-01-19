defined('_JEXEC') or die;

$marks     = $displayData['marks'];
$timings   = $displayData['timings'];
$htmlMarks = array();
$totalTime = 0;
$totalMem  = 0;
$marks     = array();

$format = JText::_('PLG_DEBUG_TIME') . ': <span class="label label-time">%.2f&nbsp;ms</span> / <span class="label label-default">%.2f&nbsp;ms</span>'
	. ' ' . JText::_('PLG_DEBUG_MEMORY') . ': <span class="label label-memory">%0.3f MB</span> / <span class="label label-default">%0.2f MB</span>'
	. ' %s: %s';

foreach ($displayData['marks'] as $m)
{
	$totalTime += $m->time;
	$totalMem  += $m->memory;
	$htmlMark   = sprintf($format, $m->time, $m->totalTime, $m->memory, $m->totalMemory, $m->prefix, $m->label);

	$marks[] = (object) array(
		'time'   => $m->time,
		'memory' => $m->memory,
		'html'   => $htmlMark,
		'tip'    => $m->label
	);
}

$avgTime = $totalTime / count($marks);
$avgMem  = $totalMem / count($marks);

foreach ($marks as $mark)
{
	if ($mark->time > $avgTime * 1.5)
	{
		$barClass   = 'bar-danger';
		$labelClass = 'label-important label-danger';
	}
	elseif ($mark->time < $avgTime / 1.5)
	{
		$barClass   = 'bar-success';
		$labelClass = 'label-success';
	}
	else
	{
		$barClass   = 'bar-warning';
		$labelClass = 'label-warning';
	}

	if ($mark->memory > $avgMem * 1.5)
	{
		$barClassMem   = 'bar-danger';
		$labelClassMem = 'label-important label-danger';
	}
	elseif ($mark->memory < $avgMem / 1.5)
	{
		$barClassMem   = 'bar-success';
		$labelClassMem = 'label-success';
	}
	else
	{
		$barClassMem   = 'bar-warning';
		$labelClassMem = 'label-warning';
	}

	$barClass    .= ' progress-' . $barClass;
	$barClassMem .= ' progress-' . $barClassMem;

	$bars[] = (object) array(
		'width' => round($mark->time / ($totalTime / 100), 4),
		'class' => $barClass,
		'tip'   => $mark->tip . ' ' . round($mark->time, 2) . ' ms'
	);

	$barsMem[] = (object) array(
		'width' => round($mark->memory / ($totalMem / 100), 4),
		'class' => $barClassMem,
		'tip'   => $mark->tip . ' ' . round($mark->memory, 3) . '  MB',
	);

	$htmlMarks[] = '<div>' . str_replace('label-time', $labelClass, str_replace('label-memory', $labelClassMem, $mark->html)) . '</div>';
}

if (!$timings)
{
	return;
}

$totalQueryTime = 0.0;
$lastStart      = null;

foreach ($timings as $k => $v)
{
	if (!($k % 2))
	{
		$lastStart = $v;
	}
	else
	{
		$totalQueryTime += $v - $lastStart;
	}
}

$totalQueryTime = $totalQueryTime * 1000;

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
?>

<h4><?php echo JText::_('PLG_DEBUG_TIME'); ?></h4>
<?php echo JLayoutHelper::render('plugins.system.debug.bars', array('bars' => $bars, 'class' => 'profile')); ?>
<h4><?php echo JText::_('PLG_DEBUG_MEMORY'); ?></h4>
<?php echo JLayoutHelper::render('plugins.system.debug.bars', array('bars' => $barsMem, 'class' => 'profile')); ?>
<div class="dbg-profile-list"><?php echo implode('', $htmlMarks); ?></div>
<br />
<div>
	<?php echo JText::sprintf('PLG_DEBUG_QUERIES_TIME', sprintf('<span class="label ' . $labelClass . '">%.2f&nbsp;ms</span>', $totalQueryTime)); ?>
</div>

