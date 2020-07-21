<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pemasoft_finance
 *
 * @copyright   2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="item-page pemasoft_finance-data">
    <?php
    if (empty($this->stocks)) {
    ?>
        <p>
            <?php echo JText::_('COM_PMS_FINANCE_NO_DATA_FOUND'); ?>
        </p>
        <?php
    } else {
        foreach ($this->stocks as $stock) {
        ?>
            <p>
                <?php echo $stock->name; ?>
            </p>
    <?php }
    }
    ?>

</div>