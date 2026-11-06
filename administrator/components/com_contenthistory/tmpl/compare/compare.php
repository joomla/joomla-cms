<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/** @var \Joomla\Component\Contenthistory\Administrator\View\Compare\HtmlView $this */

Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

$version2 = $this->items[0];
$version1 = $this->items[1];
$object1  = ArrayHelper::fromObject($version1->data);
$object2  = ArrayHelper::fromObject($version2->data);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_contenthistory.admin-compare-compare');

?>
<div role="main">
    <h1 class="mb-3"><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_TITLE'); ?></h1>

    <table id="diff" class="table">
        <caption class="visually-hidden">
            <?php echo Text::_('COM_CONTENTHISTORY_COMPARE_CAPTION'); ?>
        </caption>
        <thead>
            <tr>
                <th scope="col" class="w-25"><?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_FIELD'); ?></th>
                <th scope="col"><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_OLD'); ?></th>
                <th scope="col"><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_NEW'); ?></th>
                <th scope="col"><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_DIFF'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($object1 as $name => $value1) : ?>
                <?php if (isset($value1['value']) && isset($object2[$name]['value']) && ($value1['value'] !== $object2[$name]['value'])) : ?>
                    <?php $value2 = $object2[$name]; ?>
                    <?php
                    if (is_array($value1)) : ?>
                        <?php if (is_array($value1['value'])) : ?>
                            <tr>
                                <td colspan="4">
                                    <strong><?php echo $value1['label']; ?></strong>
                                </td>
                            </tr>
                            <?php $keys = array_keys($value1['value']); ?>
                            <?php if (isset($value2['value']) && is_array($value2['value'])) :?>
                                <?php $keys = array_unique(array_merge(array_keys($value1['value']), array_keys($value2['value']))); ?>
                            <?php endif; ?>
                                <?php foreach ($keys as $key) : ?>
                                    <?php if (isset($value1['value'][$key]) && isset($value2['value'][$key]) && $value1['value'][$key] === $value2['value'][$key]) :?>
                                        <?php continue; ?>
                                    <?php endif;?>
                                    <tr>
                                        <td></td>
                                        <td class="original">
                                            <?php if (isset($value1['value'][$key])) : ?>
                                                <?php $currentvalue1 = $value1['value'][$key]; ?>
                                                <?php if (is_array($currentvalue1)) : ?>
                                                    <?php $currentvalue1 = ArrayHelper::isAssociative($currentvalue1) ? json_encode($currentvalue1) : implode(' | ', $currentvalue1); ?>
                                                    <?php echo htmlspecialchars($key . ': ' . $currentvalue1, ENT_COMPAT, 'UTF-8'); ?>
                                                <?php else : ?>
                                                    <?php echo htmlspecialchars($key . ': ' . $currentvalue1, ENT_COMPAT, 'UTF-8'); ?>
                                                <?php endif;?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="changed">
                                            <?php if (isset($value2['value'][$key])) : ?>
                                                <?php $currentvalue2 = $value2['value'][$key]; ?>
                                                <?php if (is_array($currentvalue2)) : ?>
                                                    <?php $currentvalue2 = ArrayHelper::isAssociative($currentvalue2) ? json_encode($currentvalue2) : implode(' | ', $currentvalue2); ?>
                                                    <?php echo htmlspecialchars($key . ': ' . $currentvalue2, ENT_COMPAT, 'UTF-8'); ?>
                                                <?php else : ?>
                                                    <?php echo htmlspecialchars($key . ': ' . $currentvalue2, ENT_COMPAT, 'UTF-8'); ?>
                                                <?php endif;?>
                                            <?php endif; ?>
                                        <td class="diff">&nbsp;</td>
                                    </tr>
                                <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <th scope="row">
                                    <?php
                                    echo $value1['label']; ?>
                                </th>
                                <?php $currentvalue1 = is_array($value1['value']) ? json_encode($value1['value']) : $value1['value']; ?>
                                <td class="original"><?php
                                    echo htmlspecialchars($currentvalue1); ?></td>
                                <?php $currentvalue2 = is_array($value2['value']) ? json_encode($value2['value']) : $value2['value']; ?>
                                <td class="changed"><?php
                                    echo htmlspecialchars($currentvalue2, ENT_COMPAT, 'UTF-8'); ?></td>
                                <td class="diff">&nbsp;</td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
