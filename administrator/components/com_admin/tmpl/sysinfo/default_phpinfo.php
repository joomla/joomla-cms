<?php

use Joomla\Component\Admin\Administrator\View\Sysinfo\HtmlView;
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var HtmlView $this */
?>
<div class="sysinfo">
    <?php echo $this->phpInfo; ?>
</div>
