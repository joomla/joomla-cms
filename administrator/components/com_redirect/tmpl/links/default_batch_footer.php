<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


?>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="document.getElementById('batch_urls').value='';">
    <?php echo $this->text('JCANCEL'); ?>
</button>
<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('links.batch');return false;">
    <?php echo $this->text('JGLOBAL_BATCH_PROCESS'); ?>
</button>
