<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$buttons = $displayData;

?>
<div class="editor-xtd-buttons" role="toolbar" aria-label="<?php echo Text::_('JTOOLBAR'); ?>">
    <?php if ($buttons) : ?>
        <?php foreach ($buttons as $button) : ?>
            <?php echo $this->sublayout('button', $button); ?>
            <?php echo $this->sublayout('modal', $button); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
