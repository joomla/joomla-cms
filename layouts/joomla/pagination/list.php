<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$list       = $displayData['list'];
$responsive = $list['responsive'] ?? null;

$renderEllipsis = static function (?array $ellipsis): string {
    if ($ellipsis === null) {
        return '';
    }

    return '<li class="page-item ellipsis ' . $ellipsis['classes'] . '"' . ($ellipsis['hidden'] ? ' hidden' : '')
        . ' aria-hidden="true"><span class="page-link">&hellip;</span></li>';
};

?>
<nav class="pagination__wrapper" aria-label="<?php echo Text::_('JLIB_HTML_PAGINATION'); ?>">
    <?php if ($responsive) : ?>
        <ul class="pagination pagination--responsive ms-0 mb-4">
            <?php echo $list['previous']['data']; ?>
            <?php echo $responsive['first']['data']; ?>
            <?php echo $renderEllipsis($responsive['ellipsisStart']); ?>

            <?php foreach ($responsive['pages'] as $page) : ?>
                <?php echo $page['data']; ?>
            <?php endforeach; ?>

            <?php echo $renderEllipsis($responsive['ellipsisEnd']); ?>
            <?php echo $responsive['last']['data']; ?>
            <?php echo $list['next']['data']; ?>
        </ul>
    <?php else : ?>
        <ul class="pagination ms-0 mb-4">
            <?php echo $list['start']['data']; ?>
            <?php echo $list['previous']['data']; ?>

            <?php foreach ($list['pages'] as $page) : ?>
                <?php echo $page['data']; ?>
            <?php endforeach; ?>

            <?php echo $list['next']['data']; ?>
            <?php echo $list['end']['data']; ?>
        </ul>
    <?php endif; ?>
</nav>
