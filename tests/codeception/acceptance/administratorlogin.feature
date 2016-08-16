Feature: administrator login
  In order to manage my web application
  As an administrator
  I need to have a control panel

  Scenario: Login in Administrator
    When I Login into Joomla administrator with username "admin" and password "admin"
    Then I should see the administrator dashboard
