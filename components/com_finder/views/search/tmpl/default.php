<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('stylesheet', 'com_finder/finder.css', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'vendor/awesomplete/awesomplete.css', array('version' => 'auto', 'relative' => true));

JText::script('MOD_FINDER_SEARCH_VALUE', true);

JHtml::_('script', 'com_finder/finder.js', array('version' => 'auto', 'relative' => true));
?>

<div class="finder<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<h1>
	<?php if ($this->escape($this->params->get('page_heading'))) : ?>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	<?php else : ?>
		<?php echo $this->escape($this->params->get('page_title')); ?>
	<?php endif; ?>
</h1>
<?php endif; ?>

<?php if ($this->params->get('show_search_form', 1)) : ?>
	<div id="search-form">
		<?php echo $this->loadTemplate('form'); ?>
	</div>
<?php endif;

// Load the search results layout if we are performing a search.
if ($this->query->search === true) :
?>
	<div id="search-results">
		<?php echo $this->loadTemplate('results'); ?>
	</div>
<?php endif; ?>
</div>
