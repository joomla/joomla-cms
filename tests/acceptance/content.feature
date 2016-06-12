Feature: content
  In order to manage content article in the web
  As an owner
  I need to create modify trash publish and Unpublish content article

  Background:
    Given Joomla CMS is installed
    When Login into Joomla administrator with username "admin" and password "admin"
    Then I see administrator dashboard

  Scenario: Create an Article
    Given There is a Add Content link
    When I fill mandatory fields for creating article
      | field   | value                    |
      | title   | My_Article               |
      | content | This is my First Article |
    And I save an article
    Then I should see the "Article successfully saved." message

  Scenario: Feature an Article
    Given I search and select content article with title "My_Article"
    When I featured the article
    Then I save and see the "1 article featured." message

  Scenario: Modify an article
    Given I select the content article with title "My_Article"
    And I set access level as a "Registered"
    When I save the article
    Then I should see the "Article successfully saved" message

  Scenario: Unpublish an article
    Given I have article with name "My_Article"
    When I unpublish the article
    Then I see article unpublish message "1 article unpublished."

  Scenario: Trash an article
    Given I have "My_Article" content article which needs to be Trash
    When  I Trash the article
    Then  I see article trash message "1 article trashed."

