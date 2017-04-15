<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.system
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/*
 * none (output raw module content)
 */
function modChrome_none($module, &$params, &$attribs)
{
	echo $module->content;
}

/*
 * html5 (chosen html5 tag and font header tags)
 */
function modChrome_html5($module, &$params, &$attribs)
{
	$moduleTag      = $params->get('module_tag', 'div');
	$headerTag      = htmlspecialchars($params->get('header_tag', 'h3'), ENT_COMPAT, 'UTF-8');
	$bootstrapSize  = (int) $params->get('bootstrap_size', 0);
	$moduleClass    = $bootstrapSize != 0 ? ' span' . $bootstrapSize : '';

	// Temporarily store header class in variable
	$headerClass    = $params->get('header_class');
	$headerClass    = !empty($headerClass) ? ' class="' . htmlspecialchars($headerClass, ENT_COMPAT, 'UTF-8') . '"' : '';

	if (!empty ($module->content)) : ?>
		<<?php echo $moduleTag; ?> class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8') . $moduleClass; ?>">

		<?php if ((bool) $module->showtitle) :?>
			<<?php echo $headerTag . $headerClass . '>' . $module->title; ?></<?php echo $headerTag; ?>>
		<?php endif; ?>

			<?php echo $module->content; ?>

		</<?php echo $moduleTag; ?>>

	<?php endif;
}

/*
 * Module chrome that wraps the module in a table
 */
function modChrome_table($module, &$params, &$attribs)
{ ?>
	<table cellpadding="0" cellspacing="0" class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8'); ?>">
	<?php if ((bool) $module->showtitle) : ?>
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
 * xhtml (divs and font header tags)
 * With the new advanced parameter it does the same as the html5 chrome
 */
function modChrome_xhtml($module, &$params, &$attribs)
{
	$moduleTag      = $params->get('module_tag', 'div');
	$headerTag      = htmlspecialchars($params->get('header_tag', 'h3'), ENT_COMPAT, 'UTF-8');
	$bootstrapSize  = (int) $params->get('bootstrap_size', 0);
	$moduleClass    = $bootstrapSize != 0 ? ' span' . $bootstrapSize : '';

	// Temporarily store header class in variable
	$headerClass    = $params->get('header_class');
	$headerClass    = $headerClass ? ' class="' . htmlspecialchars($headerClass, ENT_COMPAT, 'UTF-8') . '"' : '';

	if (!empty ($module->content)) : ?>
		<<?php echo $moduleTag; ?> class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8') . $moduleClass; ?>">
			<?php if ((bool) $module->showtitle) : ?>
				<<?php echo $headerTag . $headerClass . '>' . $module->title; ?></<?php echo $headerTag; ?>>
			<?php endif; ?>
			<?php echo $module->content; ?>
		</<?php echo $moduleTag; ?>>
	<?php endif;
}

/*
 * Module chrome that allows for rounded corners by wrapping in nested div tags
 */
function modChrome_rounded($module, &$params, &$attribs)
{ ?>
		<div class="module<?php echo htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8'); ?>">
			<div>
				<div>
					<div>
						<?php if ((bool) $module->showtitle) : ?>
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
	static $css = false;

	if (!$css)
	{
		$css = true;
		$doc = JFactory::getDocument();

		$doc->addStyleDeclaration('.mod-preview-info { padding: 2px 4px 2px 4px; border: 1px solid black; position: absolute; background-color: white; color: red;}');
		$doc->addStyleDeclaration('.mod-preview-wrapper { background-color:#eee; border: 1px dotted black; color:#700;}');
	}
	?>
	<div class="mod-preview">
		<div class="mod-preview-info"><?php echo 'Position: ' . $module->position . ' [ Style: ' . $module->style . ']'; ?></div>
		<div class="mod-preview-wrapper">
			<?php echo $module->content; ?>
		</div>
	</div>
	<?php
}

/*
 * Html5-Flex
 * Same as html5 but allows title area to have styling separate from title tag styling
 * resulting in moduletable, moduleheader, and text background all separately stylable
 */
function modChrome_flex($module, &$params, &$attribs)
{
	$moduleTag = htmlspecialchars($params->get('module_tag', 'div'));
	
	// We use this alot so declare it 
	$moduleHeader = 'class="moduleheader';	
	
	// What tag do they want for wrapper 
	$headerTag = htmlspecialchars($params->get('header_tag', 'h3'));	
	
	// Get number of boostrap columns 
	$bootstrapSize = (int) $params->get('bootstrap_size', '0');	
	
	// Temporarily store header class in variable 
	$headerClass = htmlspecialchars($params->get('header_class'));	
	
	 // Create header class declaration 
	$headerClass = !empty($headerClass) ? $moduleHeader . $headerClass . '"' : $moduleHeader.'"';
	 
	 // Create module class declaration 
	$moduleClass = !empty($bootstrapSize) ? ' span' . $bootstrapSize . '' : '';
	
	// Get module suffix 
	$moduleClassSfx = htmlspecialchars($params->get('moduleclass_sfx')); 
	
	// Don't create html if no module content 
	if (!empty ($module->content))	
	{
		// Module wrapper 
		$html  = "<{$moduleTag} class=\"moduletable{$moduleClassSfx}{$moduleClass}\">"; 

		// Don't display title if not requested 
		if ((bool) $module->showtitle) 
		{
				// Create tag and wrapper 
				$html .= "<{$headerTag} {$headerClass}>";
			
					// Style the bar background for the title 
					$html .= "<span {$moduleHeader}_txtbg\">";	
	
						// Title text 
						$html .= $module->title;

					// Close Title Background Style 
					$html .= "</span>";	
				
				// Close Wrapper 
				$html .= "</{$headerTag}>";	
		}

		// Get content 
		$html .= $module->content; 
		
		 // Close module wrapper 
		$html .= "</{$moduleTag}>";

		// Display everything 
		echo $html;
	}
}
