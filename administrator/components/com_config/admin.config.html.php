<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Config
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
* @subpackage Config
*/
class JConfigView
{
	function showConfig( &$row, &$lists, $option)
	{
		global $mainframe;

		$document =& $mainframe->getDocument();
		$document->addScript($mainframe->getBaseURL().'components/com_config/js/switcher.js');
		
		$contents = '';
		ob_start();
			require_once(dirname(__FILE__).DS.'tmpl'.DS.'navigation.html');
		$contents = ob_get_contents();
		ob_end_clean();
		
		$document->addGlobalVar('MODULE_SUBMENU', $contents);
		require_once(dirname(__FILE__).DS.'tmpl'.DS.'writeable.html');
		mosCommonHTML::loadOverlib();

		$tabs = new mosTabs(1);
		?>
		<form action="index2.php" method="post" name="adminForm">

		<div id="config-document">
			<div id="page-site">
				<table class="noshow">
					<tr>
						<td with="70%">
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_site.html'); ?>
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_metadata.html'); ?>
						</td>
						<td width="30%">
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_debug.html'); ?>
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_statistics.html'); ?>
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_seo.html'); ?>
						</td>
					</tr>
				</table>
			</div>

			<div id="page-user">
				<table class="noshow">
					<tr>
						<td>
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_user.html'); ?>
						</td>
					</tr>
				</table>
				
			</div>

			<div id="page-content">
				<table class="noshow">
					<tr>
						<td with="50%">
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_content.html'); ?>
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_feeds.html'); ?>
						</td>
						<td width="50%">
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_content2.html'); ?>
						</td>
					</tr>
				</table>
			</div>

			<div id="page-server">
				<table class="noshow">
					<tr>
						<td with="60%">
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_server.html'); ?>
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_locale.html'); ?>
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_cache.html'); ?>
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_ftp.html'); ?>
						</td>
						<td width="40%">
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_database.html'); ?>
							
							<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_mail.html'); ?>
						</td>
					</tr>
				</table>				
			</div>
		</div>
		<div class="clr"></div>
		<script> loadSwicther()</script>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="secret" value="<?php echo $row->secret; ?>" />
		<input type="hidden" name="multilingual_support" value="<?php echo $row->multilingual_support; ?>" />
	  	<input type="hidden" name="lang_site" value="<?php echo $row->lang_site; ?>" />
	  	<input type="hidden" name="lang_administrator" value="<?php echo $row->lang_administrator; ?>" />
	  	<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>
