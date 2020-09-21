<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Taxonomy;
use Joomla\String\StringHelper;

$user = Factory::getUser();

// Get the mime type class.
$mime = !empty($this->result->mime) ? 'mime-' . $this->result->mime : null;

$show_description = $this->params->get('show_description', 1);

if ($show_description)
{
	// Calculate number of characters to display around the result
	$term_length = StringHelper::strlen($this->query->input);
	$desc_length = $this->params->get('description_length', 255);
	$pad_length  = $term_length < $desc_length ? (int) floor(($desc_length - $term_length) / 2) : 0;

	// Make sure we highlight term both in introtext and fulltext
	if (!empty($this->result->summary) && !empty($this->result->body))
	{
		$full_description = Helper::parse($this->result->summary . $this->result->body);
	}
	else
	{
		$full_description = $this->result->description;
	}

	// Find the position of the search term
	$pos = $term_length ? StringHelper::strpos(StringHelper::strtolower($full_description), StringHelper::strtolower($this->query->input)) : false;

	// Find a potential start point
	$start = ($pos && $pos > $pad_length) ? $pos - $pad_length : 0;

	// Find a space between $start and $pos, start right after it.
	$space = StringHelper::strpos($full_description, ' ', $start > 0 ? $start - 1 : 0);
	$start = ($space && $space < $pos) ? $space + 1 : $start;

	$description = HTMLHelper::_('string.truncate', StringHelper::substr($full_description, $start), $desc_length, true);
}
?>
<dt class="result-title">
	<h4 class="result-title <?php echo $mime; ?>">
		<?php if ($this->result->route) : ?>
			<a href="<?php echo Route::_($this->result->route); ?>">
				<?php echo $this->result->title; ?>
			</a>
		<?php else : ?>
			<?php echo $this->result->title; ?>
		<?php endif; ?>
	</h4>
</dt>

<?php $taxonomies = $this->result->getTaxonomy(); ?>
<?php if (count($taxonomies) && $this->params->get('show_taxonomy', 1)) : ?>
	<dd class="result-taxonomy">
	<?php foreach ($taxonomies as $type => $taxonomy) : ?>
		<?php $branch = Taxonomy::getBranch($type); ?>
		<?php if ($branch->state == 1 && in_array($branch->access, $user->getAuthorisedViewLevels())) : ?>
			<?php
			$taxonomy_text = array();

			foreach ($taxonomy as $node) :
				if ($node->state == 1 && in_array($node->access, $user->getAuthorisedViewLevels())) :
					$taxonomy_text[] = $node->title;
				endif;
			endforeach;

			if (count($taxonomy_text)) : ?>
				<span class="badge badge-secondary"><?php echo $type . ': ' . implode(',', $taxonomy_text); ?></span>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
	</dd>
<?php endif; ?>
<?php if ($show_description && $description !== '') : ?>
	<dd class="result-text">
		<?php echo $description; ?>
	</dd>
<?php endif; ?>
<?php if ($this->result->start_date && $this->params->get('show_date', 1)) : ?>
	<dd class="result-date small">
		<?php echo HTMLHelper::_('date', $this->result->start_date, Text::_('DATE_FORMAT_LC3')); ?>
	</dd>
<?php endif; ?>
<?php if ($this->params->get('show_url', 1)) : ?>
	<dd class="result-url small">
		<?php echo $this->baseUrl, Route::_($this->result->cleanURL); ?>
	</dd>
<?php endif; ?>