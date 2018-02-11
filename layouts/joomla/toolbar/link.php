<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.core');
HTMLHelper::_('webcomponent', ['joomla-toolbar-button' => 'system/webcomponents/joomla-toolbar-button.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => true]);

$id     = isset($displayData['id']) ? $displayData['id'] : '';
$task   = '';
$class  = $displayData['class'];
$text   = $displayData['text'];

if (!empty($displayData['task']))
{
	$task = ' task="' . $displayData['task'] . '"';
}
else if (!empty($displayData['doTask']))
{
	$task = ' execute="location.href=\'' . $displayData['doTask'] . '\';"';
}

$margin = (strpos($task, 'index.php?option=com_config') === false) ? '' : 'ml-auto';

if ($margin):
?>
<div class="<?php echo $margin; ?>"></div>
<?php endif; ?>
<joomla-toolbar-button <?php echo $id.$task; ?> class="btn btn-outline-danger btn-sm">
	<span class="<?php echo trim($class); ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</joomla-toolbar-button>
