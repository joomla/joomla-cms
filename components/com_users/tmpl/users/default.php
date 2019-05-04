<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Load the custom fields
//JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');


HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
?>

<?php if ($this->params->get('show_page_heading')) : ?>
    <h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
    </h1>
<?php endif; ?>

<?php if ($this->params->get('filter_field') !== 'hide' || $this->params->get('show_pagination_limit')) : ?>
    <fieldset class="com-content-category__filters filters btn-toolbar clearfix">
        <legend class="hidden-xs-up"><?php echo JText::_('COM_USERS_FORM_FILTER_LEGEND'); ?></legend>
		<?php if ($this->params->get('filter_field') !== 'hide') : ?>
            <div class="btn-group">
				<?php if ($this->params->get('filter_field') === 'tag') : ?>
                    <select name="filter_tag" id="filter_tag" onchange="document.adminForm.submit();">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_TAG'); ?></option>
						<?php echo HTMLHelper::_(
							'select.options',
							HTMLHelper::_('tag.options', array('filter.published' => array(1), 'filter.language' => $langFilter), true), 'value',
							'text', $this->state->get('filter.tag')
						); ?>
                    </select>
				<?php elseif ($this->params->get('filter_field') === 'month') : ?>
                    <select name="filter-search" id="filter-search" onchange="document.adminForm.submit();">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_MONTH'); ?></option>
						<?php echo HtmlHelper::_(
							'select.options', HtmlHelper::_('content.months', $this->state), 'value', 'text', $this->state->get('list.filter')
						); ?>
                    </select>
				<?php else : ?>
                    <label class="filter-search-lbl sr-only" for="filter-search">
						<?php echo JText::_('COM_USERS_' . $this->params->get('filter_field') . '_FILTER_LABEL') . '&#160;'; ?>
                    </label>
                    <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>"
                           class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_USERS_FILTER_SEARCH_DESC'); ?>"
                           placeholder="<?php echo JText::_('COM_USERS_' . $this->params->get('filter_field') . '_FILTER_LABEL'); ?>">
				<?php endif; ?>
            </div>
		<?php endif; ?>
		<?php if ($this->params->get('show_pagination_limit')) : ?>
            <div class="com-content-category__pagination btn-group float-right">
                <label for="limit" class="sr-only">
					<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
                </label>
				<?php echo $this->pagination->getLimitBox(); ?>
            </div>
		<?php endif; ?>

        <input type="hidden" name="filter_order" value="">
        <input type="hidden" name="filter_order_Dir" value="">
        <input type="hidden" name="limitstart" value="">
        <input type="hidden" name="task" value="">
    </fieldset>

    <div class="com-content-category__filter-submit control-group hidden-xs-up float-right">
        <div class="controls">
            <button type="submit" name="filter_submit" class="btn btn-primary"><?php echo JText::_('COM_USERS_FORM_FILTER_SUBMIT'); ?></button>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($this->items)) : ?>
	<?php if ($this->params->get('show_no_users', 1)) : ?>
        <p class="com-users-users__no-users"><?php echo JText::_('COM_USERS_NO_USERS'); ?></p>
	<?php endif; ?>
<?php else : ?>
<div class="com-users-users user-list">
    <form action="<?php echo Route::_('index.php?option=com_users&view=users'); ?>" method="post" name="adminForm" id="adminForm">
        <table class="com-users-users__table users table table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th scope="col" id="userlist_header_name">
			        <?php echo HTMLHelper::_('grid.sort', 'COM_USERS_USERS_NAME', 'users.name', $listDirn, $listOrder, null, 'asc', '', 'adminForm'); ?>
                </th>
                <th scope="col" id="userlist_header_articles">
		            <?php echo HTMLHelper::_('grid.sort', 'COM_USERS_USERS_NUMBER_ARTICLES', 'articlesByUser', $listDirn, $listOrder, null, 'asc', '', 'adminForm'); ?>
                </th>
                <th scope="col" id="userlist_header_lastvisit">
		            <?php echo HTMLHelper::_('grid.sort', 'COM_USERS_USERS_LASTVISIT', 'users.lastvisitDate', $listDirn, $listOrder, null, 'asc', '', 'adminForm'); ?>
                </th>
                <th scope="col" id="userlist_header_onlinestatus">
		            <?php echo HTMLHelper::_('grid.sort', 'COM_USERS_USERS_ONLINE_STATUS', 'session.time', $listDirn, $listOrder, null, 'asc', '', 'adminForm'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
			<?php foreach ($this->items as $i => $item): ?>

                <tr class="cat-list-row<?php $i % 2; ?>">

                    <td headers="categorylist_header_title" class="list-title">
                        <a href="<?php echo Route::_('index.php?option=com_users&view=user&id=' . $item->id); ?>">
							<?php echo $item->name; ?>
                        </a>
                    </td>

                    <td>
						<?php echo $item->articlesByUser; ?>
                    </td>
                    <td headers="categorylist_header_author" class="list-author">
	                    <?php echo HTMLHelper::_(
		                    'date', $item->lastvisitDate,
		                    $this->escape($this->params->get('date_format', Text::_('DATE_FORMAT_LC3')))
	                    ); ?>
                    </td>
                    <td headers="categorylist_header_hits" class="list-hits">
						<?php if ($item->time): ?>
                            <span class="badge badge-success">
								<?php echo Text::_('COM_USERS_ONLINE');?>
							</span>
						<?php else: ?>
                            <span class="badge badge-warning">
								<?php echo Text::_('COM_USERS_OFFLINE');?>
							</span>
						<?php endif; ?>
                    </td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="limitstart" value="">
        <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->state->get('list.ordering')); ?>">
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->state->get('list.direction')); ?>">

        <div class="pagination">
            <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
			<?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    </form>
	<?php endif; ?>
</div>
