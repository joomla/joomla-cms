<?php

require_once 'SeleniumJoomlaTestCase.php';

class User0001Test extends SeleniumJoomlaTestCase
{

  function testCreateUser()
  {
  	print("Starting testCreateUser" . "\n");
  	$this->setUp();
  	$this->doAdminLogin();
  	$this->gotoAdmin();
  	print("Add new user" . "\n");
    $this->click("link=Add New User");
    $this->waitForPageToLoad("30000");
    $this->type("jform_name", "username1");
    $this->type("jform_username", "loginname1");
    $this->type("jform_password", "password1");
    $this->type("jform_password2", "password1");
    $this->type("jform_email", "email@example.com");
    $this->type("jform_profile_address1", "address1");
    $this->type("jform_profile_address2", "address2");
    $this->type("jform_profile_city", "city");
    $this->type("jform_profile_region", "region");
    $this->type("jform_profile_country", "country");
    $this->type("jform_profile_postal_code", "postal code");
    $this->type("jform_profile_phone", "phone");
    $this->type("jform_profile_website", "website");
    $this->click("1group_1");
    $this->click("//li[@id='toolbar-save']/a/span");
    $this->waitForPageToLoad("30000");
    print("Save and check that it exists" . "\n");
    $this->type("search", "username1");
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
    $this->assertEquals("username1", $this->getText("link=username1"));
    $this->assertEquals("loginname1", $this->getText("//div[@id='element-box']/div[2]/form/table/tbody/tr/td[3]"));
    $this->assertEquals("Public", $this->getText("//div[@id='element-box']/div[2]/form/table/tbody/tr/td[6]"));
    $this->assertEquals("email@example.com", $this->getText("//div[@id='element-box']/div[2]/form/table/tbody/tr/td[7]"));
	print("Delete the user" . "\n");
    $this->click("cb0");
    $this->click("//li[@id='toolbar-delete']/a/span");
    $this->waitForPageToLoad("30000");
    print("Check that user does not exist" . "\n");
    $this->assertFalse($this->isElementPresent("link=username1"));
    print("Finished user0001Test.php" . "\n");
  }
}

