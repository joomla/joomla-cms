<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Taxonomy;
use Joomla\String\StringHelper;

$user             = Factory::getApplication()->getIdentity();
$show_description = $this->params->get('show_description', 1);

if ($show_description) {
    // Calculate number of characters to display around the result
    $term_length = StringHelper::strlen($this->query->input);
    $desc_length = $this->params->get('description_length', 255);
    $pad_length  = $term_length < $desc_length ? (int) floor(($desc_length - $term_length) / 2) : 0;

    // Make sure we highlight term both in introtext and fulltext
    $full_description = $this->result->description;
    if (!empty($this->result->summary) && !empty($this->result->body)) {
        $full_description = Helper::parse($this->result->summary . $this->result->body);
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

$showImage  = $this->params->get('show_image', 0);
$imageClass = $this->params->get('image_class', '');
$extraAttr  = [];

if ($showImage && !empty($this->result->imageUrl) && $imageClass !== '') {
    $extraAttr['class'] = $imageClass;
}

$icon = '';
if (!empty($this->result->mime)) {
    $icon = '<span class="icon-file-' . $this->result->mime . '" aria-hidden="true"></span> ';
}


$show_url = '';
if ($this->params->get('show_url', 1)) {
    $show_url = '<cite class="result__title-url">' . $this->baseUrl . Route::_($this->result->cleanURL) . '</cite>';
}
?>
<li class="result__item">
    <?php if ($showImage && isset($this->result->imageUrl)) : ?>
        <figure class="<?php echo htmlspecialchars($imageClass, ENT_COMPAT, 'UTF-8'); ?> result__image">
            <?php if ($this->params->get('link_image') && $this->result->route) : ?>
                <a href="<?php echo Route::_($this->result->route); ?>">
                    <?php echo HTMLHelper::_('image', $this->result->imageUrl, $this->result->imageAlt, $extraAttr); ?>
                </a>
            <?php else : ?>
                <?php echo HTMLHelper::_('image', $this->result->imageUrl, $this->result->imageAlt, $extraAttr); ?>
            <?php endif; ?>
        </figure>
    <?php endif; ?>
    <p class="result__title">
        <?php if ($this->result->route) : ?>
            <?php echo HTMLHelper::link(
                Route::_($this->result->route),
                '<span class="result__title-text">' . $icon . $this->result->title . '</span>' . $show_url,
                [
                            'class' => 'result__title-link'
                    ]
            ); ?>
        <?php else : ?>
            <?php echo $this->result->title; ?>
        <?php endif; ?>
    </p>
    <?php if ($show_description && $description !== '') : ?>
        <p class="result__description">
            <?php if ($this->result->start_date && $this->params->get('show_date', 1)) : ?>
                <time class="result__date" datetime="<?php echo HTMLHelper::_('date', $this->result->start_date, 'c'); ?>">
                    <?php echo HTMLHelper::_('date', $this->result->start_date, Text::_('DATE_FORMAT_LC3')); ?>
                </time>
            <?php endif; ?>
            <?php echo $description; ?>
        </p>
    <?php endif; ?>
    <?php $taxonomies = $this->result->getTaxonomy(); ?>
    <?php if (count($taxonomies) && $this->params->get('show_taxonomy', 1)) : ?>
        <ul class="result__taxonomy">
            <?php foreach ($taxonomies as $type => $taxonomy) : ?>
                <?php if ($type == 'Language' && (!Multilanguage::isEnabled() || (isset($taxonomy[0]) && $taxonomy[0]->title == '*'))) : ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php $branch = Taxonomy::getBranch($type); ?>
                <?php if ($branch->state == 1 && in_array($branch->access, $user->getAuthorisedViewLevels())) : ?>
                    <?php $taxonomy_text = []; ?>
                    <?php foreach ($taxonomy as $node) : ?>
                        <?php if ($node->state == 1 && in_array($node->access, $user->getAuthorisedViewLevels())) : ?>
                            <?php $taxonomy_text[] = $node->title; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (count($taxonomy_text)) : ?>
                        <li class="result__taxonomy-item result__taxonomy--<?php echo $type; ?>">
                            <span><?php echo Text::_(LanguageHelper::branchSingular($type)); ?>:</span>
                            <?php echo Text::_(LanguageHelper::branchSingular(implode(',', $taxonomy_text))); ?>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>
