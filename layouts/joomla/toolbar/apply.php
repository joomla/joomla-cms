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

if (preg_match('/Joomla.submitbutton/', $displayData['doTask']))
{
	$ctrls = str_replace("Joomla.submitbutton('", '', $displayData['doTask']);
	$ctrls = str_replace("')", '', $ctrls);
	$ctrls = str_replace(";", '', $ctrls);

	$options = array('task' => $ctrls);
	Factory::getDocument()->addScriptOptions('keySave', $options);
}

$id       = isset($displayData['id']) ? $displayData['id'] : '';
$doTask   = $displayData['doTask'];
$class    = $displayData['class'];
$text     = $displayData['text'];
$btnClass = $displayData['btnClass'];
?>
<button<?php echo $id; ?> onclick="<?php echo $doTask; ?>" class="<?php echo $btnClass; ?>">
	<span class="<?php echo trim($class); ?>"></span>
	<?php echo $text; ?>
</button>
