Feature: users
  In order to manage users in the web
  As an owner
  I need to create edit block unblock and delete user

  Background:
    Given Joomla CMS is installed
    When Login into Joomla administrator with username "admin" and password "admin"
    Then I see administrator dashboard

  Scenario: create a user
    Given There is a add user link
    When I fill mandatory fields for creating User
      | field             | value                    |
      | Name              | register                 |
      | Login Name        | register                 |
      | Password          | register                 |
      | Confirm Password  | register                 |
      | Email             | baldhapiyu@gmail.com     |
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

  Scenario: Verify available tabs in com_users
    Given There is a add/edit user link
    When I check available tabs
      |     tab              |
      | Account Details      |
      | Assigned User Groups |
      | Basic Settings       |

  Scenario: Create super admin and login into the backend
    Given There is a add user link
    When  I fill mandatory fields for creating User
      | field             | value                  |
      | Name              | prital                 |
      | Login Name        | prital                 |
      | Password          | prital                 |
      | Confirm Password  | prital                 |
      | Email             | baldhapiyu@gmail.com   |
    And I set assigned user group as an "Administrator"
    Then I Save the  user
    And I see the "User successfully saved." message

  Scenario: User without username fails
    Given There is a add user link
    When I fill mandatory fields for creating User but don't fill Login Name
      | field             | value                  |
      | Name              | piyu                   |
      | Password          | piyu                   |
      | Confirm Password  | piyu                   |
      | Email             | piyu@gmail.com         |
    Then I Save the  user
    And I see the "Invalid field:  Login Name" message

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
    Then I confirm the user should have been deleted by getting the message "1 User Group successfully deleted."

  Scenario: Create ACL level
    Given There is a add viewing access level link
    When I fill Level Title as a "joomla"
    And I save the Access Level
    Then I should see the "Access level successfully saved".

  Scenario:  Edit ACL
    Given I search and select the Access Level with name "joomla"
    And I set Access Level title as a "Gsoc_joomla"
    When I save Access Level
    Then I should see the "Access level successfully saved".

  Scenario: Delete ACL
    Given I search and select the Access Level with name "Gsoc_Joomla"
    When I Delete the Access le vel "Gsoc_admin"
    Then I confirm the  Access Level have been deleted by getting the message "1 View Access Level successfully removed."


  Scenario: User settings (mixed with com_config)






