Feature: administrator login
  In order to manage my web application
  As administrator
  I need to be able to login

  Scenario: Successful login
    Given I am registered administrator named "admin"
    When I login into Joomla Administrator with username "admin" and password "admin"
    Then I should see administrator dashboard
