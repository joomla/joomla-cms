<?php

class JPluginHelper
{
      public function getPlugin($type, $plugin = null)
      {
         require_once dirname(__FILE__).'/FakeAuthenticationPlugin.php';
         $testPlugin = new stdClass;
         $testPlugin->type = 'authentication';
         $testPlugin->name = 'fake';
         return array($testPlugin);
      }  
}