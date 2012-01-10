<?php
/**
 * models/cpanel/tmpl/default.php
 *
 * @package		J2XMLImporter
 * @subpackage	com_j2xmlimporter
 * @version		1.6.0
 * @since		File available since Release v1.5.3
 *
 * @author		Helios Ciancio <info@alikonweb.it>
 * @link		http://www.alikonweb.it
 * @copyright	Copyright (C) 2010 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XMLImporter is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');
JHTML::_('behavior.tooltip');
jimport('joomla.language.language');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'version.php');

?>
<table width='100%'>
    <tr>
        <td width='55%' class='adminform' valign='top'>
		<div id='cpanel'>
<?php 
     echo JText::_('Control Cockpit');
		$link = 'index.php?option=com_plugins';
   	$this->_quickiconButton($link, 'icon-48-plugin.png', JText::_('Alikonweb Plugin MANAGER'));
?>
		</div>
        <div class='clr'></div>
        </td>
		<td valign='top' width='45%' style='padding: 7px 0 0 5px'>
			<?php
			echo $this->pane->startPane('pane');
			
			$title = JText::_('Welcome_to_J2XMLImporter');
			echo $this->pane->startPanel('AA4J', 'welcome');
			?>
			<table class='adminlist'>
			<tr>
				<td colspan='2'>
					<p><?php echo JText::_('COM_J2XMLIMPORTER_DESCRIPTION')?></p>
				</td>
				<td rowspan='4' style="text-align:center">
					<a href='http://www.alikonweb.it/'>
					<img src='components/com_j2xmlimporter/assets/images/j2xmlimporter.png' width='110' height='110' alt='J2XMLImporter' title='J2XMLImporter' align='middle' border='0'>
					</a>
				</td>
			</tr>
			<tr>
				<td width='25%'>
					<?php echo JText::_('Version'); ?>
				</td>
				<td width='45%'>
					<?php  echo aa4jVersion::getLongVersion(); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Copyright'); ?>
				</td>
				<td>
					<a href='http://www.alikonweb.it' target='_blank'>&copy; 2005-2011 Alikonweb <img src='components/com_j2xmlimporter/assets/images/alikonweb.png' alt='alikonweb.it' title='alikonweb.it' border='0'></a>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('License'); ?>
				</td>
				<td>
					<a href='http://www.gnu.org/licenses/gpl-3.0.html' target='_blank'>GNU GPL v3</a>
				</td>
			</tr>
			</table>
			<?php
			echo $this->pane->endPanel();
			
			$title = JText::_('Status');
			echo $this->pane->startPanel($title, 'status');
			?>
			<table class='adminlist'>
			<tr>
				<td>
					<?php echo JText::_('Alikonweb - Ajax Captcha');?>
				</td>
				<td>
					<?php if (JPluginHelper::isEnabled('alikonweb', 'alikonweb.captchabot')){	
					  echo "<img src='templates/bluestork/images/admin/tick.png'  alt='status' title='statsus' align='middle' border='0'>";
					}else{	
						echo "<img src='templates/bluestork/images/admin/publish_x.png'  alt='status' title='statsus' align='middle' border='0'>";
					}
					?>
				</td>
			</tr>		
			<tr>
				<td>
					<?php echo JText::_('Alikonweb - Detector'); 				
	        ?>
				</td>
				<td>
					<?php if (JPluginHelper::isEnabled('alikonweb', 'alikonweb.detector')){	
					  echo "<img src='templates/bluestork/images/admin/tick.png'  alt='status' title='statsus' align='middle' border='0'>";
					}else{	
						echo "<img src='templates/bluestork/images/admin/publish_x.png'  alt='status' title='statsus' align='middle' border='0'>";
					}
					?>
				</td>
			</tr>	
			<tr>
				<td>
					<?php echo JText::_('Contact - Detector'); 				
	        ?>
				</td>
				<td>
					<?php if (JPluginHelper::isEnabled('contact', 'detector')){	
					  echo "<img src='templates/bluestork/images/admin/tick.png'  alt='status' title='statsus' align='middle' border='0'>";
					}else{	
						echo "<img src='templates/bluestork/images/admin/publish_x.png'  alt='status' title='statsus' align='middle' border='0'>";
					}
					?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('User - Detector'); 
	        ?>
				</td>
				<td>
					<?php if (JPluginHelper::isEnabled('user', 'detector')){	
					  echo "<img src='templates/bluestork/images/admin/tick.png'  alt='status' title='statsus' align='middle' border='0'>";
					}else{	
						echo "<img src='templates/bluestork/images/admin/publish_x.png'  alt='status' title='statsus' align='middle' border='0'>";
					}
					?>
				</td>
			</tr>
			</table>
      <?php
			echo $this->pane->endPanel();
			
			$title = JText::_('Support us');
			echo $this->pane->startPanel($title, 'supportus');
			?>
			<table class='adminlist'>
			<tr>
				<td>
					<p><?php echo JText::_('COM_J2XMLIMPORTER_MSG_DONATION1'); ?></p>
					<div style="text-align: center;">
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_donations">
							<input type="hidden" name="business" value="info@alikonweb.it">
							<input type="hidden" name="lc" value="en_US">
							<input type="hidden" name="item_name" value="alikonweb.it">
							<input type="hidden" name="currency_code" value="EUR">
							<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted">
							<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal secure payments.">
							<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
					</div>
					<p><?php echo JText::_('COM_J2XMLIMPORTER_MSG_DONATION2'); ?></p>
				</td>
			</tr>
			</table>
			<?php 
			echo $this->pane->endPanel();
			
			echo $this->pane->endPane();
			?>
		</td>
    </tr>
</table>
<form action="index.php" method="post" name="adminForm">
	<input type="hidden" name="option" value="com_j2xmlimporter" />
	<input type="hidden" name="c" value="website" />
	<input type="hidden" name="view" value="cpanel" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>
