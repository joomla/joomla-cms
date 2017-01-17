<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$callStack = $displayData['callStack'];

if (empty($callStack))
{
    return;
}

$count = count($callStack);

?>

<div>
    <table class="table table-striped dbg-query-table">
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo JText::_('PLG_DEBUG_CALL_STACK_CALLER'); ?></th>
                <th><?php echo JText::_('PLG_DEBUG_CALL_STACK_FILE_AND_LINE'); ?></th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($callStack as $call) : ?>
                <?php
            	// Dont' back trace log classes.
            	if (isset($call['class']) && strpos($call['class'], 'JLog') !== false)
            	{
            		$count--;

            		continue;
            	}
                ?>

                <tr>
                    <td><?php echo $count; ?></td>
                    <td>
                        <?php
                    	if (isset($call['class']))
                    	{
                    		// If entry has Class/Method print it.
                    		echo htmlspecialchars($call['class'] . $call['type'] . $call['function']) . '()';
                    	}
                    	else if (isset($call['args']))
                		{
                			// If entry has args is a require/include.
                			echo htmlspecialchars($call['function']) . ' ' . JDebugHelper::formatLink($call['args'][0]);
                		}
                		else
                		{
                			// It's a function.
                			echo htmlspecialchars($call['function']) . '()';
                		}
                        ?>
                    </td>
                    <td>
                        <?php
                        // If entry doesn't have line and number the next is a call_user_func.
                        if (!isset($call['file']) && !isset($call['line']))
                        {
                            echo JText::_('PLG_DEBUG_CALL_STACK_SAME_FILE');
                        }
                        // If entry has file and line print it.
                        else
                        {
                            echo JDebugHelper::formatLink(htmlspecialchars($call['file']), htmlspecialchars($call['line']));
                        }
                        ?>

                    </td>
                </tr>

                <?php $count--; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!ini_get('xdebug.file_link_format')) : ?>
	<div>
        [<a href="https://xdebug.org/docs/all_settings#file_link_format" target="_blank">
            <?php echo JText::_('PLG_DEBUG_LINK_FORMAT'); ?>
        </a>]
    </div>
<?php endif; ?>
