Feature: users
  In order to manage users in the web
  As an owner
  I need to create edit block unblock and delete user

  Background:
    Given Joomla CMS is installed
    When Login into Joomla administrator with username "admin" and password "admin"
    Then I see administrator dashboard

  Scenario: Verify available tabs in com_users
    Given There is an user link
    When I see the user edit view tabs
    Then I check available tabs "Account Details", "Assigned User Groups" and "Basic Settings"

  Scenario: perform a add new user
    Given There is a add user link
    When I create new user with fields Name "register", Login Name "register", Password "register" and Email "register@gmail.com"
    Then I Save the  user
    And I see the "User successfully saved." message

  Scenario: Edit user
    Given I search and select the user with user name "register"
    When I set name as an "Editor" and User Group as "Editor"
    Then I Save the  user
    And I see the "User successfully saved." message

  Scenario: Block a User
    Given I have a user with user name "register"
    When I block the user
    Then I should see the user block message "User blocked."

  Scenario: Unblock user
    Given I have a blocked user with user name "register"
    When I unblock the user
    Then I should see the user unblock message "User enabled."

  Scenario: Delete user
    Given I have a user with user name "Editor"
    When I Delete the user "Editor"
    Then I confirm the user should have been deleted by getting the message "1 user successfully deleted."

  Scenario: Create super admin and login into the backend
    Given There is a add user link
    When  I create a super admin with fields Name "prital", Login Name "prital", Password "prital", and Email "prital@gmail.com"
    And I set assigned user group as an Administrator
    Then I Save the  user
    And Login in backend with username "prital" and password "prital"

  Scenario:create User without username fails
    Given There is a add user link
    When I don't fill Login Name but fulfill remaining mandatory fields: Name "piyu", Password "piyu" and Email "piyu@gmail.com"
    Then I Save the  user
    And I see the "Invalid field:  Login Name" alert error

  Scenario: Create group
    Given There is a add new group link
    When I fill Group Title as a "Gsoc"
    And I save the Group
    Then I should see the "Group successfully saved." message

  Scenario: Edit group
    Given I search and select the Group with name "Gsoc"
    And I set group Title as a "Gsoc_admin"
    When I save the Group
    Then I should see the "Group successfully saved." message

  Scenario: Delete Group
    Given I search and select the Group with name "Gsoc_admin"
    When I Delete the Group "Gsoc_admin"
    Then I confirm the group should have been deleted by getting the message "1 User Group successfully deleted."

  Scenario: Create ACL level
    Given There is a add viewing access level link
    When I fill Level Title as a "joomla" and set Access as a public
    And I save the Access Level
    Then I should be see the "Access level successfully saved." message

  Scenario:  Edit ACL
    Given I search and select the Access Level with name "joomla"
    And I set Access Level title as a "Gsoc_joomla"
    When I save Access Level
    Then I should be see the "Access level successfully saved." message

  Scenario: Delete ACL
    Given I search and select the Access Level with name "Gsoc_joomla"
    When I Delete the Access level "Gsoc_joomla"
    Then I confirm the  Access Level have been deleted by getting the message "1 View Access Level successfully removed."
