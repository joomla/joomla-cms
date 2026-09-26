<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var \Joomla\Component\Templates\Administrator\View\Template\HtmlView $this */

ksort($this->files, SORT_STRING);
?>

<ul class="directory-tree treeselect">
    <?php foreach ($this->files as $key => $value) : ?>
        <?php if (is_array($value)) : ?>
            <li class="folder-select">
                <a class="folder-url" data-id="<?php echo base64_encode($key); ?>" href="" data-base="template">
                    <span class="icon-folder icon-fw" aria-hidden="true"></span>
                    <?php $explodeArray = explode('/', rtrim($key, '\\'));
                    echo $this->escape(end($explodeArray)); ?>
                </a>
                <?php echo $this->folderTree($value); ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
