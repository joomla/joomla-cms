<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2019 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');

class com_sppagebuilderInstallerScript {

  /**
  * method to uninstall the component
  *
  * @return void
  */

  public function uninstall($parent) {
    $db = JFactory::getDBO();
    $status = new stdClass;
    $status->modules = array();
    $manifest = $parent->getParent()->manifest;

    // Uninstall Plugins
    $plugins = $manifest->xpath('plugins/plugin');
    foreach ($plugins as $plugin) {
      $name = (string)$plugin->attributes()->name;
      $group = (string)$plugin->attributes()->group;

      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      $query->select($db->quoteName(array('extension_id')));
      $query->from($db->quoteName('#__extensions'));
      $query->where($db->quoteName('type') . ' = '. $db->quote('plugin'));
      $query->where($db->quoteName('element') . ' = '. $db->quote($name));
      $query->where($db->quoteName('folder') . ' = '. $db->quote($group));
      $db->setQuery($query);
      $extensions = $db->loadColumn();

      if (count((array) $extensions)) {
        foreach ($extensions as $id) {
          $installer = new JInstaller;
          $result = $installer->uninstall('plugin', $id);
        }
        $status->plugins[] = array('name' => $name, 'result' => $result);
      }
    }

    // Uninstal Modules
    $modules = $manifest->xpath('modules/module');
    foreach ($modules as $module)
    {
      $name = (string)$module->attributes()->module;
      $client = (string)$module->attributes()->client;
      $db = JFactory::getDBO();
      if($db->getServerType() == 'postgresql'){
        $query = "SELECT extension_id FROM #__extensions WHERE type='module' AND element = ".$db->Quote($name)."";
      } else {
        $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = ".$db->Quote($name)."";
      }
      $db->setQuery($query);
      $extensions = $db->loadColumn();
      if (count((array) $extensions))
      {
        foreach ($extensions as $id)
        {
          $installer = new JInstaller;
          $result = $installer->uninstall('module', $id);
        }
        $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
      }
    }
  }


  /**
  * method to run before an install/update/uninstall method
  *
  * @return void
  */

  public function preflight($type, $parent) {
    // Remove Free Updater
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $conditions = array($db->quoteName('location') . ' = ' . $db->quote('http://www.joomshaper.com/updates/com-sp-page-builder-free.xml'));
    $query->delete($db->quoteName('#__update_sites'));
    $query->where($conditions);
    $db->setQuery($query);
    $db->execute();
  }


  /**
  * method to run after an install/update/uninstall method
  *
  * @return void
  */

  public function postflight($type, $parent) {
    $db = JFactory::getDBO();
    $status = new stdClass;
    $status->modules = array();
    $src = $parent->getParent()->getPath('source');
    $manifest = $parent->getParent()->manifest;

    // Install Plugins
    $plugins = $manifest->xpath('plugins/plugin');
    foreach ($plugins as $plugin) {
      $name = (string)$plugin->attributes()->name;
      $group = (string)$plugin->attributes()->group;
      $path = $src . '/plugins/' . $group . '/' . $name;

      $installer = new JInstaller;
      $result = $installer->install($path);

      if ($result) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $fields = array( $db->quoteName('enabled') . ' = 1' );

        $conditions = array(
          $db->quoteName('type') . ' = ' . $db->quote('plugin'),
          $db->quoteName('element') . ' = ' . $db->quote($name),
          $db->quoteName('folder') . ' = ' . $db->quote($group)
        );

        $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
      }
    }

    // Install Modules
    $modules = $manifest->xpath('modules/module');
    foreach ($modules as $module) {
      $name = (string)$module->attributes()->module;
      $client = (string)$module->attributes()->client;
      $path = $src . '/modules/' . $client . '/' . $name;
      $position = (isset($module->attributes()->position) && $module->attributes()->position) ? (string)$module->attributes()->position : '';
      $ordering = (isset($module->attributes()->ordering) && $module->attributes()->ordering) ? (string)$module->attributes()->ordering : 0;

      $installer = new JInstaller;
      $result = $installer->install($path);

      if($client == 'administrator') {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $fields = array();

        $fields[] = $db->quoteName('published') . ' = 1';

        if($position) {
          $fields[] = $db->quoteName('position') . ' = ' . $db->quote($position);
        }

        if($ordering) {
          $fields[] = $db->quoteName('ordering') . ' = ' . $db->quote($ordering);
        }

        $conditions = array(
          $db->quoteName('module') . ' = ' . $db->quote($name)
        );

        $query->update($db->quoteName('#__modules'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();

        // Retrive ID
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id')));
        $query->from($db->quoteName('#__modules'));
        $query->where($db->quoteName('module') . ' = ' . $db->quote($name));
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        // New
        if($id) {
          try{
            $db = JFactory::getDbo();
            $obj = new stdClass();
            $obj->moduleid = $id;
            $obj->menuid = 0;
            $db->insertObject('#__modules_menu', $obj);
          } catch(Exception $e){
            // ignore it
          }
        }
      }
    }

    if ($type == 'uninstall') {
      return true;
    } ?>

    <style type="text/css">
    .sppb-installation-wrap{
      padding: 40px;
      overflow: hidden;
      background-color: #0080FE;
      color: #fff;
      font-size: 16px;
      line-height: 26px;
      box-sizing: border-box;
      margin-bottom: 30px;
    }
    .sppb-installation-wrap .sppb-installation-left {
      float: left;
      width: 128px;
      height: 128px;
      line-height: 128px;
      text-align: center;
      margin-right: 15px;
      background-color: #fff;
      border-radius: 3px;
      box-shadow: 0 0 5px rgba(0,0,0,0.15);
    }
    .sppb-installation-wrap .sppb-installation-left img{
      display: inline-block;
    }
    .sppb-installation-wrap .sppb-installation-texts p{
      margin-bottom: 26px;
    }
    .sppb-installation-wrap .sppb-installation-texts h2{
      font-size: 24px;
      vertical-align: middle;
    }
    .sppb-installation-wrap .sppb-installation-texts h2 span{
      font-size: 16px;
      color: rgba(255,255,255,0.88);
      border-left: 1px solid rgba(255,255,255, 0.45);
      padding-left: 20px;
      margin-left: 20px;
      vertical-align: middle;
    }
    .sppb-installation-wrap .sppb-installation-footer{
      margin-top: 60px;
    }
    
    .sppb-installation-wrap .sppb-installation-footer a{
      margin-right: 10px;
    }
    
    .sppb-installation-wrap .sppb-installation-jed{
      background-color: #03E16D;
      padding: 20px;
      border-radius: 3px;
    }
    
    .sppb-installation-wrap .sppb-installation-jed span{
        display: block;
        font-size: 36px;
        float: left;
        width: 36px;
        height: 50px;
        line-height: 50px;
        margin-right: 15px;
    }
    
    .sppb-installation-wrap .sppb-installation-jed h3{
      margin-top: 0;
    }
    
    .sppb-installation-wrap .sppb-installation-jed p{
      margin-bottom: 0;
    }
    
    .sppb-installation-wrap .sppb-installation-jed p a{
      color: #fff;
      text-decoration: underline;
      font-style: italic;
    }
    
    .sppb-installation-wrap .btn-sppb-custom{
      background-color: #fff;
      border: none;
      font-size: 14px;
      padding: 10px 15px;
      color: #0080FE;
      font-weight: 500;
      border-radius: 3px;
    }
    .sppb-installation-wrap .pagebuilder-social-links{
      margin-top: 30px;
    }
    .sppb-installation-wrap .pagebuilder-social-links a{
      color: #fff;
      font-size: 14px;
      text-decoration: none;
      margin-right: 20px;
    }
    </style>

    <link href="<?php echo JURI::base() . 'components/com_sppagebuilder/assets/css/font-awesome.min.css'; ?>" rel="stylesheet">

    <div class="sppb-installation-wrap row-fluid">
      <div class="span4 sppb-installation-left span2">
        <img src="<?php echo JURI::root(); ?>administrator/components/com_sppagebuilder/assets/img/icon.svg" alt="SP Page Builder" />
      </div> <!-- /.sppb-installation-left -->
      <div class="sppb-installation-right span8">
        <div class="sppb-installation-texts">
          <h2>SP Page Builder Lite <span>Version: 3.6.0</span></h2>
          <p>Trusted by <strong>400,000+</strong> people worldwide, SP Page Builder is an extremely powerful drag &amp; drop design system.<br/>
          Whether you're a beginner or a professional, you must love taking control over your website design.</p>
          <p>With SP Page Builder, you can build a unique, stunning and functional site without coding a single line.<br/>
          Using the tool, anyone can build a professional quality site in minutes.</p>
          <div class="sppb-installation-jed">
            <span class="fa fa-thumbs-o-up"></span>
            <h3>Rate us on JED</h3>
            <p>If you found this product useful for you then please rate this product on <a href="https://extensions.joomla.org/extension/sp-page-builder/" target="_blank">Joomla Extension Directory</a>.</p>
          </div>
        </div>
        <div class="sppb-installation-footer">
          <div class="pagebuilder-links">
            <a class="btn btn-sppb-custom" href="index.php?option=com_sppagebuilder&amp;task=page.add" target="_blank"><span class="fa fa-plus-circle"></span> Create a New Page</a>
            <a class="btn btn-sppb-custom" href="https://www.joomshaper.com/forums/categories/page-builder" target="_blank"><span class="fa fa-support"></span> Support Forum</a>
            <a class="btn btn-sppb-custom" href="#" target="_blank"><span class="fa fa-play-circle-o"></span> Videos</a>
            <a class="btn btn-sppb-custom" href="https://www.joomshaper.com/documentation/sp-page-builder/sp-page-builder-3" target="_blank"><span class="fa fa-book"></span> Documentation</a>
          </div>
          <div class="pagebuilder-social-links">
            <a href="https://www.facebook.com/joomshaper" target="_blank"><span class="fa fa-facebook-square"></span> Like us on FaceBook</a>
            <a href="https://twitter.com/joomshaper" target="_blank"><span class="fa fa-twitter-square"></span> Follow us on Twitter</a>
            <a href="https://www.youtube.com/user/joomshaper" target="_blank"><span class="fa fa-youtube"></span> Subscribe us on YouTube</a>
          </div>
        </div>
      </div> <!-- /.sppb-installation-right -->
    </div> <!-- /.sppb-installation-wrap -->
  <?php
  }
}
