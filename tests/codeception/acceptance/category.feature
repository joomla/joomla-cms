Feature: category
  In order to manage category in the web
  As an owner
  I need to create modify trash publish and Unpublish category

  Background:
    When I Login into Joomla administrator with username "admin" and password "admin"
    And I see the administrator dashboard

  Scenario: Verify available tabs in Category
    Given There is an article category link
    When I check available tabs in category
    Then I see available tabs "Category", "Options", "Publishing" and "Permissions"

  Scenario: Create new category
    Given There is an article category link
    When I fill mandatory fields for creating Category
      |     Title     |
      |   Category_1  |
      |   Category_2  |
    Then I should see the category "Category_1" is created
    And I should see the category "Category_2" is created

  Scenario: Modify category
    Given There is an article category link
    When I search and select category with title "Category_1"
    And I set the title as a "GSoc_category"
    And I save the category
    Then I should see the category "GSoc_category" is created

  Scenario: Unpublish category
    Given I have a category with title "GSoc_category" which needs to be unpublish
    When I unpublish the category
    Then I should see the category is now unpublished

  Scenario: Trash category
    Given I have a category with title "GSoc_category" which needs to be trash
    When I trash the category
    Then I should see the category "GSoc_category" in trash

  Scenario: Create category without Title fails
    Given There is an article category link
    When I create new category without field title
    And I save the category
    Then I should see the "Invalid field:  Title"

  Scenario: Create menu item for newly created article
    Given There is a add content link
    When I create a new article "Test_article" with content as a "This Article test for menu item"
    And I save an article
    And I create menu item with title "Article"
    And I choose menu item type "Articles" and select "Single Article"
    And I select an article "Test_article"
    And I save the menu item
    Then I should see the menu item "Article" is created

  Scenario: Create menu item for articles belonging to a specific Category
    Given There is a add content link
    When I create a new article "Test_category" with content as a "This Article test for category menu item"
    And I set category as a "- Category_2"
    And I save an article
    And I create menu item with title "All Categories"
    And I choose menu item type "Articles" and select "List All Categories"
    And I select a top level category "Category_2"
    And I save the menu item
    Then I should see the menu item "All Categories" is created

  Scenario: Category ACL Settings
    Given There is an article category link
    When I search and select category with title "Category_2"
    And I set access level as a "Registered"
    And I save the category
    Then I should see the "Registered" as category access level

  Scenario: Category Language settings
    Given There is an article category link
    When I search and select category with title "Category_2"
    And I set language as a "English (UK)"
    And I save the category
    Then I should see the category language as "English (UK)"

  Scenario: Check article if exist in frontend
    Given There is joomla home page
    When I press on "Article" menu
    Then I should see the "Test_article" in home page

  Scenario: Create an article for registered user and ensure is not visible in frontend
    Given There is a add content link
    When I select the content article with title "Test_article"
    And I set access level as a "Registered"
    And I save the article
    And I press on "Article" menu in joomla home page
    Then I should see the "You are not authorised to view this resource." error
