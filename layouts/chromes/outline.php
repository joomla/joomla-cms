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
use Joomla\CMS\Language\Text;

$module  = $displayData['module'];

static $css = false;

if (!$css)
{
	$css = true;
	$doc = Factory::getDocument();

	$doc->addStyleDeclaration('
		.mod-preview {
			background: rgba(100,100,100,.08);
			box-shadow: 0 0 0 4px #f4f4f4, 0 0 0 5px rgba(100,100,100,.2);
			border-radius: 1px;
			margin: 8px 0;
		}
		.mod-preview-info {
			padding: 4px 6px;
			margin-bottom: 5px;
			font-family: Arial, sans-serif;
			font-size: .75rem;
			line-height: 1rem;
			color: white;
			background-color: #33373f;
			border-radius: 3px;
			box-shadow: 0 -10px 20px rgba(0,0,0,.2) inset;
		}
		.mod-preview-info span {
			font-weight: bold;
			color: #ccc;
		}
		.mod-preview-wrapper {
			margin-bottom: .5rem;
		}
		');
}
?>
<div class="mod-preview">
	<div class="mod-preview-info">
		<div class="mod-preview-position">
			<?php echo Text::sprintf('JGLOBAL_PREVIEW_POSITION', $module->position); ?>
		</div>
		<div class="mod-preview-style">
			<?php echo Text::sprintf('JGLOBAL_PREVIEW_STYLE', $module->style); ?>
		</div>
	</div>
	<div class="mod-preview-wrapper">
		<?php echo $module->content; ?>
	</div>
</div>
