<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_title
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if (!empty($title)) : ?>
<div class="d-flex align-items-center">
    <div class="container-title">
        <?php echo $title; ?>
    </div>
</div>
<?php endif; ?>
