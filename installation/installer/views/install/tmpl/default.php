<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');
	$steps	= $this->getSteps();
?>

<div id="stepbar">
	<div class="t">
		<div class="t">
			<div class="t"></div>
		</div>
	</div>

	<div class="m">
			<h1><?php echo JText::_('Steps') ?></h1>
			<div class="step-<?php echo $steps['lang'] ?>">
				1 : <?php echo JText::_('Language') ?>
			</div>
			<div class="step-<?php echo $steps['preinstall'] ?>">
				2 : <?php echo JText::_('Pre-Installation check') ?>
			</div>
			<div class="step-<?php echo $steps['license'] ?>">
				3 : <?php echo JText::_('License') ?>
			</div>
			<div class="step-<?php echo $steps['dbconfig'] ?>">
				4 : <?php echo JText::_('Database') ?>
			</div>
			<div class="step-<?php echo $steps['ftpconfig'] ?>">
				5 : <?php echo JText::_('FTP Configuration') ?>
			</div>
			<div class="step-<?php echo $steps['mainconfig'] ?>">
				6 : <?php echo JText::_('Configuration') ?>
			</div>
			<div class="step-<?php echo $steps['finish'] ?>">
				7 : <?php echo JText::_('Finish') ?>
			</div>
		<div class="box"></div>
  	</div>

	<div class="b">
		<div class="b">
			<div class="b"></div>
		</div>
	</div>

</div>

<?php

$tpl	= $this->get('subtemplate');
$output	= $this->loadTemplate($tpl);

if (! JError::isError($output))
{
	echo $output;
}

