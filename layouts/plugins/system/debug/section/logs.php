<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$entries         = $displayData['entries'];
$deprecatedCount = $displayData['deprecatedCount'];
$logEntriesTotal = count($entries);

$priorities = array(
	JLog::EMERGENCY => '<span class="badge badge-important">EMERGENCY</span>',
	JLog::ALERT     => '<span class="badge badge-important">ALERT</span>',
	JLog::CRITICAL  => '<span class="badge badge-important">CRITICAL</span>',
	JLog::ERROR     => '<span class="badge badge-important">ERROR</span>',
	JLog::WARNING   => '<span class="badge badge-warning">WARNING</span>',
	JLog::NOTICE    => '<span class="badge badge-info">NOTICE</span>',
	JLog::INFO      => '<span class="badge badge-info">INFO</span>',
	JLog::DEBUG     => '<span class="badge">DEBUG</span>'
);

?>

<h4><?php echo JText::sprintf('PLG_DEBUG_LOGS_LOGGED', $logEntriesTotal); ?></h4>
<br />

<?php if ($deprecatedCount > 0) : ?>
	<div class="alert alert-warning">
		<h4><?php echo JText::sprintf('PLG_DEBUG_LOGS_DEPRECATED_FOUND_TITLE', $deprecatedCount); ?></h4>
		<div><?php echo JText::_('PLG_DEBUG_LOGS_DEPRECATED_FOUND_TEXT'); ?></div>
	</div>
	<br />
<?php endif; ?>

<ol>
	<?php foreach ($entries as $i => $entry) : ?>
		<li id="dbg_logs_<?php echo $i; ?>">
			<h5><?php echo $priorities[$entry->priority], ' ', $entry->category; ?></h5>
			<br />
			<pre><?php echo $entry->message; ?></pre>
			<?php if ($entry->callStack) : ?>
				<?php echo JHtml::_('bootstrap.startAccordion', 'dbg_logs_' . $i, array('active' => '')); ?>
				<?php echo JHtml::_('bootstrap.addSlide', 'dbg_logs_' . $i, JText::_('PLG_DEBUG_CALL_STACK'), 'dbg_logs_backtrace_' . $i); ?>
				<?php echo JLayoutHelper::render('plugins.system.debug.callstack', array('callStack' => $entry->callStack)); ?>
				<?php echo JHtml::_('bootstrap.endSlide'); ?>
				<?php echo JHtml::_('bootstrap.endAccordion'); ?>
			<?php endif; ?>
			<hr />
		</li>
	<?php endforeach; ?>
</ol>
