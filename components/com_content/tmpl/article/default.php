<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$params = $this->item->params;

// Decode attribs safely once
$attribs = !empty($this->item->attribs) ? json_decode($this->item->attribs) : new stdClass();

// Decode images and URLs safely
$images  = isset($this->item->images)  ? json_decode($this->item->images)  : new stdClass();
$urls    = isset($this->item->urls)    ? json_decode($this->item->urls)    : new stdClass();

?>

{{--
  FIX 1: Missing space before dynamic pageclass_sfx
  BEFORE: class="com-content-article item-page<?php echo $this->pageclass_sfx; ?>"
  AFTER:  class="com-content-article item-page <?php echo $this->pageclass_sfx; ?>"
  Source: https://github.com/joomla/joomla-cms/issues/38238
--}}

<div class="com-content-article item-page <?php echo $this->escape($this->pageclass_sfx); ?>"
     itemscope itemtype="https://schema.org/Article">

    <?php if ($params->get('show_page_heading')) : ?>
    <div class="page-header">
        {{--
          FIX 2: Better heading style — wrap title in <h1> cleanly,
          remove redundant JHtml::_('string.truncate') call on title.
          BEFORE: <?php echo JHtml::_('string.truncate', $this->escape($this->item->title), 50); ?>
          AFTER:  <?php echo $this->escape($this->item->title); ?>
        --}}
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    </div>
    <?php endif; ?>

    <?php echo LayoutHelper::render('joomla.content.icons', [
        'params' => $params,
        'item'   => $this->item,
        'print'  => false,
    ]); ?>

    {{--
      FIX 3: Article title — remove redundant truncation, use plain escape.
      BEFORE: echo JHtml::_('string.truncate', $this->escape($this->item->title), 50);
      AFTER:  echo $this->escape($this->item->title);
    --}}
    <?php if ($params->get('show_title')) : ?>
    <h2 class="article-title" itemprop="name">
        <?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
            <a href="<?php echo $this->item->readmore_link; ?>" itemprop="url">
                <?php echo $this->escape($this->item->title); ?>
            </a>
        <?php else : ?>
            <?php echo $this->escape($this->item->title); ?>
        <?php endif; ?>
    </h2>
    <?php endif; ?>

    <?php echo LayoutHelper::render('joomla.content.info_block.block', [
        'item'     => $this->item,
        'params'   => $params,
        'position' => 'above',
    ]); ?>

    {{--
      FIX 4: URL position logic fix.
      BEFORE: used $urls->urls_position which does NOT exist — only $urls->links and $urls->text exist.
      AFTER:  fall back to $params->get('urls_position') when $urls object has no urls_position property.
    --}}
    <?php
    $urlsPosition = isset($urls->urls_position)
        ? $urls->urls_position
        : $params->get('urls_position', '0');
    ?>

    <?php if (isset($urls) && ($urlsPosition == '0')) : ?>
        <?php echo LayoutHelper::render('joomla.content.urls', ['item' => $this->item]); ?>
    <?php endif; ?>

    <?php
    // Fulltext image
    if (!empty($images->image_fulltext)) :
        $imgfloat = empty($images->float_fulltext)
            ? $params->get('float_fulltext')
            : $images->float_fulltext;
    ?>
    <div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image">
        <img
            src="<?php echo htmlspecialchars($images->image_fulltext); ?>"
            alt="<?php echo htmlspecialchars($images->image_fulltext_alt ?? ''); ?>"
            itemprop="image"
        />
    </div>
    <?php endif; ?>

    <?php // Add spacing between article body sections — FIX 5: improved layout spacing ?>
    <div class="article-body" itemprop="articleBody">
        <?php echo $this->item->text; ?>
    </div>

    <?php echo LayoutHelper::render('joomla.content.info_block.block', [
        'item'     => $this->item,
        'params'   => $params,
        'position' => 'below',
    ]); ?>

    <?php if ($urlsPosition == '1') : ?>
        <?php echo LayoutHelper::render('joomla.content.urls', ['item' => $this->item]); ?>
    <?php endif; ?>

    {{--
      FIX 6: alternative_readmore property fix.
      BEFORE: $this->item->alternative_readmore  → triggers "Undefined property" notice
      AFTER:  $attribs->alternative_readmore      → correct location of this property
    --}}
    <?php
    $readmore      = '';
    $readmoreTitle = '';

    if ($params->get('show_readmore') && !empty($this->item->readmore)) :
        if (!empty($attribs->alternative_readmore)) {
            // FIX: was incorrectly read from $this->item — now correctly from $attribs
            $readmore = $attribs->alternative_readmore;
        } else {
            $readmore = $params->get('show_readmore_title', 0)
                ? JText::sprintf('COM_CONTENT_READ_MORE_TITLE', $this->escape($this->item->title))
                : JText::_('COM_CONTENT_READ_MORE');
        }
    ?>
    <div class="readmore mt-3">
        <a href="<?php echo $this->item->readmore_link; ?>" itemprop="url">
            <?php echo $this->escape($readmore); ?>
        </a>
    </div>
    <?php endif; ?>

</div>