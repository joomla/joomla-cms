Feature: Users Frontend
  In order to manage users account in the web
  As a user
  I need to check user login and registration in joomla! CMS

  Background:
    When I Login into Joomla administrator with username "admin" and password "admin"
    And I see the administrator dashboard

  Scenario: Create user from frontend (index.php?option=com_users)
    Given that user registration is enabled
    And there is no user with Username "patel" or Email "patel@gmail.com"
    When I press on the link "Create an account"
    And I create a user with fields Name "patel", Uaername "patel", Password "patel" and Email "patel@gmail.com"
    And I press the "Register" button
    Then I should see "Could not instantiate mail function." message

  Scenario: check the created user in the backend
    Given I am on the User Manager page
    And I search the user with user name "patel"
    Then I should see the user "patel"

  Scenario: User can not login, if the account has not been activated
    Given A not yet activated user with username "patel" exists
    And I am on a frontend page with a login module
    When I enter username "patel" and password "patel" into the login module
    And I press on "Log in"
    Then I should see the "Login denied! Your account has either been blocked or you have not activated it yet." warning

  Scenario: Check if block and activation are working
    Given I am on the User Manager page
    When I unblock the user "patel"
    And I activate the user "patel"
    And I login with user "patel" with password "patel" in frontend
    Then I should see the message "Hi patel,"

  Scenario: Profile changes done in the frontend are available in the backend
    Given I am logged in into the frontend as user "patel" with password "patel"
    When I press on the "Edit Profile" button
    And I change the name to "patidar"
    And I press on "Submit" button
    And I should see "Profile successfully saved." message
    And I am on the User Manager page
    And I search the user with name "patidar"
    Then I should see the name "patidar"

  Scenario: Test last login date
    Given Needs to user "patel" logged in at least once
    When I login as a super admin from backend
    Then I should see last login date for "patidar"
