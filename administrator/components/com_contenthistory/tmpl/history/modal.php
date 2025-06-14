<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Contenthistory\Administrator\View\History\HtmlView $this */

Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

$hash           = $this->state->get('sha1_hash');
$formUrl        = 'index.php?option=com_contenthistory&view=history&layout=modal&tmpl=component&item_id=' . $this->state->get('item_id') . '&' . Session::getFormToken() . '=1';

Text::script('COM_CONTENTHISTORY_BUTTON_SELECT_ONE_VERSION', true);
Text::script('COM_CONTENTHISTORY_BUTTON_SELECT_TWO_VERSIONS', true);
Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');

$inlineJS = <<<JS
document.querySelectorAll('.js-link-open-window').forEach((link) => link.addEventListener('click', (e) => {
  e.preventDefault();
  window.open(link.dataset.url, 'win2', 'width=800,height=600,resizable=yes,scrollbars=yes')
}));
JS;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('multiselect')
    ->useScript('com_contenthistory.admin-history-modal')
    ->useScript('list-view')
    ->addInlineScript($inlineJS, [], ['type' => 'module']);
?>
<div class="container-popup">
    <div id="subhead" class="subhead noshadow mb-3">
        <?php echo $this->toolbar->render(); ?>
    </div>
    <form action="<?php echo Route::_($formUrl); ?>" method="post" name="adminForm" id="adminForm">
        <table class="table table-sm">
            <caption class="visually-hidden">
                <?php echo Text::_('COM_CONTENTHISTORY_VERSION_CAPTION'); ?>
            </caption>
            <thead>
                <tr>
                    <td class="w-1 text-center">
                        <input class="form-check-input" type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)">
                    </td>
                    <th scope="col" class="w-15">
                        <?php echo Text::_('JDATE'); ?>
                    </th>
                    <th scope="col" class="w-15 d-none d-md-table-cell">
                        <?php echo Text::_('COM_CONTENTHISTORY_VERSION_NOTE'); ?>
                    </th>
                    <th scope="col" class="w-10">
                        <?php echo Text::_('COM_CONTENTHISTORY_KEEP_VERSION'); ?>
                    </th>
                    <th scope="col" class="w-15 d-none d-md-table-cell">
                        <?php echo Text::_('JAUTHOR'); ?>
                    </th>
                    <th scope="col" class="w-10 text-end">
                        <?php echo Text::_('COM_CONTENTHISTORY_CHARACTER_COUNT'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0; ?>
                <?php foreach ($this->items as $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="text-center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->version_id, false, 'cid', 'cb', $item->save_date); ?>
                        </td>
                        <th scope="row">
                            <a href="#" class="js-link-open-window save-date" data-url="<?php echo Route::_('index.php?option=com_contenthistory&view=preview&layout=preview&tmpl=component&' . Session::getFormToken() . '=1&version_id=' . $item->version_id); ?>">
                                <?php echo HTMLHelper::_('date', $item->save_date, Text::_('DATE_FORMAT_LC6')); ?>
                            </a>
                            <?php if ($item->sha1_hash == $hash) : ?>
                                <span class="icon-star" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('JCURRENT'); ?></span>
                            <?php endif; ?>
                        </th>
                        <td class="d-none d-md-table-cell">
                            <?php echo htmlspecialchars($item->version_note); ?>
                        </td>
                        <td>
                            <?php if ($item->keep_forever) : ?>
                                <button type="button" class="js-grid-item-action btn btn-secondary btn-sm" data-item-id="cb<?php echo $i; ?>" data-item-task="history.keep">
                                    <?php echo Text::_('JYES'); ?>
                                    &nbsp;<span class="icon-lock" aria-hidden="true"></span>
                                </button>
                            <?php else : ?>
                                <button type="button" class="js-grid-item-action btn btn-secondary btn-sm" data-item-id="cb<?php echo $i; ?>" data-item-task="history.keep">
                                    <?php echo Text::_('JNO'); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?php echo empty($item->editor) ? $item->editor_user_id : htmlspecialchars($item->editor); ?>
                        </td>
                        <td class="text-end">
                            <?php echo number_format((int) $item->character_count, 0, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR')); ?>
                        </td>
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php /* load the pagination. */ ?>
        <?php echo $this->pagination->getListFooter(); ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>

    </form>
</div>
