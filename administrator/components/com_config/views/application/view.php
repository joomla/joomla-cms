<?php
/**
 * @version		$Id: view.php 10882 2008-08-31 17:55:14Z willebil $
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigApplicationView
{
	function showConfig(&$row, &$lists)
	{
		global $mainframe;

		// Load tooltips behavior
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.switcher');

		// Load component specific configurations
		$table = &JTable::getInstance('component');
		$table->loadByOption('com_users');
		$userparams = new JParameter($table->params, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_users'.DS.'config.xml');
		$table->loadByOption('com_media');
		$mediaparams = new JParameter($table->params, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_media'.DS.'config.xml');

		// Build the component's submenu
		$contents = '';
		$tmplpath = dirname(__FILE__).DS.'tmpl';
		ob_start();
		require_once($tmplpath.DS.'navigation.php');
		$contents = ob_get_contents();
		ob_end_clean();

		// Set document data
		$document = &JFactory::getDocument();
		$document->setBuffer($contents, 'modules', 'submenu');

		$document->addScriptDeclaration("
			document.switcher = null;
			window.addEvent('domready', function(){
			 	toggler = $('submenu')
			  	element = $('config-document')
			  	if (element) {
			  		document.switcher = new JSwitcher(toggler, element, {cookieName: toggler.getAttribute('class')});
			  	}
			});
		");

		// Load settings for the FTP layer
		jimport('joomla.client.helper');
		$ftp = &JClientHelper::setCredentialsFromRequest('ftp');
		?>
		<form action="index.php" method="post" name="adminForm">
		<?php if ($ftp) {
			require_once($tmplpath.DS.'ftp.php');
		} ?>
		<div id="config-document">
			<div id="page-site">
				<table class="noshow">
					<tr>
						<td width="65%">
							<?php require_once($tmplpath.DS.'config_site.php'); ?>
							<?php require_once($tmplpath.DS.'config_metadata.php'); ?>
						</td>
						<td width="35%">
							<?php require_once($tmplpath.DS.'config_seo.php'); ?>
						</td>
					</tr>
				</table>
			</div>
			<div id="page-system">
				<table class="noshow">
					<tr>
						<td width="60%">
							<?php require_once($tmplpath.DS.'config_system.php'); ?>
							<fieldset class="adminform">
								<legend><?php echo JText::_('User Settings'); ?></legend>
								<?php echo $userparams->render('userparams'); ?>
							</fieldset>
							<fieldset class="adminform">
								<legend><?php echo JText::_('Media Settings'); ?>
				<span class="error hasTip" title="<?php echo JText::_('Warning');?>::<?php echo JText::_('WARNPATHCHANGES'); ?>">
					<?php echo ConfigApplicationView::WarningIcon(); ?>
				</span>
								</legend>
								<?php echo $mediaparams->render('mediaparams'); ?>
							</fieldset>
						</td>
						<td width="40%">
							<?php require_once($tmplpath.DS.'config_debug.php'); ?>
							<?php require_once($tmplpath.DS.'config_cache.php'); ?>
							<?php require_once($tmplpath.DS.'config_session.php'); ?>
						</td>
					</tr>
				</table>
			</div>
			<div id="page-server">
				<table class="noshow">
					<tr>
						<td width="60%">
							<?php require_once($tmplpath.DS.'config_server.php'); ?>
							<?php require_once($tmplpath.DS.'config_locale.php'); ?>
							<?php require_once($tmplpath.DS.'config_ftp.php'); ?>
						</td>
						<td width="40%">
							<?php require_once($tmplpath.DS.'config_database.php'); ?>
							<?php require_once($tmplpath.DS.'config_mail.php'); ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="c" value="global" />
		<input type="hidden" name="live_site" value="<?php echo isset($row->live_site) ? $row->live_site : ''; ?>" />
		<input type="hidden" name="option" value="com_config" />
		<input type="hidden" name="secret" value="<?php echo $row->secret; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
		<?php
	}

	function WarningIcon()
	{
		global $mainframe;

		$tip = '<img src="'.JURI::root().'includes/js/ThemeOffice/warning.png" border="0"  alt="" />';

		return $tip;
	}
}
