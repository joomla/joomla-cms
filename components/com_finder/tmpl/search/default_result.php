<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

// Get the mime type class.
$mime = !empty($this->result->mime) ? 'mime-' . $this->result->mime : null;

$show_description = $this->params->get('show_description', 1);

if ($show_description)
{
	// Calculate number of characters to display around the result
	$term_length = StringHelper::strlen($this->query->input);
	$desc_length = $this->params->get('description_length', 255);
	$pad_length  = $term_length < $desc_length ? (int) floor(($desc_length - $term_length) / 2) : 0;

	// Find the position of the search term
	$pos = $term_length ? StringHelper::strpos(StringHelper::strtolower($this->result->description), StringHelper::strtolower($this->query->input)) : false;

	// Find a potential start point
	$start = ($pos && $pos > $pad_length) ? $pos - $pad_length : 0;

	// Find a space between $start and $pos, start right after it.
	$space = StringHelper::strpos($this->result->description, ' ', $start > 0 ? $start - 1 : 0);
	$start = ($space && $space < $pos) ? $space + 1 : $start;

	$description = JHtml::_('string.truncate', StringHelper::substr($this->result->description, $start), $desc_length, true);
}

$route = $this->result->route;

// Get the route with highlighting information.
if (!empty($this->query->highlight)
	&& empty($this->result->mime)
	&& $this->params->get('highlight_terms', 1)
	&& JPluginHelper::isEnabled('system', 'highlight'))
{
	$route .= '&highlight=' . base64_encode(json_encode($this->query->highlight));
}
?>
<dt class="result-title">
	<?php echo $this->pagination->limitstart + $this->result->counter . '. '; ?>
	<h4 class="result-title <?php echo $mime; ?>">
		<?php if ($this->result->route) : ?>
			<a href="<?php echo JRoute::_($this->result->route); ?>">
				<?php echo $this->result->title; ?>
			</a>
		<?php else : ?>
			<?php echo $this->result->title; ?>
		<?php endif; ?>
	</h4>
</dt>
<?php
$type = $this->result->getTaxonomy('Type');
$type = array_shift($type);
?>
<dd class="result-type">
	<span class="small">
		(<?php echo $type->title; ?>)
	</span>
</dd>
<?php if ($this->result->category) : ?>
	<dd class="result-category">
		<span class="small">
			(<?php echo $this->result->category; ?>)
		</span>
	</dd>
<?php endif; ?>
<?php if ($show_description && $description !== '') : ?>
	<dd class="result-text">
		<p class="result-text">
			<?php echo $description; ?>
		</p>
	</dd>
<?php endif; ?>
<?php if ($this->result->created && $this->params->get('show_date', 1)) : ?>
	<dd class="result-created">
		<?php echo JText::sprintf('JGLOBAL_CREATED_DATE_ON', \JHtml::_('date', $this->result->start_date, \JText::_('DATE_FORMAT_LC3'))); ?>
	</dd>
<?php endif; ?>
