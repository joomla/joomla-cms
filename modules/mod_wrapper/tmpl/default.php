<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('webcomponent', ['joomla-iframe' => 'com_wrapper/webcomponents/joomla-iframe.min.js'], ['version' => 'auto', 'relative' => true]);
?>
<joomla-iframe iframe-auto-height="<?php echo $load; ?>"
		iframe-name="<?php echo $target; ?>"
		iframe-src="<?php echo $url; ?>"
		iframe-width="<?php echo $width; ?>"
		iframe-height="<?php echo $height; ?>"
		iframe-scrolling="<?php echo $scroll; ?>"
		iframe-border="<?php echo $frameborder; ?>"
		iframe-title="<?php echo $title; ?>"
		iframe-class="wrapper">
	<noscript>
		<iframe name="<?php echo $target; ?>"
				id="blockrandom-<?php echo $id; ?>"
				title="<?php echo $name; ?>"
				src="<?php echo $url; ?>"
				width="<?php echo $width; ?>"
				height="<?php echo $height; ?>"
				scrolling="<?php echo $scroll; ?>"
				frameborder="<?php echo $frameborder; ?>"
				class="wrapper"></iframe>
	</noscript>
</joomla-iframe>
