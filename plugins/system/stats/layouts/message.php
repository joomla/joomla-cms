<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var  \Joomla\Plugin\System\Stats\Extension\Stats  $plugin        Plugin rendering this layout
 * @var  Registry                                     $pluginParams  Plugin parameters
 * @var  array                                        $statsData     Array containing the data that will be sent to the stats server
 */
?>

<joomla-alert type="info" dismiss class="js-pstats-alert hidden" role="alertdialog" close-text="<?php echo Text::_('JCLOSE'); ?>" aria-labelledby="alert-stats-heading">
    <div class="alert-heading" id="alert-stats-heading"><?php echo Text::_('PLG_SYSTEM_STATS_LABEL_MESSAGE_TITLE'); ?></div>
    <div>
        <div class="alert-message">
            <p>
                <?php echo Text::_('PLG_SYSTEM_STATS_MSG_JOOMLA_WANTS_TO_SEND_DATA'); ?>
            </p>
            <details>
                <summary>
                    <?php echo Text::_('PLG_SYSTEM_STATS_MSG_WHAT_DATA_WILL_BE_SENT'); ?>
                </summary>
                <?php echo $plugin->render('stats', compact('statsData')); ?>
            </details>
            <p class="fw-bold"><?php echo Text::_('PLG_SYSTEM_STATS_MSG_ALLOW_SENDING_DATA'); ?></p>
            <p class="actions">
                <button type="button" class="btn btn-primary js-pstats-btn-allow-never"><?php echo Text::_('PLG_SYSTEM_STATS_BTN_NEVER_SEND'); ?></button>
                <button type="button" class="btn btn-primary js-pstats-btn-allow-always"><?php echo Text::_('PLG_SYSTEM_STATS_BTN_SEND_ALWAYS'); ?></button>
            </p>
        </div>
    </div>
</joomla-alert>
