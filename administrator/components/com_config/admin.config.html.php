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

		mosCommonHTML::loadOverlib();

		$tabs = new mosTabs(1);
		?>
		<form action="index2.php" method="post" name="adminForm">

		<div id="config-navigation">
			<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'navigation.html'); ?>
		</div>

		<div id="config-document">
			<div id="page-site">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_site.html'); ?>
			</div>

			<div id="page-server">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_server.html'); ?>
			</div>

			<div id="page-debug">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_debug.html'); ?>
			</div>

			<div id="page-database">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_database.html'); ?>
			</div>

			<div id="page-locale">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_locale.html'); ?>
			</div>

			<div id="page-mail">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_mail.html'); ?>
			</div>

			<div id="page-cache">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_cache.html'); ?>
			</div>

			<div id="page-user">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_user.html'); ?>
			</div>

			<div id="page-metadata">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_metadata.html'); ?>
			</div>

			<div id="page-statistics">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_statistics.html'); ?>
			</div>

			<div id="page-seo">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_seo.html'); ?>
			</div>

			<div id="page-content">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_content.html'); ?>
			</div>

			<div id="page-ftp">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_ftp.html'); ?>
			</div>

			<div id="page-feeds">
				<?php require_once(dirname(__FILE__).DS.'tmpl'.DS.'config_feeds.html'); ?>
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