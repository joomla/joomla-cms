<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Contact\Site\Helper\RouteHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_contact.contacts-list')
    ->useScript('core');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<div class="com-contact-featured__items">
    <form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
        <?php if ($this->params->get('filter_field')) : ?>
            <div class="com-contact-featured__filter btn-group">
                <label class="filter-search-lbl visually-hidden" for="filter-search">
                    <?php echo Text::_('COM_CONTACT_FILTER_SEARCH_DESC'); ?>
                </label>
                <input
                    type="text"
                    name="filter-search"
                    id="filter-search"
                    value="<?php echo $this->escape($this->state->get('list.filter')); ?>"
                    class="inputbox" onchange="document.adminForm.submit();"
                    placeholder="<?php echo Text::_('COM_CONTACT_FILTER_SEARCH_DESC'); ?>"
                >
                <button type="submit" name="filter_submit" class="btn btn-primary"><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></button>
                <button type="reset" name="filter-clear-button" class="btn btn-secondary"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
            </div>
        <?php endif; ?>

        <?php if ($this->params->get('show_pagination_limit')) : ?>
            <div class="com-contact-featured__pagination btn-group float-end">
                <label for="limit" class="visually-hidden">
                    <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
                </label>
                <?php echo $this->pagination->getLimitBox(); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('COM_CONTACT_NO_CONTACTS'); ?>
            </div>
        <?php else : ?>
        <table class="com-contact-featured__table table table-striped table-bordered table-hover">
            <caption class="visually-hidden">
                <?php echo Text::_('COM_CONTACT_TABLE_CAPTION'); ?>,
            </caption>
            <?php if ($this->params->get('show_headings')) : ?>
                <thead>
                    <tr>
                        <th scope="col" class="item-title">
                            <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
                        </th>

                        <?php if ($this->params->get('show_position_headings')) : ?>
                        <th scope="col" class="item-position">
                            <?php echo HTMLHelper::_('grid.sort', 'COM_CONTACT_POSITION', 'a.con_position', $listDirn, $listOrder); ?>
                        </th>
                        <?php endif; ?>

                        <?php if ($this->params->get('show_email_headings')) : ?>
                        <th scope="col" class="item-email">
                            <?php echo Text::_('JGLOBAL_EMAIL'); ?>
                        </th>
                        <?php endif; ?>

                        <?php if ($this->params->get('show_telephone_headings')) : ?>
                        <th scope="col" class="item-phone">
                            <?php echo Text::_('COM_CONTACT_TELEPHONE'); ?>
                        </th>
                        <?php endif; ?>

                        <?php if ($this->params->get('show_mobile_headings')) : ?>
                        <th scope="col" class="item-phone">
                            <?php echo Text::_('COM_CONTACT_MOBILE'); ?>
                        </th>
                        <?php endif; ?>

                        <?php if ($this->params->get('show_fax_headings')) : ?>
                        <th scope="col" class="item-phone">
                            <?php echo Text::_('COM_CONTACT_FAX'); ?>
                        </th>
                        <?php endif; ?>

                        <?php if ($this->params->get('show_suburb_headings')) : ?>
                        <th scope="col" class="item-suburb">
                            <?php echo HTMLHelper::_('grid.sort', 'COM_CONTACT_SUBURB', 'a.suburb', $listDirn, $listOrder); ?>
                        </th>
                        <?php endif; ?>

                        <?php if ($this->params->get('show_state_headings')) : ?>
                        <th scope="col" class="item-state">
                            <?php echo HTMLHelper::_('grid.sort', 'COM_CONTACT_STATE', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                        <?php endif; ?>

                        <?php if ($this->params->get('show_country_headings')) : ?>
                        <th scope="col" class="item-state">
                            <?php echo HTMLHelper::_('grid.sort', 'COM_CONTACT_COUNTRY', 'a.country', $listDirn, $listOrder); ?>
                        </th>
                        <?php endif; ?>
                    </tr>
                </thead>
            <?php endif; ?>
            <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php if ($this->items[$i]->published == 0) : ?>
                        <tr class="system-unpublished featured-list-row<?php echo $i % 2; ?>">
                    <?php else : ?>
                        <tr class="featured-list-row<?php echo $i % 2; ?>" itemscope itemtype="https://schema.org/Person">
                    <?php endif; ?>
                    <th scope="row" class="list-title">
                        <a href="<?php echo Route::_(RouteHelper::getContactRoute($item->slug, $item->catid, $item->language)); ?>" itemprop="url">
                            <span itemprop="name"><?php echo $this->escape($item->name); ?></span>
                        </a>
                        <?php if ($item->published == 0) : ?>
                            <div>
                                <span class="list-published badge bg-warning text-light">
                                    <?php echo Text::_('JUNPUBLISHED'); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </th>

                    <?php if ($this->params->get('show_position_headings')) : ?>
                        <td class="item-position" itemprop="jobTitle">
                            <?php echo $item->con_position; ?>
                        </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('show_email_headings')) : ?>
                        <td class="item-email" itemprop="email">
                            <?php echo $item->email_to; ?>
                        </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('show_telephone_headings')) : ?>
                        <td class="item-phone" itemprop="telephone">
                            <?php echo $item->telephone; ?>
                        </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('show_mobile_headings')) : ?>
                        <td class="item-phone" itemprop="telephone">
                            <?php echo $item->mobile; ?>
                        </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('show_fax_headings')) : ?>
                        <td class="item-phone" itemprop="faxNumber">
                            <?php echo $item->fax; ?>
                        </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('show_suburb_headings')) : ?>
                        <td class="item-suburb" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                            <span itemprop="addressLocality"><?php echo $item->suburb; ?></span>
                        </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('show_state_headings')) : ?>
                        <td class="item-state" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                            <span itemprop="addressRegion"><?php echo $item->state; ?></span>
                        </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('show_country_headings')) : ?>
                        <td class="item-state" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                            <span itemprop="addressCountry"><?php echo $item->country; ?></span>
                        </td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <div>
            <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->state->get('list.ordering')); ?>">
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->state->get('list.direction')); ?>">
        </div>
    </form>
</div>
