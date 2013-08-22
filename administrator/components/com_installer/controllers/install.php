<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 */
class InstallerControllerInstall extends JControllerLegacy
{
	/**
	 * Install an extension.
	 *
	 * @return  void
	 * @since   1.5
	 */
	public function install()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('install');
		if ($model->install())
		{
			$cache = JFactory::getCache('mod_menu');
			$cache->clean();
			// TODO: Reset the users acl here as well to kill off any missing bits
		}

		$app = JFactory::getApplication();
		$redirect_url = $app->getUserState('com_installer.redirect_url');
		if (empty($redirect_url))
		{
			$redirect_url = JRoute::_('index.php?option=com_installer&view=install', false);
		} else
		{
			// wipe out the user state when we're going to redirect
			$app->setUserState('com_installer.redirect_url', '');
			$app->setUserState('com_installer.message', '');
			$app->setUserState('com_installer.extension_message', '');
		}
		$this->setRedirect($redirect_url);
	}
	
	public function installfromweb() {
		$model = $this->getModel('install');
		$html = $model->installfromweb();
		
		//echo $this->temphtml();
		echo $html;
		jexit();
	}
	
	public function temphtml() {
		$html = <<<EOT
<div class="row-fluid">
        <div class="span3">
          <div class="well sidebar-nav">
            <strong>Category Nav here</strong>
            <ul class="nav nav-list">
              <li class="nav-header">Sidebar</li>
              <li class="active"><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li class="nav-header">Sidebar</li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li class="nav-header">Sidebar</li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
            </ul>
          </div><!--/.well -->
        </div><!--/span-->
        <div class="span9">
          <div class="row-fluid">
            <div class="span4">
              <h2>JBolo!</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#" onclick="Joomla.installfromweb('')">Install »</a> <a class="btn" href="#">Details »</a></p>
            </div><!--/span-->
            <div class="span4">
              <h2>Akeeba Backup</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#" onclick="Joomla.installfromweb('')">Install »</a> <a class="btn" href="#">Details »</a></p>
            </div><!--/span-->
            <div class="span4">
              <h2>Advanced Module Manager</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#" onclick="Joomla.installfromweb('http://download.nonumber.nl/?ext=advancedmodulemanager', 'Advanced Module Manager')">Install »</a> <a class="btn" href="#">Details »</a></p>
            </div><!--/span-->
          </div><!--/row-->
        </div><!--/span-->
      </div>
EOT;
	
	return $html;
	}	
	
}
