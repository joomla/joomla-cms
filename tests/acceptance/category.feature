Feature: category
  In order to manage category in the web
  As an owner
  I need to create modify trash publish and Unpublish category

  Background:
    Given Joomla CMS is installed
    When Login into Joomla administrator with username "admin" and password "admin"
    Then I see administrator dashboard

  Scenario: Verify available tabs in Category
    Given There is an article category link
    When I check available tabs in category
    Then I see available tabs "Category", "Publishing", "Permissions" and "Options"

  Scenario: Create new category
    Given There is an article category link
    When I fill mandatory fields for creating Category
      |     Title     |
      |   Category_1  |
      |   Category_2  |

    And I save the category
    Then I should see the "Category successfully saved." message

  Scenario: Modify category
    Given There is an article category link
    When I search and select category with title "Category_1"
    And I set the title as a "GSoc_category"
    And I save the category
    Then I should see the "Category successfully saved." message

  Scenario: Unpublish category
    Given I have a category with title "GSoc_category" which needs to be unpublish
    When I unpublish the category
    Then I should see the "1 category successfully unpublished." message

  Scenario: Trash category
    Given I have a category with title "GSoc_category" which needs to be trash
    When I trash the category
    Then I should see the "1 category successfully trashed." message

  Scenario: Create category without Title fails
    Given There is an article category link
    When I create new category without field title
    And I save the category
    Then I should see the "Invalid field:  Title"

  Scenario: Create menu item for newly created article
    Given There is a add content link
    When I create a new article "Test_article" with content as a "This Article test for menu item"
    And I save an article
    And I add the "Atricle" menu item in main menu
    And I Select menu item type as a "single article"
    And I select an article "Test_article"
    And I save the menu item
    Then I should see the "Menu item successfully saved." message

  Scenario: Create menu item for articles belonging to a specific Category
    Given There is a add content link
    When I create a new article "Test_category" with content as a "This Article test for category menu item"
    And I set category as a "Category_2"
    And I save an article
    And I add the "Category" menu item in main menu
    And I Select menu item type as a "Category List"
    And I select an category "Category_2"
    And I save the menu item
    Then I should see the "Menu item successfully saved." message

  Scenario: Category ACL Settings
    Given There is an article category link
    When I search and select category with title "Category_2"
    And I set access level as a "Registered"
    And I save the category
    Then I should see the "Category successfully saved." message

  Scenario: Category Language settings
    Given There is an article category link
    When I search and select category with title "Category_2"
    And I set language as a "English (UK)"
    And I save the category
    Then I should see the "Category successfully saved." message
    