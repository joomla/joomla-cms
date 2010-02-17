<?php

require_once 'SeleniumJoomlaTestCase.php';

class DoInstall extends SeleniumJoomlaTestCase
{
  function testDoInstall()
  {
  	echo("Starting Install\n");
	$this->setUp();
	$cfg = new SeleniumConfig();
    $this->open($cfg->path ."/installation/index.php");
    $this->click("link=Next");
    $this->waitForPageToLoad("30000");
    $this->click("link=Next");
    $this->waitForPageToLoad("30000");
    $this->click("link=Next");
    $this->waitForPageToLoad("30000");
    $this->type("jform_db_host", $cfg->db_host);
    $this->type("jform_db_user", $cfg->db_user);
    $this->type("jform_db_pass", $cfg->db_pass);
    $this->type("jform_db_name", $cfg->db_name);
    $this->click("jform_db_old0");
    $this->click("link=Next");
    $this->waitForPageToLoad("30000");
    $this->click("link=Next");
    $this->waitForPageToLoad("30000");
    $this->type("jform_site_name", $cfg->site_name);
    $this->type("jform_admin_user", $cfg->username);
    $this->type("jform_admin_email", $cfg->admin_email);
    $this->type("jform_admin_password", $cfg->password);
    $this->type("jform_admin_password2", $cfg->password);
    $this->click("instDefault");
    sleep(5);
    $this->click("link=Next");
    $this->waitForPageToLoad("30000");
	$this->assertTrue(true);
  }
}
?>
