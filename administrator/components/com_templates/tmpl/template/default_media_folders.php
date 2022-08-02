<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Legacy is the default
if (!(is_countable($this->mediaFiles) ? count($this->mediaFiles) : 0)) {
    return;
}

ksort($this->mediaFiles, SORT_STRING);
?>
<ul class="directory-tree treeselect">
    <?php foreach ($this->mediaFiles as $key => $value) : ?>
        <?php if (is_array($value)) : ?>
            <li class="folder-select">
                <a class="folder-url" data-id="<?php echo base64_encode((string) $key); ?>" href="" data-base="media">
                    <span class="icon-folder icon-fw" aria-hidden="true"></span>
                    <?php $explodeArray = explode('/', rtrim((string) $key, '\\'));
                    echo $this->escape(end($explodeArray)); ?>
                </a>
                <?php echo $this->mediaFolderTree($value); ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
