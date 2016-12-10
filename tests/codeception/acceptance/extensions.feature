Feature: extensions
  In order to install extension in Joomla
  As an administrator
  I need to install update uninstall extension in the extension manager

  Background:
    When I Login into Joomla administrator
    And I see the administrator dashboard

  Scenario: Test extension installation
    Given There is an extension install text
    When I install "weblinks" extension
    Then I should see the extension "weblinks" is installed

  Scenario: Test extension update
    Given There is an extension "weblinks" installed with update available
    When I install extension update
    Then I should see the extension "weblinks" is updated