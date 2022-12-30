<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$input = Factory::getApplication()->getInput();
?>

<?php if (count($this->updatedList) === 0) : ?>
    <div class="alert alert-success">
        <span class="icon-check-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('NOTICE'); ?></span>
        <?php echo Text::_('COM_TEMPLATES_OVERRIDE_UPTODATE'); ?>
    </div>
<?php else : ?>
    <div class="alert alert-info">
        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
        <?php echo Text::_('COM_TEMPLATES_OVERRIDE_NOT_UPTODATE'); ?>
    </div>
    <form action="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="updateForm" id="updateForm">
        <div class="row mt-2">
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <tr>
                            <td class="w-5 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </td>
                            <th scope="col" class="w-7">
                                <?php echo Text::_('COM_TEMPLATES_OVERRIDE_CHECKED'); ?>
                            </th>
                            <th scope="col" class="w-30">
                                <?php echo Text::_('COM_TEMPLATES_OVERRIDE_TEMPLATE_FILE'); ?>
                            </th>
                            <th scope="col">
                                <?php echo Text::_('COM_TEMPLATES_OVERRIDE_CREATED_DATE'); ?>
                            </th>
                            <th scope="col">
                                <?php echo Text::_('COM_TEMPLATES_OVERRIDE_MODIFIED_DATE'); ?>
                            </th>
                            <th scope="col">
                                <?php echo Text::_('COM_TEMPLATES_OVERRIDE_SOURCE'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->updatedList as $i => $value) : ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $value->hash_id, false, 'cid', 'cb', '', 'updateForm'); ?>
                                </td>
                                <td>
                                    <?php echo HTMLHelper::_('jgrid.published', $value->state, $i, 'template.', 1, 'cb', null, null, 'updateForm'); ?>
                                </td>
                                <td>
                                    <a href="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . (int) $value->extension_id . '&file=' . $value->hash_id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>"><?php echo base64_decode($value->hash_id); ?></a>
                                </td>
                                <td>
                                    <?php $created_date = $value->created_date; ?>
                                    <?php echo $created_date > 0 ? HTMLHelper::_('date', $created_date, Text::_('DATE_FORMAT_FILTER_DATETIME')) : '-'; ?>
                                </td>
                                <td>
                                    <?php if (is_null($value->modified_date)) : ?>
                                        <span class="badge bg-warning text-dark"><?php echo Text::_('COM_TEMPLATES_OVERRIDE_CORE_REMOVED'); ?></span>
                                    <?php else : ?>
                                        <?php echo HTMLHelper::_('date', $value->modified_date, Text::_('DATE_FORMAT_FILTER_DATETIME')); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $value->action; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </form>
<?php endif; ?>
