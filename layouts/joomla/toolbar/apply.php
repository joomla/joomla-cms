<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.core');
HTMLHelper::_('webcomponent', ['joomla-toolbar-button' => 'system/webcomponents/joomla-toolbar-button.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => true]);

$options = array('task' => $displayData['task']);
Factory::getDocument()->addScriptOptions('keySave', $options);

$id       = isset($displayData['id']) ? $displayData['id'] : '';
$class    = $displayData['class'];
$text     = $displayData['text'];
$btnClass = $displayData['btnClass'];
$group    = $displayData['group'];
$task     = '';
$list     = !empty($displayData['list'])     ? ' list-selection' : '';
$form     = !empty($displayData['form'])     ? ' form=' . $displayData['form'] . '"' : '';
$validate = !empty($displayData['validate']) ? ' form-validation' : '';

if (!empty($displayData['task']))
{
	$task = ' task="' . $displayData['task'] . '"';
}
else if (!empty($displayData['doTask']))
{
	$task = ' execute="' . $displayData['doTask'] . '"';
}
?>

<joomla-toolbar-button <?php echo $id.$task.$list.$form.$validate; ?> class="<?php echo $btnClass; ?>">
	<span class="<?php echo trim($class); ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</joomla-toolbar-button>
