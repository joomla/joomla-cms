<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Contact\Site\View\Category\HtmlView $this */
if ($this->maxLevel != 0 && count($this->children[$this->category->id]) > 0) :
    ?>
<ul class="com-contact-category__children list-striped list-condensed">
    <?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
        <?php if ($this->params->get('show_empty_categories') || $child->numitems || count($child->getChildren())) : ?>
    <li>
        <h4 class="item-title">
            <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($child->id, $child->language)); ?>">
            <?php echo $this->escape($child->title); ?>
            </a>

            <?php if ($this->params->get('show_cat_items') == 1) : ?>
                <span class="badge bg-info float-end" title="<?php echo Text::_('COM_CONTACT_CAT_NUM'); ?>"><?php echo $child->numitems; ?></span>
            <?php endif; ?>
        </h4>

            <?php if ($this->params->get('show_subcat_desc') == 1) : ?>
                <?php if ($child->description) : ?>
                <div class="category-desc">
                    <?php echo HTMLHelper::_('content.prepare', $child->description, '', 'com_contact.category'); ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (count($child->getChildren()) > 0) :
                $this->children[$child->id] = $child->getChildren();
                $this->category = $child;
                $this->maxLevel--;
                echo $this->loadTemplate('children');
                $this->category = $child->getParent();
                $this->maxLevel++;
            endif; ?>
    </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
