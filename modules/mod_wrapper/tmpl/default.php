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

HtmlHelper::_('webcomponent', 'com_wrapper/webcomponents/joomla-iframe.min.js', ['version' => 'auto', 'relative' => true]);
?>
<joomla-iframe auto-height="<?php echo $load; ?>"
	name="<?php echo $target; ?>"
	title="<?php echo $ititle; ?>"
	src="<?php echo $url; ?>"
	width="<?php echo $width; ?>"
	height="<?php echo $height; ?>"
	scrolling="<?php echo $scroll; ?>"
	frameborder="<?php echo $frameborder; ?>"
	use-class="wrapper"
	no-frame-text="<?php echo Text::_('MOD_WRAPPER_NO_IFRAMES'); ?>">
	<noscript><iframe name="<?php echo $target; ?>"
				src="<?php echo $url; ?>"
				width="<?php echo $width; ?>"
				height="<?php echo $height; ?>"
				scrolling="<?php echo $scroll; ?>"
				frameborder="<?php echo $frameborder; ?>"
				class="wrapper"><?php echo Text::_('MOD_WRAPPER_NO_IFRAMES'); ?></iframe>
	</noscript>
</joomla-iframe>
