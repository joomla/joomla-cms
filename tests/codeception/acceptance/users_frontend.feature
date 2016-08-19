Feature: Users Frontend
  In order to manage users account in the web
  As a user
  I need to check user login and registration in joomla! CMS

  Background:
    When I Login into Joomla administrator with username "admin" and password "admin"
    And I see the administrator dashboard

  Scenario: Create user from frontend (index.php?option=com_users)
    Given that user registration is enabled
    And there is no user with Username "User Two" or Email "user2@example.com"
    When I press on the link "Create an account"
    And I create a user with fields Name "User Two", Username "user2", Password "pass2" and Email "user2@example.com"
    And I press the "Register" button
    Then I should see "Could not instantiate mail function." message

  Scenario: check the created user in the backend
    Given I am on the User Manager page
    And I search the user with user name "user2"
    Then I should see the user "user2"

  Scenario: User can not login, if the account has not been activated
    Given A not yet activated user with username "User Two" exists
    And I am on a frontend page with a login module
    When I enter username "user2" and password "pass2" into the login module
    And I press the "Log in" button
    Then I should see the "Login denied! Your account has either been blocked or you have not activated it yet." warning

  Scenario: Check if block and activation are working
    Given I am on the User Manager page
    When I unblock the user "User Two"
    And I activate the user "User Two"
    And I login with user "user2" with password "pass2" in frontend
    Then I should see the message "Hi User Two,"

  Scenario: Profile changes done in the frontend are available in the backend
    Given I am logged in into the frontend as user "user2" with password "pass2"
    When I press on the "Edit Profile" button
    And I change the name to "User Three"
    And I press the "Submit" button
    And I should see "Profile successfully saved." message
    And I am on the User Manager page
    And I search the user with name "User Three"
    Then I should see the name "User Three"

  Scenario: Test last login date
    Given Needs to user "User Two" logged in at least once
    When I login as a super admin from backend
    Then I should see last login date for "User Three"
