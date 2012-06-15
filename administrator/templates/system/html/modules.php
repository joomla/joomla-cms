<?php
/**
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/*
 * none (output raw module content)
 */
function modChrome_none($module, &$params, &$attribs)
{
	echo $module->content;
}

/*
 * xhtml (divs and font header tags)
 */
function modChrome_xhtml($module, &$params, &$attribs)
{
	$content = trim($module->content);
	if (!empty ($content)) : ?>
		<div class="module<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
		<?php if ($module->showtitle != 0) : ?>
			<h3><?php echo $module->title; ?></h3>
		<?php endif; ?>
			<?php echo $content; ?>
		</div>
	<?php endif;
}

/*
 * allows sliders
 */
function modChrome_sliders($module, &$params, &$attribs)
{
	$content = trim($module->content);
	if (!empty($content))
	{
		if ($params->get('automatic_title', '0')=='0') {
			echo JHtml::_('sliders.panel', $module->title, 'module'.$module->id);
		}
		elseif (method_exists('mod'.$module->name.'Helper', 'getTitle')) {
			echo JHtml::_('sliders.panel', call_user_func_array(array('mod'.$module->name.'Helper','getTitle'), array($params, $module)), 'module'.$module->id);
		}
		else {
			echo JHtml::_('sliders.panel', JText::_('MOD_'.$module->name.'_TITLE'), 'module'.$module->id);
		}
		echo $content;
	}
}

/*
 * allows tabs
 */
function modChrome_tabs($module, &$params, &$attribs)
{
	$content = trim($module->content);
	if (!empty($content))
	{
		if ($params->get('automatic_title', '0')=='0') {
			echo JHtml::_('tabs.panel', $module->title, 'module'.$module->id);
		}
		elseif (method_exists('mod'.$module->name.'Helper', 'getTitle')) {
			echo JHtml::_('tabs.panel', call_user_func_array(array('mod'.$module->name.'Helper', 'getTitle'), array($params)), 'module'.$module->id);
		}
		else {
			echo JHtml::_('tabs.panel', JText::_('MOD_'.$module->name.'_TITLE'), 'module'.$module->id);
		}
		echo $content;
	}
}
