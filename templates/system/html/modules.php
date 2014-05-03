<?php
/**
 * /**
 *Chromes predeterminados jokte! jxhtml,_menu
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	juuntos.org
 * @copyright   Copyleft.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * ******************************************************************************
 *Chromes predeterminados joomla (xhtml,none,rounded,horz,table,outline)
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;


/*
 * jxhtml (Variación de xhtml que nos permite mayor sensibilidad para agrupar los titulos h3 dentreo de una div personalizable)
 */
function modChrome_jxhtml($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
		<?php if ($module->showtitle != 0) : ?>
		<div class="tit">
		<h3><?php echo $module->title; ?></h3>
		</div>
		<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	<?php endif;
}

/*
 * jxmenu (Variación de xhtml que nos permite mayor asignar la extensión _menu , generando predeterminada la clase .moduletable_menu )
 */
function modChrome_jxmenu($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="moduletable_menu<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
		<?php if ($module->showtitle != 0) : ?>
		<div>
		<h3><?php echo $module->title; ?></h3>
		</div>
		<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	<?php endif;
}
/*
/*
 * none (output raw module content)
 */
function modChrome_none($module, &$params, &$attribs)
{
	echo $module->content;
}

/*
 * Module chrome that wraps the module in a table
 */
function modChrome_table($module, &$params, &$attribs)
{ ?>
	<table cellpadding="0" cellspacing="0" class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
	<?php if ($module->showtitle != 0) : ?>
		<tr>
			<th>
				<?php echo $module->title; ?>
			</th>
		</tr>
	<?php endif; ?>
		<tr>
			<td>
				<?php echo $module->content; ?>
			</td>
		</tr>
		</table>
	<?php
}

/*
 * Module chrome that wraps the tabled module output in a <td> tag of another table
 */
function modChrome_horz($module, &$params, &$attribs)
{ ?>
	<table cellspacing="1" cellpadding="0" width="100%">
		<tr>
			<td>
				<?php modChrome_table($module, $params, $attribs); ?>
			</td>
		</tr>
	</table>
	<?php
}

/*
 * xhtml (divs and font headder tags)
 */
function modChrome_xhtml($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
		<?php if ($module->showtitle != 0) : ?>
			<h3><?php echo $module->title; ?></h3>
		<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	<?php endif;
}

/*
 * Module chrome that allows for rounded corners by wrapping in nested div tags
 */
function modChrome_rounded($module, &$params, &$attribs)
{ ?>
		<div class="module<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
			<div>
				<div>
					<div>
						<?php if ($module->showtitle != 0) : ?>
							<h3><?php echo $module->title; ?></h3>
						<?php endif; ?>
					<?php echo $module->content; ?>
					</div>
				</div>
			</div>
		</div>
	<?php
}

/*
 * Module chrome that add preview information to the module
 */
function modChrome_outline($module, &$params, &$attribs)
{
	static $css=false;
	if (!$css)
	{
		$css=true;
		jimport('joomla.environment.browser');
		$doc = JFactory::getDocument();
		$browser = JBrowser::getInstance();
		$doc->addStyleDeclaration(".mod-preview-info { padding: 2px 4px 2px 4px; border: 1px solid black; position: absolute; background-color: white; color: red;}");
		$doc->addStyleDeclaration(".mod-preview-wrapper { background-color:#eee; border: 1px dotted black; color:#700;}");
		if ($browser->getBrowser()=='msie')
		{
			if ($browser->getMajor() <= 7) {
				$doc->addStyleDeclaration(".mod-preview-info {filter: alpha(opacity=80);}");
				$doc->addStyleDeclaration(".mod-preview-wrapper {filter: alpha(opacity=50);}");
			}
			else {
				$doc->addStyleDeclaration(".mod-preview-info {-ms-filter: alpha(opacity=80);}");
				$doc->addStyleDeclaration(".mod-preview-wrapper {-ms-filter: alpha(opacity=50);}");
			}
		}
		else
		{
			$doc->addStyleDeclaration(".mod-preview-info {opacity: 0.8;}");
			$doc->addStyleDeclaration(".mod-preview-wrapper {opacity: 0.5;}");
		}
	}
	?>
	<div class="mod-preview">
		<div class="mod-preview-info"><?php echo $module->position."[".$module->style."]"; ?></div>
		<div class="mod-preview-wrapper">
			<?php echo $module->content; ?>
		</div>
	</div>
	<?php
}
?>
