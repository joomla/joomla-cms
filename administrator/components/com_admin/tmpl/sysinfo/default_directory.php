<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Sysinfo\HtmlView $this */

?>
<div class="sysinfo">
    <table class="table">
        <caption class="visually-hidden">
            <?php echo Text::_('COM_ADMIN_DIRECTORY_PERMISSIONS'); ?>
        </caption>
        <thead>
            <tr>
                <th scope="col" class="w-60">
                    <?php echo Text::_('COM_ADMIN_DIRECTORY'); ?>
                </th>
                <th scope="col">
                    <?php echo Text::_('COM_ADMIN_STATUS'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->directory as $dir => $info) : ?>
                <tr>
                    <th scope="row">
                        <?php echo '&#x200E;' . HTMLHelper::_('directory.message', $dir, $info['message']); ?>
                    </th>
                    <td>
                        <?php echo HTMLHelper::_('directory.writable', $info['writable']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
