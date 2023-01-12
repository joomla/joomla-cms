<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;

$clientId  = $this->state->get('client_id');

// Show only Module Positions of published Templates
$published = 1;
$positions = HTMLHelper::_('modules.positions', $clientId, $published);
$positions['']['items'][] = ModulesHelper::createOption('nochange', Text::_('COM_MODULES_BATCH_POSITION_NOCHANGE'));
$positions['']['items'][] = ModulesHelper::createOption('noposition', Text::_('COM_MODULES_BATCH_POSITION_NOPOSITION'));

// Build field
$attr = [
    'id' => 'batch-position-id',
];

Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');

$this->document->getWebAssetManager()
    ->usePreset('choicesjs')
    ->useScript('webcomponent.field-fancy-select')
    ->useScript('joomla.batch-copymove');

?>

<div class="p-3">
    <p><?php echo Text::_('COM_MODULES_BATCH_TIP'); ?></p>
    <div class="row">
        <?php if ($clientId != 1) : ?>
            <div class="form-group col-md-6">
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
                </div>
            </div>
        <?php elseif ($clientId == 1 && ModuleHelper::isAdminMultilang()) : ?>
            <div class="form-group col-md-6">
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.adminlanguage', []); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-group col-md-6">
            <div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <?php if ($published >= 0) : ?>
            <div class="col-md-6">
                <div class="controls">
                    <label id="batch-choose-action-lbl" for="batch-choose-action">
                        <?php echo Text::_('COM_MODULES_BATCH_POSITION_LABEL'); ?>
                    </label>
                    <div id="batch-choose-action">
                        <joomla-field-fancy-select allow-custom search-placeholder="<?php echo $this->escape(Text::_('COM_MODULES_TYPE_OR_SELECT_POSITION')); ?>">
                        <?php echo HTMLHelper::_('select.groupedlist', $positions, 'batch[position_id]', $attr); ?>
                        </joomla-field-fancy-select>
                        <div id="batch-copy-move" class="control-group radio">
                            <?php echo HTMLHelper::_('modules.batchOptions'); ?>
                        </div>
                    </div>
                </div>
        <?php endif; ?>
        </div>
    </div>
</div>
