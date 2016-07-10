Feature: Users Frontend
  In order to manage users account in the web
  As a user
  I need to check user login and registration in joomla! CMS

  Background:
    Given I see the joomla! Home page

  Scenario: Create user from frontend (index.php?option=com_users)
    Given I click on the link "Create an account"
    And I create a user with fields Name "patel", Username "patel", Password "patel" and Email "patel@gmail.com"
    When I press the "Register"
    Then I see "Could not instantiate mail function." message
    And user is created

  Scenario: check the created user in the backend
    Given There is a user manager page in administrator
    When I search the user with user name "patel"
    Then I should see the user "prital"

  Scenario: Login with created user to assure it is blocked
    Given A newly created user "patel" with password "patel"
    When He press the "login"
    Then He should see the "Login denied! Your account has either been blocked or you have not activated it yet." warning

  Scenario: Check if block and activation are working
    Given There is a user manager page in administrator
    And I unblock the user "patel"
    And I activate the user "patel"
    When A login user "patel" with password "patel"
    Then He should see the message "Hi patel,"

  Scenario: Profile changes done in the frontend are available in the backend
    Given I am logged in into the frontend as user "patel"
    And I press the "Edit Profile" button
    And I change the name to "patidar"
    And I press the "submit" button
    When I login to the backend as "admin"
    And I go to the user manager page
    And I search the user with name "patidar"
    Then I should see the name "patidar"

  Scenario: Test last login date
    Given Needs to user "patel" logged in at least once
    When I login as a super admin from backend
    Then I should see last login date
