<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$module  = $displayData['module'];

static $css = false;

if (!$css)
{
	$css = true;
	$doc = Factory::getDocument();

	$doc->addStyleDeclaration('.mod-preview-info { padding: 2px 4px 2px 4px; border: 1px solid black; position: absolute; background-color: white; color: red;}');
	$doc->addStyleDeclaration('.mod-preview-wrapper { background-color:#eee; border: 1px dotted black; color:#700;}');
}
?>
<div class="mod-preview">
	<div
		class="mod-preview-info"><?php echo 'Position: ' . $module->position . ' [ Style: ' . $module->style . ' ]'; ?></div>
	<div class="mod-preview-wrapper">
		<?php echo $module->content; ?>
	</div>
</div>
