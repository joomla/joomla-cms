<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
*/
class modules_html {

	/**
	* @param object
	* @param object
	* @param int -1=show without wrapper and title, -2=xhtml style
	*/
	function module( &$module, &$params, $style=0, $preview = false ) 
	{
		global $mainframe;
		
		if($preview) 
		{
			$doc =& $mainframe->getDocument();
			$css  = ".mod-preview-info { padding: 2px 4px 2px 4px; border: 1px solid black; position: absolute; background-color: white; color: red;opacity: .80; filter: alpha(opacity=80); -moz-opactiy: .80; }";
			$css .= ".mod-preview-wrapper { background-color:#eee;  border: 1px dotted black; color:#700; opacity: .50; filter: alpha(opacity=50); -moz-opactiy: .50;}";
			$doc->addStyleDeclaration($css);
			
			?>
			<div class="mod-preview">
			<div class="mod-preview-info"><?php echo $module->position.'['.$style.']' ?></div>
			<div class="mod-preview-wrapper">
			<?php
		}
		
		switch ( $style ) 
		{
			case -3:
			// allows for rounded corners
				modules_html::modoutput_rounded( $module, $params );
				break;

			case -2:
			// xhtml (divs and font headder tags)
				modules_html::modoutput_xhtml( $module, $params );
				break;

			case -1:
			// show a naked module - no wrapper and no title
				modules_html::modoutput_naked( $module, $params );
				break;

			case 1:
			// show a naked module - no wrapper and no title
				modules_html::modoutput_horz( $module, $params );
				break;

			case 0:
			default:
			// standard tabled output
				modules_html::modoutput_table( $module, $params );
				break;
		}

		if ( $params->get( 'rssurl' ) ) {
			// feed output
			// load RSS module file
			// kept for backward compatability
			// modules_html::modoutput_feed( $params );
			$path = JPATH_SITE .'/modules/mod_rss.php';
			if (file_exists( $path )) {
				require_once( $path );
			}
		}
		
		if($preview ) {
			?></div></div><?php
		}
	}

	/*
	* standard tabled output
	*/
	function modoutput_table( $module, &$params  ) {

		$moduleclass_sfx = $params->get( 'moduleclass_sfx' );

		?>
		<table cellpadding="0" cellspacing="0" class="moduletable<?php echo $moduleclass_sfx; ?>">
		<?php
		if ( $module->showtitle != 0 ) {
			?>
			<tr>
				<th valign="top">
					<?php echo $module->title; ?>
				</th>
			</tr>
			<?php
		}
		?>
		<tr>
			<td>
				<?php echo $module->content;?>
			</td>
		</tr>
		</table>
		<?php
	}

	/*
	* standard tabled output
	*/
	function modoutput_horz( $module, &$params  ) {
		?>
		<table cellspacing="1" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<?php
				modules_html::modoutput_table($module, $params);
				?>
			</td>
		</tr>
		</table>
		<?php
	}

	/*
	* show a naked module - no wrapper and no title
	*/
	function modoutput_naked( $module, &$params  ) {

		$moduleclass_sfx = $params->get( 'moduleclass_sfx' );

		echo $module->content;
	}

	/*
	* xhtml (divs and font headder tags)
	*/
	function modoutput_xhtml( $module, &$params ) {

		$moduleclass_sfx = $params->get( 'moduleclass_sfx' );

		?>
		<div class="moduletable<?php echo $moduleclass_sfx; ?>">
			<?php
			if ($module->showtitle != 0) {
				//echo $number;
				?>
				<h3>
					<?php echo $module->title; ?>
				</h3>
				<?php
			}

			echo $module->content;
			?>
		</div>
		<?php
	}

	/*
	* allows for rounded corners
	*/
	function modoutput_rounded( $module, &$params ) {

		$moduleclass_sfx = $params->get( 'moduleclass_sfx' );

		?>
		<div class="module<?php echo $moduleclass_sfx; ?>">
			<div>
				<div>
					<div>
						<?php
						if ($module->showtitle != 0) {
							echo "<h3>$module->title</h3>";
						}

						echo $module->content;

						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
?>