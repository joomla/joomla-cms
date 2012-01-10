<?php
/**
 * @version		$Id: default.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
?>
<table class="adminform">
	<tr>
		<td width="55%" valign="top">
			<div id="cpanel">
                            <div style=" overflow: hidden;">
			<div style="float:left;">
                                <div class="icon">
                                    <a href="index.php?option=com_fieldsattach&view=fieldsattachgroups">
                                        <img src="components/com_fieldsattach/images/groups.png" alt="Groups"  />
                                        <span><?php echo JText::_( 'Groups' );?></span>
                                    </a>
                                </div>
                        </div>
                        <div style="float:left;">
                                <div class="icon">
                                    <a href="index.php?option=com_fieldsattach&view=fieldsattachunidades">
                                        <img src="components/com_fieldsattach/images/units.png" alt="Fields"  />
                                        <span><?php echo JText::_( 'Fields' );?></span>
                                    </a>
                                </div>
                        </div>
                        <div style="float:left;">
                                <div class="icon">
                                    <a href="index.php?option=com_fieldsattach&view=fieldsattachdisplay">
                                        <img src="components/com_fieldsattach/images/help.png" alt="Help"  />
                                        <span><?php echo JText::_( 'FrontEnd display' );?></span>
                                    </a>
                                </div>
                        </div>
                             
</div>
                               <!-- SUPPORT FORUM-->
                            <div style="width:85%;  overflow: hidden; border: #ccc 1px solid; padding: 10px; margin:30px 10px;">
                                 <img src="components/com_fieldsattach/images/gssupport" alt="Support" style="float:left; margin-right: 10px;"/>
                                 <div style="margin:20px   0px ;"><strong><?php echo JText::_( 'Do you have a problem or suggestion?' );?></strong><br />
                                 <?php echo JText::_( 'You can visit the <a href="http://www.percha.com/forum/index/5-fieldsattach-for-joomla-17.html" target="_blank">forum support</a>, and help me to improve the component.' );?></div>

                            </div>
                            <!-- REVIEW-->
                            <div style="width:85%;  overflow: hidden; border: #ccc 1px solid; padding: 10px; margin:30px 10px;">
                                 <img src="components/com_fieldsattach/images/smile.jpg" alt="" style="float:left; margin-right: 10px;"/>
                                 <div style="margin:20px   0px ;"><strong><?php echo JText::_( 'Do you like fieldsattach?' );?></strong><br />
                                 <?php echo JText::_( 'Help me width your review or your vote on the official <a href="http://extensions.joomla.org/extensions/news-production/content-construction/18564?qh=YToxOntpOjA7czoxMjoiZmllbGRzYXR0YWNoIjt9" target="_blank">Joomla Extensions Directory</a> website.' );?></div>
                               
                            </div>
                           <!-- PAYPAL-->
                            <div style="width:85%;  overflow: hidden; border: #ccc 1px solid; padding: 10px; margin:30px 10px;">
                                 <img src="components/com_fieldsattach/images/paypal.png" alt="" style="float:left; margin-right: 10px;"/>
                                 <div style="margin:20px   0px ;"><strong><?php echo JText::_( 'Do you pay me a coffee?' );?></strong><br />
                                 <?php echo JText::_( '<a href="http://www.percha.com/forum" target="_blank">Thanks</a>.' );?></div>

                            </div>



			</div>

		</td>

		<td width="45%" valign="top">
			<div style="border:1px solid #ccc;background:#fff;margin:15px;padding:15px">
			<div style="float:right;margin:10px;">
				<img src="components/com_fieldsattach/images/logo.gif" alt="Percha.com"  /></div>
			<h3>Version</h3>
			<p>2.6 beta</p>

			<h3>Copyright</h3>
			<p>© 2009 - 2012 Cristian Grañó Reder<br />
			<a href="http://www.percha.com/" target="_blank">www.percha.com</a></p>

			<h3>License</h3>
			<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>
			<p>&nbsp;</p> 
			</div>
		</td>
	</tr>
</table>
