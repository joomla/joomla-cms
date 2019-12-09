<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.system
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


// Bootstrap
function modChrome_bootstrap($module, $params, $attribs) {
	static $modulescount;
	global $modules;

	$modulescount = count(JModuleHelper::getModules($attribs['name']));
	$bootstrapSize = (int) $params->get('bootstrap_size', 0);
	$moduleClass = $bootstrapSize != 0 ? ' col-xs-' . $bootstrapSize : '';
	$headerTag = htmlspecialchars($params->get('header_tag', 'h3'));
	$headerClass = $params->get('header_class');
	if ($headerClass) {
		$headerClass = ' class="' . $headerClass . '"';
	}
	$name = '';

	if ($params->get('moduleclass_sfx')) {
		$modClassSfx = ' ' . $params->get('moduleclass_sfx');
	} else {
		$modClassSfx = '';
	}

	if (isset($attribs['name'])) {
		$name = $attribs['name'];
		if (isset($modules[$name])) {
			$modules[$name] += 1;
		} else {
			$modules[$name] = 1;
		}
	}

	if (!empty($module->content)) {
		$modules[$name] == 1;
		$counter = ($modules[$name]);

		echo '<' . $params->get('module_tag', 'div') . ' class="module' . $modClassSfx . ' ' . $moduleClass . '">';
		if ($module->content) {
			if ($module->showtitle) {
				echo '<div class="module-title">';
				echo '<' . $headerTag . $headerClass . '>' . $module->title . '</' . $headerTag . '>';
				echo '</div>';
			}
			echo '<div class="module-content">';
			echo $module->content;
			echo '</div>';
		}
		echo '</' . $params->get('module_tag', 'div') . '>';

		if ($counter == $modulescount) {
			echo '</div>';
		}
	}
}

// Standard style with headline horizontal on left
function modChrome_headline_left($module, &$params, &$attribs) {
	$moduleTag = $params->get('module_tag', 'div');
	$headerTag = htmlspecialchars($params->get('header_tag', 'h3'));
	$headerClass = $params->get('header_class');
	if ($headerClass) {
		$headerClass = ' class="' . $headerClass . '"';
	}
	if ($params->get('moduleclass_sfx')) {
		$modClassSfx = ' ' . $params->get('moduleclass_sfx');
	} else {
		$modClassSfx = '';
	}

	if (!empty($module->content)) :
		?>
		<<?php echo $moduleTag; ?> class="module<?php echo htmlspecialchars($modClassSfx); ?>">
		<div class="headline-inline left">
			<?php if ((bool) $module->showtitle) : ?>
				<div class="headline">
				<?php echo '<' . $headerTag . $headerClass . '>' . $module->title . '</' . $headerTag . '>'; ?>
				</div>
			<?php endif; ?>
			<div class="module-content">
				<?php echo $module->content; ?>
			</div>
			</<?php echo $moduleTag; ?>>
		</div>
		<?php
	endif;
}

// Standard style with headline horizontal on right
function modChrome_headline_right($module, &$params, &$attribs) {
	$moduleTag = $params->get('module_tag', 'div');
	$headerTag = htmlspecialchars($params->get('header_tag', 'h3'));
	$headerClass = $params->get('header_class');
	if ($headerClass) {
		$headerClass = ' class="' . $headerClass . '"';
	}
	if ($params->get('moduleclass_sfx')) {
		$modClassSfx = ' ' . $params->get('moduleclass_sfx');
	} else {
		$modClassSfx = '';
	}

	if (!empty($module->content)) :
		?>
		<<?php echo $moduleTag; ?> class="module<?php echo htmlspecialchars($modClassSfx); ?>">
		<div class="headline-inline right">
			<?php if ((bool) $module->showtitle) : ?>
				<div class="headline">
				<?php echo '<' . $headerTag . $headerClass . '>' . $module->title . '</' . $headerTag . '>'; ?>
				</div>
			<?php endif; ?>
			<div class="module-content">
				<?php echo $module->content; ?>
			</div>
			</<?php echo $moduleTag; ?>>
		</div>
		<?php
	endif;
}


function modChrome_well_small($module, &$params, &$attribs)
{
	$moduleTag     = htmlspecialchars( $params->get( 'module_tag', 'div' ), ENT_QUOTES, 'UTF-8' );
	$bootstrapSize = (int) $params->get( 'bootstrap_size', 0 );
	$moduleClass   = $bootstrapSize !== 0 ? ' span' . $bootstrapSize : '';
	$headerTag     = htmlspecialchars( $params->get( 'header_tag', 'h3' ), ENT_QUOTES, 'UTF-8' );
	$headerClass   = htmlspecialchars( $params->get( 'header_class', 'page-header' ), ENT_COMPAT, 'UTF-8' );

	if ( $module->content )
	{
		echo '<' . $moduleTag . ' class="well well-sm' . htmlspecialchars( $params->get( 'moduleclass_sfx' ), ENT_COMPAT, 'UTF-8' ) . $moduleClass . '">';

		if ( $module->showtitle )
		{
			echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
		}

		echo $module->content;
		echo '</' . $moduleTag . '>';
	}
}

function modChrome_well_normal($module, &$params, &$attribs)
{
	$moduleTag     = htmlspecialchars( $params->get( 'module_tag', 'div' ), ENT_QUOTES, 'UTF-8' );
	$bootstrapSize = (int) $params->get( 'bootstrap_size', 0 );
	$moduleClass   = $bootstrapSize !== 0 ? ' span' . $bootstrapSize : '';
	$headerTag     = htmlspecialchars( $params->get( 'header_tag', 'h3' ), ENT_QUOTES, 'UTF-8' );
	$headerClass   = htmlspecialchars( $params->get( 'header_class', 'page-header' ), ENT_COMPAT, 'UTF-8' );

	if ( $module->content )
	{
		echo '<' . $moduleTag . ' class="well ' . htmlspecialchars( $params->get( 'moduleclass_sfx' ), ENT_COMPAT, 'UTF-8' ) . $moduleClass . '">';

		if ( $module->showtitle )
		{
			echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
		}

		echo $module->content;
		echo '</' . $moduleTag . '>';
	}
}

function modChrome_well_large($module, &$params, &$attribs)
{
	$moduleTag     = htmlspecialchars( $params->get( 'module_tag', 'div' ), ENT_QUOTES, 'UTF-8' );
	$bootstrapSize = (int) $params->get( 'bootstrap_size', 0 );
	$moduleClass   = $bootstrapSize !== 0 ? ' span' . $bootstrapSize : '';
	$headerTag     = htmlspecialchars( $params->get( 'header_tag', 'h3' ), ENT_QUOTES, 'UTF-8' );
	$headerClass   = htmlspecialchars( $params->get( 'header_class', 'page-header' ), ENT_COMPAT, 'UTF-8' );

	if ( $module->content )
	{
		echo '<' . $moduleTag . ' class="well well-lg' . htmlspecialchars( $params->get( 'moduleclass_sfx' ), ENT_COMPAT, 'UTF-8' ) . $moduleClass . '">';

		if ( $module->showtitle )
		{
			echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
		}

		echo $module->content;
		echo '</' . $moduleTag . '>';
	}
}
