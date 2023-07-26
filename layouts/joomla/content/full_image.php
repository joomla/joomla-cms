<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$params  = $displayData->params;
$images  = json_decode($displayData->images);

if (empty($images->image_fulltext)) {
    return;
}

$imgclass   = empty($images->float_fulltext) ? $params->get('float_fulltext') : $images->float_fulltext;
$layoutAttr = [
    'src'      => $images->image_fulltext,
    'alt'      => empty($images->image_fulltext_alt) && empty($images->image_fulltext_alt_empty) ? false : $images->image_fulltext_alt,
];
?>
<figure class="<?php echo $this->escape($imgclass); ?> item-image">
    <?php echo LayoutHelper::render('joomla.html.image', $layoutAttr); ?>
    <?php if (isset($images->image_fulltext_caption) && $images->image_fulltext_caption !== '') : ?>
        <figcaption class="caption"><?php echo $this->escape($images->image_fulltext_caption); ?></figcaption>
    <?php endif; ?>
</figure>
