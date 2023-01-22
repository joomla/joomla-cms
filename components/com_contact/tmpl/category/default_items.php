<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Contact\Administrator\Helper\ContactHelper;
use Joomla\Component\Contact\Site\Helper\RouteHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_contact.contacts-list')
    ->useScript('core');

$canDo   = ContactHelper::getActions('com_contact', 'category', $this->category->id);
$canEdit = $canDo->get('core.edit');
$userId  = Factory::getUser()->id;

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<div class="com-contact-category__items">
    <form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
        <?php if ($this->params->get('filter_field')) : ?>
            <div class="com-contact-category__filter btn-group">
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
            <div class="com-contact-category__pagination btn-group float-end">
                <label for="limit" class="visually-hidden">
                    <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
                </label>
                <?php echo $this->pagination->getLimitBox(); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($this->items)) : ?>
            <?php if ($this->params->get('show_no_contacts', 1)) : ?>
                <div class="alert alert-info">
                    <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('COM_CONTACT_NO_CONTACTS'); ?>
                </div>
            <?php endif; ?>

        <?php else : ?>
            <table class="com-content-category__table category table table-striped table-bordered table-hover" id="contactList">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_CONTACT_TABLE_CAPTION'); ?>,
                </caption>
                <?php if ($this->params->get('show_headings')) : ?>
                    <thead>
                        <tr>
                            <th scope="col" id="categorylist_header_title">
                                <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder, null, 'asc', '', 'adminForm'); ?>
                            </th>
                            <th scope="col">
                                <?php echo Text::_('COM_CONTACT_CONTACT_DETAILS'); ?>
                            </th>
                            <?php if ($canEdit || ($canDo->get('core.edit.own') && $item->created_by === $userId)) : ?>
                                <th scope="col">
                                    <?php echo Text::_('COM_CONTACT_EDIT_CONTACT'); ?>
                                </th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                <?php endif; ?>
                <tbody>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <?php if ($this->items[$i]->published == 0) : ?>
                            <tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
                        <?php else : ?>
                            <tr class="cat-list-row<?php echo $i % 2; ?>" >
                        <?php endif; ?>
                        <th scope="row" class="list-title">
                            <a href="<?php echo Route::_(RouteHelper::getContactRoute($item->slug, $item->catid, $item->language)); ?>">
                                <?php if ($this->params->get('show_image_heading')) : ?>
                                    <?php if ($item->image) : ?>
                                        <?php echo LayoutHelper::render(
                                            'joomla.html.image',
                                            [
                                                'src'   => $item->image,
                                                'alt'   => '',
                                                'class' => 'contact-thumbnail img-thumbnail',
                                            ]
                                        ); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php echo $this->escape($item->name); ?>
                            </a>
                            <?php if ($item->published == 0) : ?>
                                <div>
                                    <span class="list-published badge bg-warning text-light">
                                        <?php echo Text::_('JUNPUBLISHED'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if ($item->publish_up && strtotime($item->publish_up) > strtotime(Factory::getDate())) : ?>
                                <div>
                                    <span class="list-published badge bg-warning text-light">
                                        <?php echo Text::_('JNOTPUBLISHEDYET'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if (!is_null($item->publish_down) && strtotime($item->publish_down) < strtotime(Factory::getDate())) : ?>
                                <div>
                                    <span class="list-published badge bg-warning text-light">
                                        <?php echo Text::_('JEXPIRED'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if ($item->published == -2) : ?>
                                <div>
                                    <span class="badge bg-warning text-light">
                                        <?php echo Text::_('JTRASHED'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php echo $item->event->afterDisplayTitle; ?>
                        </th>
                        <td>
                            <?php echo $item->event->beforeDisplayContent; ?>

                            <?php if ($this->params->get('show_telephone_headings') && !empty($item->telephone)) : ?>
                                <?php echo Text::sprintf('COM_CONTACT_TELEPHONE_NUMBER', $item->telephone); ?><br>
                            <?php endif; ?>

                            <?php if ($this->params->get('show_mobile_headings') && !empty($item->mobile)) : ?>
                                <?php echo Text::sprintf('COM_CONTACT_MOBILE_NUMBER', $item->mobile); ?><br>
                            <?php endif; ?>

                            <?php if ($this->params->get('show_fax_headings') && !empty($item->fax)) : ?>
                                <?php echo Text::sprintf('COM_CONTACT_FAX_NUMBER', $item->fax); ?><br>
                            <?php endif; ?>

                            <?php if ($this->params->get('show_position_headings') && !empty($item->con_position)) : ?>
                                <?php echo $item->con_position; ?><br>
                            <?php endif; ?>

                            <?php if ($this->params->get('show_email_headings') && !empty($item->email_to)) : ?>
                                <?php echo $item->email_to; ?><br>
                            <?php endif; ?>

                            <?php $location = []; ?>
                            <?php if ($this->params->get('show_suburb_headings') && !empty($item->suburb)) : ?>
                                <?php $location[] = $item->suburb; ?>
                            <?php endif; ?>

                            <?php if ($this->params->get('show_state_headings') && !empty($item->state)) : ?>
                                <?php $location[] = $item->state; ?>
                            <?php endif; ?>

                            <?php if ($this->params->get('show_country_headings') && !empty($item->country)) : ?>
                                <?php $location[] = $item->country; ?>
                            <?php endif; ?>
                            <?php echo implode(', ', $location); ?>

                            <?php echo $item->event->afterDisplayContent; ?>
                        </td>
                        <?php if ($canEdit || ($canDo->get('core.edit.own') && $item->created_by === $userId)) : ?>
                            <td>
                                <?php echo HTMLHelper::_('contacticon.edit', $item, $this->params); ?>
                            </td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($canDo->get('core.create')) : ?>
            <?php echo HTMLHelper::_('contacticon.create', $this->category, $this->category->params); ?>
        <?php endif; ?>

        <?php if ($this->params->get('show_pagination', 2)) : ?>
            <div class="com-contact-category__pagination w-100">
                <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                    <p class="com-contact-category__counter counter float-end pt-3 pe-2">
                        <?php echo $this->pagination->getPagesCounter(); ?>
                    </p>
                <?php endif; ?>

                <?php echo $this->pagination->getPagesLinks(); ?>
            </div>
        <?php endif; ?>
        <div>
            <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->state->get('list.ordering')); ?>">
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->state->get('list.direction')); ?>">
        </div>
    </form>
</div>
