<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Get the mime type class.
$mime = !empty($this->result->mime) ? 'mime-' . $this->result->mime : null;

// Get the base url.
$base = JUri::getInstance()->toString(array('scheme', 'host', 'port'));

// Calculate number of characters to display around the result
$length = $this->params->get('description_length', 255) - strlen($this->query->input);

// Get the route with highlighting information.
if (!empty($this->query->highlight) && empty($this->result->mime) && $this->params->get('highlight_terms', 1) && JPluginHelper::isEnabled('system', 'highlight'))
{
	$route = $this->result->route . '&highlight=' . base64_encode(json_encode($this->query->highlight));
} else {
	$route = $this->result->route;
}
?>

<li>
	<h4 class="result-title <?php echo $mime; ?>"><a href="<?php echo JRoute::_($route); ?>"><?php echo $this->result->title; ?></a></h4>
	<?php if ($this->params->get('show_description', 1)) : ?>
	<p class="result-text<?php echo $this->pageclass_sfx; ?>">
		<?php preg_match('/(?:.){0,' . $length . '}' . $this->query->input . '(?:.){0,' . $length . '}/s', $this->result->description, $description); ?>
		<?php if(!empty($description[0])) : ?>
			<?php echo $description[0]; ?>
		<?php else: ?>
			<?php echo JHtml::_('string.truncate', $this->result->description, $this->params->get('description_length', 255)); ?>
		<?php endif; ?>
	</p>
	<?php endif; ?>
	<?php if ($this->params->get('show_url', 1)) : ?>
	<div class="small result-url<?php echo $this->pageclass_sfx; ?>"><?php echo $base . JRoute::_($this->result->route); ?></div>
	<?php endif; ?>
</li>
