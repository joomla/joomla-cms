<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * @var $links   array    Array with keys 'previous' and 'next' with non-SEO links to the previous and next pages
 * @var $page    integer  The page number
 */

$lang = Factory::getLanguage();
?>
<ul class="pagination">
    <li class="previous page-item">
        <?php if ($links['previous']) :
            $direction = $lang->isRtl() ? 'right' : 'left';
            $title = htmlspecialchars($this->list[$page]->title, ENT_QUOTES, 'UTF-8');
            $ariaLabel = Text::_('JPREVIOUS') . ': ' . $title . ' (' . Text::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $page, $n) . ')';
            ?>
        <a class="page-link" href="<?php echo Route::_($links['previous']); ?>" title="<?php echo $title; ?>" aria-label="<?php echo $ariaLabel; ?>" rel="prev">
            <?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span> ' . Text::_('JPREV'); ?>
        </a>
        <?php endif; ?>
    </li>
    <li class="next page-item">
        <?php if ($links['next']) :
            $direction = $lang->isRtl() ? 'left' : 'right';
            $title = htmlspecialchars($this->list[$page + 2]->title, ENT_QUOTES, 'UTF-8');
            $ariaLabel = Text::_('JNEXT') . ': ' . $title . ' (' . Text::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', ($page + 2), $n) . ')';
            ?>
        <a class="page-link" href="<?php echo Route::_($links['next']); ?>" title="<?php echo $title; ?>" aria-label="<?php echo $ariaLabel; ?>" rel="next">
            <?php echo Text::_('JNEXT') . ' <span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?>
        </a>
        <?php endif; ?>
    </li>
</ul>
