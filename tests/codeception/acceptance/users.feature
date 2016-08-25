Feature: users
  In order to manage users in the web
  As an owner
  I need to create edit block unblock and delete user

  Background:
    When I Login into Joomla administrator with username "admin" and password "admin"
    And I see the administrator dashboard

  Scenario: Verify available tabs in com_users
    Given There is an user link
    When I see the user edit view tabs
    Then I check available tabs "Account Details", "Assigned User Groups" and "Basic Settings"

  Scenario: Create a add new user
    Given There is a add user link
    When I create new user with fields Name "register", Login Name "register", Password "register" and Email "register@example.com"
    And I Save the user
    Then I should see the user "register" is created

  Scenario: Edit user
    Given I search and select the user with user name "register"
    When I set name as an "Editor" and User Group as "Editor"
    And I Save the user
    Then I should see the user "Editor" is created

  Scenario: Block a User
    Given I have a user with user name "register"
    When I block the user
    Then I should see the user "register" is now blocked

  Scenario: Unblock user
    Given I have a blocked user with user name "register"
    When I unblock the user
    Then I should see the user "register" is now unblocked

  Scenario: Delete user
    Given I have a user with user name "Editor"
    When I Delete the user "Editor"
    Then I should see "No Matching Results" for deleted user "Editor"

  Scenario: Create super admin and login into the backend
    Given There is a add user link
    And  I fill a super admin with fields Name "User One", Login Name "user1", Password "pass1", and Email "user1@example.com"
    When I set assigned user group as an Administrator
    And I Save the user
    Then Login in backend with username "User One" and password "pass1"

  Scenario: Create User without username fails
    Given There is a add user link
    When I don't fill Login Name but fulfill remaining mandatory fields: Name "User Two", Password "pass2" and Email "user2@example.com"
    And I Save the user
    Then I see the title "Users: New"
    But I see the alert error "Invalid field:  Login Name"

  Scenario: Create group
    Given There is a add new group link
    When I fill Group Title as a "Group One"
    And I save the Group
    Then I should see the group "Group One" is created

  Scenario: Edit group
    Given I search and select the Group with name "Group One"
    And I set group Title as a "Group Two"
    When I save the Group
    Then I should see the group "Group Two" is created

  Scenario: Delete Group
    Given I search and select the Group with name "Group Two"
    When I Delete the Group "Group Two"
    Then I should see "No Matching Results" for deleted user "Group Two"

  Scenario: Create ACL level
    Given There is a add viewing access level link
    When I fill Level Title as a "Acl One" and set Access as a public
    And I save the Access Level
    Then I should see the access level "Acl One" is created

  Scenario: Edit ACL
    Given I search and select the Access Level with name "Acl One"
    And I set Access Level title as a "Acl Two"
    When I save the Access Level
    Then I should see the access level "Acl Two" is created

  Scenario: Delete ACL
    Given I search and select the Access Level with name "Acl Two"
    When I Delete the Access level "Acl Two"
    Then I should see "No Matching Results" for deleted user "Acl Two"

  Scenario: User settings (Allow user registration)
    Given There is an user link
    And I goto the option setting
    When I set Allow User Registration as a yes
    And I save the setting
    Then I should be see the link Create an account in frontend
