<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var \Joomla\Component\Templates\Administrator\View\Template\HtmlView $this */

// Legacy is the default
if (!count($this->mediaFiles)) {
    return;
}

ksort($this->mediaFiles, SORT_STRING);
?>
<ul class="directory-tree treeselect">
    <?php foreach ($this->mediaFiles as $key => $value) : ?>
        <?php if (is_array($value)) : ?>
            <li class="folder-select">
                <a class="folder-url" data-id="<?php echo base64_encode($key); ?>" href="" data-base="media">
                    <span class="icon-folder icon-fw" aria-hidden="true"></span>
                    <?php $explodeArray = explode('/', rtrim($key, '\\'));
                    echo $this->escape(end($explodeArray)); ?>
                </a>
                <?php echo $this->mediaFolderTree($value); ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
