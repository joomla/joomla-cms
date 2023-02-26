<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   array  $statsData  Array containing the data that will be sent to the stats server
 */

$versionFields = array('php_version', 'db_version', 'cms_version');
?>
<table class="table mb-3 d-none" id="js-pstats-data-details">
    <caption class="visually-hidden">
        <?php echo Text::_('PLG_SYSTEM_STATS_STATISTICS'); ?>
    </caption>
    <thead>
        <tr>
            <th scope="col" class="w-15">
                <?php echo Text::_('PLG_SYSTEM_STATS_SETTING'); ?>
            </th>
            <th scope="col">
                <?php echo Text::_('PLG_SYSTEM_STATS_VALUE'); ?>
            </th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($statsData as $key => $value) : ?>
        <tr>
            <th scope="row"><?php echo Text::_('PLG_SYSTEM_STATS_LABEL_' . strtoupper($key)); ?></th>
            <td><?php echo in_array($key, $versionFields) ? (preg_match('/\d+(?:\.\d+)+/', $value, $matches) ? $matches[0] : $value) : $value; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
