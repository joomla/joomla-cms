<?php 
/**
 * @version		$Id: default.php 01 2012-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.CjLib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$config = CJLib::get_cjconfig(true);

$link = JRoute::_('index.php?option=com_cjlib&task=process&secret='.$config['cron_secret'], false, -1);
$link = str_replace('/administrator/','/', $link);

?>

<h1>CoreJoomla Framework API Library</h1>
<p><strong>Version:</strong> <?php echo CJLIB_VER;?></p>
<hr/>
<h3>Please update your <span style="color:red">download id</span> in CjLib Options. Click on Options button on toolbar above.</h3>
<p>For autoupdates to work, please make sure plg_installer_cjupdater plugin is enabled.</p>
<p>Visit <a href="http://www.corejoomla.com" target="_blank">www.corejoomla.com</a> to find your download id. Download ID will allow you to update corejoomla extensions automatically.</p>
<hr/>

<div id="cjlib-configuration" style="margin: 0 10px;">
	<div style="border: 1px solid #ccc; padding: 5px; ">
		<div><strong><?php echo JText::_('LBL_CRON_URL');?></strong></div>
		<div><?php echo JText::_('TXT_CJLIB_URL_HELP');?></div>
		<div style="margin: 10px 0;"><?php echo $link;?></div>
	</div>
	
	<form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_cjlib&task=save_config');?>" method="post">
		<fieldset class="adminform">
			<legend><?php echo JText::_('LBL_CRON_CONFIGURATION');?></legend>
			<p><?php echo JText::_('TXT_MANUAL_CRON_HELP');?></p>
			<ul class="adminformlist adminlist">
				<li>
					<label title="" class="hasTip required" for="manual-cron" id="manual-cron-lbl"><?php echo JText::_('LBL_ENABLE_MANUAL_CRON')?><span class="star">&nbsp;*</span></label>
					<input type="checkbox" class="inputbox required" value="1" <?php echo $config['manual_cron'] == 1 ? 'checked="checked"': ''?> id="manual-cron" name="manual_cron" aria-required="true" required="required">
				</li>
				<li>
					<label title="" class="hasTip required" for="cron-emails" id="cron-emails-lbl"><?php echo JText::_('LBL_CRON_EMAILS')?><span class="star">&nbsp;*</span></label>
					<input type="text" size="40" class="inputbox required" value="<?php echo $config['cron_emails']?>" id="cron-emails" name="cron_emails" aria-required="true" required="required">
				</li>
				<li>
					<label title="" class="hasTip required" for="cron-delay" id="cron-delay-lbl"><?php echo JText::_('LBL_CRON_DELAY')?><span class="star">&nbsp;*</span></label>
					<input type="text" size="40" class="inputbox required" value="<?php echo $config['cron_delay']?>" id="cron-delay" name="cron_delay" aria-required="true" required="required">
				</li>
			</ul>
		</fieldset>
		<input type="hidden" name="option" value="com_cjlib" />
		<input type="hidden" name="task" value="save" />
		<input type="hidden" name="view" value="default" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	
	<div><small>This product includes GeoLite data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a></small></div>
</div>