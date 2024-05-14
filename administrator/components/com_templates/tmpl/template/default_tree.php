<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

?>

<ul class="directory-tree treeselect">
    <?php foreach ($this->files as $key => $value) : ?>
        <?php if (is_array($value)) : ?>
            <?php
            $keyArray  = explode('/', rtrim($key, '\\'));
            $fileArray = explode('/', $this->fileName);
            $count     = 0;

            $keyArrayCount = count($keyArray);

            if (count($fileArray) >= $keyArrayCount) {
                for ($i = 0; $i < $keyArrayCount; $i++) {
                    if ($keyArray[$i] === $fileArray[$i]) {
                        $count++;
                    }
                }

                if ($count === $keyArrayCount) {
                    $class = 'folder show';
                } else {
                    $class = 'folder';
                }
            } else {
                $class = 'folder';
            }

            ?>
            <li class="<?php echo $class; ?>">
                <a class="folder-url" href="">
                    <span class="icon-folder icon-fw" aria-hidden="true"></span>&nbsp;<?php $explodeArray = explode('/', $key);
                    echo $this->escape(end($explodeArray)); ?>
                </a>
                <?php echo $this->directoryTree($value); ?>
            </li>
        <?php endif; ?>
        <?php if (is_object($value)) : ?>
            <li>
                <a class="file" href='<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $this->id . '&file=' . $value->id . '&isMedia=0'); ?>'>
                    <span class="icon-file-alt" aria-hidden="true"></span>&nbsp;<?php echo $this->escape($value->name); ?>
                </a>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
