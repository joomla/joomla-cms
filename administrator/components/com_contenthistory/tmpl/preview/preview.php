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

Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

?>
<div role="main">
    <h1>
        <?php echo Text::sprintf('COM_CONTENTHISTORY_PREVIEW_SUBTITLE_DATE', $this->item->save_date); ?>
    </h1>
    <?php if ($this->item->version_note) : ?>
        <h2>
            <?php echo Text::sprintf('COM_CONTENTHISTORY_PREVIEW_SUBTITLE', $this->item->version_note); ?>
        </h2>
    <?php endif; ?>

    <table class="table">
        <caption class="visually-hidden">
            <?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_CAPTION'); ?>
        </caption>
        <thead>
            <tr>
                <th class="w-25" scope="col"><?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_FIELD'); ?></th>
                <th scope="col"><?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_VALUE'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->item->data as $name => $value) : ?>
            <?php if (is_object($value->value)) : ?>
                <tr>
                    <td colspan="2">
                        <?php echo $value->label; ?>
                    </td>
                </tr>
                <?php foreach ($value->value as $subName => $subValue) : ?>
                    <?php if ($subValue && isset($subValue->value)) : ?>
                        <?php $subValue->value = (\is_object($subValue->value) || \is_array($subValue->value)) ? \json_encode($subValue->value, \JSON_UNESCAPED_UNICODE) : $subValue->value; ?>
                        <tr>
                            <th scope="row"><em>&nbsp;&nbsp;<?php echo $subValue->label; ?></em></th>
                            <td><?php echo $subValue->value; ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <th scope="row"><?php echo $value->label; ?></th>
                    <td><?php echo $value->value; ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
