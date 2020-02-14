<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

?>
<div class="search">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h1 class="page-title">
			<?php if ($this->escape($this->params->get('page_heading'))) : ?>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			<?php else : ?>
				<?php echo $this->escape($this->params->get('page_title')); ?>
			<?php endif; ?>
		</h1>
	<?php endif; ?>
	<?php echo $this->loadTemplate('form'); ?>
	<?php if ($this->error == null && count($this->results) > 0) : ?>
		<?php echo $this->loadTemplate('results'); ?>
	<?php else : ?>
		<?php echo $this->loadTemplate('error'); ?>
	<?php endif; ?>
</div>
