<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$paths = $displayData['paths'];

?>
<?php if (empty($paths)) : ?>

    <p><?php echo JText::_('JNONE'); ?></p>

<?php else : ?>

    <ul>
        <?php foreach ($paths as /* $extension => */ $files) : ?>
			<?php foreach ($files as $file => $status) : ?>
				<li>

                <?php
                echo JText::_($status ? 'PLG_DEBUG_LANG_LOADED' : 'PLG_DEBUG_LANG_NOT_LOADED'),
                    ' : ',
                    JDebugHelper::formatLink($file);
                ?>

                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
	</ul>
    
<?php endif;
