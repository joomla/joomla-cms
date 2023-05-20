<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<table class="table" id="<?php echo str_replace(' ', '', $module->title) . $module->id; ?>">
    <caption class="visually-hidden"><?php echo $module->title; ?></caption>
    <thead>
        <tr>
            <th scope="col" class="w-50">
                <?php if ($params->get('name', 1) == 0) : ?>
                    <?php echo Text::_('JGLOBAL_USERNAME'); ?>
                <?php else : ?>
                    <?php echo Text::_('MOD_LOGGED_NAME'); ?>
                <?php endif; ?>
            </th>
            <th scope="col" class="w-30"><?php echo Text::_('JCLIENT'); ?></th>
            <th scope="col" class="w-20"><?php echo Text::_('JDATE'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user) : ?>
            <tr>
                <th scope="row">
                    <?php if (isset($user->editLink)) : ?>
                        <a href="<?php echo $user->editLink; ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php else : ?>
                        <?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </th>
                <td>
                    <?php if ($user->client_id === null) : ?>
                        <?php // This is a shared session so we do not know the client ?>
                        <?php echo Text::_('JGLOBAL_NONAPPLICABLE'); ?>
                    <?php elseif ($user->client_id) : ?>
                        <?php echo Text::_('JADMINISTRATION'); ?>
                    <?php else : ?>
                        <form action="<?php echo $user->logoutLink; ?>" method="post" name="adminForm">
                            <?php echo Text::_('JSITE'); ?>
                            <button type="submit" class="me-2 btn btn-danger btn-sm">
                                <?php echo Text::_('JLOGOUT'); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo HTMLHelper::_('date', $user->time, Text::_('DATE_FORMAT_LC5')); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
