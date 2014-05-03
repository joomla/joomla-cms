<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */
// no direct access
defined('_JEXEC') or die;
$site = JFactory::getConfig();
?>
<script type="text/javascript">
var wnd = null;

function winclose () {
    wnd.close()
}

window.addEvent('domready', function(){
    $('sendreg').addEvent('click', function () {
        var jparams = 'name=' + $('namereg').value + '&mailuser=' + $('namereg').value + '&email=<?php echo $site->get('mailfrom'); ?>&site=<?php echo $site->get('sitename'); ?>';
        wnd = window.open('http://www.jokte.org/administrator/components/com_regusjokte/views/reguser/tmpl/ajaxreg.php?'+jparams, 'Registro', 'width=490,height=350,location=no,toolbars=no,status=no,titlebar=no,menubar=no,scrollbars=no');
        setTimeout("winclose()",12000);
    });
});
</script>
<div style="clear: both;">
    <form action="<?php echo JRoute::_('index.php?option=com_installer&view=warnings'); ?>" method="post" name="adminForm" id="adminForm">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_INSTALLER_SUBMENU_JOKTE_REGISTER'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <label style="width: 200px;" title=""><?php echo JText::_('COM_INSTALLER_NAME_USER_REGISTER'); ?></label>
                    <input type="text" name="namereg" id="namereg" value="">
                </li>
                <li>
                    <label style="width: 200px;" title=""><?php echo JText::_('COM_INSTALLER_EMAIL_USER_REGISTER'); ?></label>
                    <input type="text" name="mailreg" id="mailreg" value="" >
                </li>
                <li>
                    <input type="button" name="sendreg" id="sendreg" value="<?php echo JText::_('COM_INSTALLER_SEND_REGISTER'); ?>" class="inputbox">
                </li>
            </ul>
        </fieldset>

        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
