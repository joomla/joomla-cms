<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Newsfeeds\Site\Helper\RouteHelper;

defined('_JEXEC') or die;

?>
<?php if ($this->maxLevel != 0 && count($this->children[$this->category->id]) > 0) : ?>
    <ul>
        <?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
            <?php if ($this->params->get('show_empty_categories') || $child->numitems || count($child->getChildren())) : ?>
                <li>
                    <span class="item-title">
                        <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($child->id, $child->language)); ?>">
                            <?php echo $this->escape($child->title); ?>
                        </a>
                    </span>
                    <?php if ($this->params->get('show_subcat_desc') == 1) : ?>
                        <?php if ($child->description) : ?>
                            <div class="category-desc">
                                <?php echo HTMLHelper::_('content.prepare', $child->description, '', 'com_newsfeeds.category'); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($this->params->get('show_cat_items') == 1) : ?>
                        <span class="badge bg-info">
                            <?php echo Text::_('COM_NEWSFEEDS_CAT_NUM'); ?>&nbsp;
                            <?php echo $child->numitems; ?>
                        </span>
                    <?php endif; ?>
                    <?php if (count($child->getChildren()) > 0) : ?>
                        <?php $this->children[$child->id] = $child->getChildren(); ?>
                        <?php $this->category = $child; ?>
                        <?php $this->maxLevel--; ?>
                        <?php echo $this->loadTemplate('children'); ?>
                        <?php $this->category = $child->getParent(); ?>
                        <?php $this->maxLevel++; ?>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
<?php endif;
