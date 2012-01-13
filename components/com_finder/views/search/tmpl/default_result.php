<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Get the mime type class.
$mime = !empty($this->result->mime) ? 'mime-' . $this->result->mime : null;

// Get the base url.
$base = JURI::getInstance()->toString(array('scheme', 'host', 'port'));

// Get the route with highlighting information.
if (!empty($this->query->highlight) && empty($this->result->mime) && $this->params->get('highlight_terms', 1) && JPluginHelper::isEnabled('system', 'highlight')) {
	$route = $this->result->route . '&highlight=' . base64_encode(serialize($this->query->highlight));
} else {
	$route = $this->result->route;
}
?>

<dt class="result-title <?php echo $mime; ?>">
	<a href="<?php echo JRoute::_($route); ?>"><?php echo $this->result->title; ?></a>
</dt>
<dd class="result-text<?php echo $this->pageclass_sfx; ?>">
	<?php echo JHtml::_('string.truncate', $this->result->description, $this->params->get('description_length', 255)); ?>
</dd>

<?php if ($this->params->get('show_url', 1)): ?>
<dd class="result-url<?php echo $this->pageclass_sfx; ?>">
	<?php echo $base . JRoute::_($this->result->route); ?>
</dd>
<?php endif;
