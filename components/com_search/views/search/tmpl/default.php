<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<div class="<?php echo $this->params->get('pageclass_sfx'); ?>">
<?php if ($this->params->get('show_page_title', 1)) : ?>
<h2>
	<?php if ($this->escape($this->params->get('page_heading'))) :?>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	<?php else : ?>
		<?php echo $this->escape($this->params->get('page_title')); ?>
	<?php endif; ?>
</h2>
<?php endif; ?>

<?php echo $this->loadTemplate('form'); ?>
<?php if (!$this->error && count($this->results) > 0) :
	echo $this->loadTemplate('results');
else :
	echo $this->loadTemplate('error');
endif; ?>
</div>