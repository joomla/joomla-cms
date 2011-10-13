<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Get the mime type class.
$mime = !empty($this->result->mime) ? 'mime-'.$this->result->mime : null;

// Get the base url.
$base = JURI::getInstance()->toString(array('scheme', 'host', 'port'));

// Get the route with highlighting information.
if (!empty($this->query->highlight) && empty($this->result->mime) && $this->params->get('highlight_terms', 1) && JPluginHelper::isEnabled('content', 'highlight')) {
	$route = $this->result->route.'&highlight='.base64_encode(serialize($this->query->highlight));
} else {
	$route = $this->result->route;
}
?>

<h2 class="title <?php echo $mime; ?>">
	<a href="<?php echo JRoute::_($route); ?>"><?php echo $this->result->title; ?></a>
</h2>

<?php
// Show the start date if set.
if (intval($this->result->start_date) && $this->params->get('show_date_filters', 0)):
?>
	<span class="start-date">
		<?php echo JHtml::date($this->result->start_date, $this->params->get('date_format', 'd-M-Y')); ?>
	</span>
<?php
endif;

// Show the summary.
if ($this->params->get('show_description', 1)):
?>
	<div class="description">
		<?php echo JHtml::_('string.truncate', $this->result->description, $this->params->get('description_length', 255)); ?>
	</div>
<?php
endif;

// Show the URL.
if ($this->params->get('show_url', 1)):
?>
	<p class="url">
		<?php echo $base.JRoute::_($this->result->route); ?><?php echo ($this->result->size) ? ' - '.JHtml::_('number.bytes', $this->result->size) : null; ?>
	</p>
<?php
endif;