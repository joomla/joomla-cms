Feature: Content
  In order to manage articles
  As an administrator
  I need to create, edit, and delete articles

  Background:
    Given I am logged in as administrator
    And I go to the ArticleManager page

  Scenario: Create an Article
    Given the article "Test Article" does not exist
    When I click "New" in the toolbar
    And I fill in
      | field | value                  |
      | title | Test Article           |
      | body  | This is a test article |
    And I click "Save and Close" in the toolbar
    Then I am on the ArticleManager page
    And I see the message "article saved"
    And I see the article "Test Article" in the list

  Scenario: Edit an Article
    Given the article "Test Article" exists
    When I select the article "Test Article"
    And I click "Edit" in the toolbar
    And I fill in
      | field | value                  |
      | title | Test Article, changed  |
    And I click "Save and Close" in the toolbar
    Then I am on the ArticleManager page
    And I see the message "article saved"
    And I don't see the article "Test Article" in the list
    And I see the article "Test Article, changed" in the list

  Scenario: Delete an Article
    Given the article "Test Article, changed" exists
    When I select the article "Test Article, changed"
    And I click "Delete" in the toolbar
    Then I am on the ArticleManager page
    And I see the message "article deleted"
    And I don't see the article "Test Article, changed" in the list
