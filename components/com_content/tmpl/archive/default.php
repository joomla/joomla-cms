<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Content\Site\View\Archive\HtmlView $this */
?>
<div class="com-content-archive archive">
<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1>
            <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
    </div>
<?php endif; ?>

<form id="adminForm" action="<?php echo Route::_('index.php'); ?>" method="post" class="com-content-archive__form">
    <fieldset class="com-content-archive__filters filters">
        <legend class="visually-hidden">
            <?php echo Text::_('COM_CONTENT_FORM_FILTER_LEGEND'); ?>
        </legend>
        <div class="filter-search form-inline">
            <?php if ($this->params->get('filter_field') !== 'hide') : ?>
            <div class="mb-2">
                <label class="filter-search-lbl visually-hidden" for="filter-search"><?php echo Text::_('COM_CONTENT_TITLE_FILTER_LABEL') . '&#160;'; ?></label>
                <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox col-md-2" onchange="document.getElementById('adminForm').submit();" placeholder="<?php echo Text::_('COM_CONTENT_TITLE_FILTER_LABEL'); ?>">
            </div>
            <?php endif; ?>

            <span class="me-2">
                <label class="visually-hidden" for="month"><?php echo Text::_('JMONTH'); ?></label>
                <?php echo $this->form->monthField; ?>
            </span>
            <span class="me-2">
                <label class="visually-hidden" for="year"><?php echo Text::_('JYEAR'); ?></label>
                <?php echo $this->form->yearField; ?>
            </span>
            <span class="me-2">
                <label class="visually-hidden" for="limit"><?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?></label>
                <?php echo $this->form->limitField; ?>
            </span>

            <button type="submit" class="btn btn-primary" style="vertical-align: top;"><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></button>
            <input type="hidden" name="view" value="archive">
            <input type="hidden" name="option" value="com_content">
            <input type="hidden" name="limitstart" value="0">
        </div>
    </fieldset>
</form>
<?php echo $this->loadTemplate('items'); ?>
</div>
