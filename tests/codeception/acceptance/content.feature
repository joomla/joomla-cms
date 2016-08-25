Feature: content
  In order to manage content article in the web
  As an owner
  I need to create modify trash publish and Unpublish content article

  Background:
    When I Login into Joomla administrator with username "admin" and password "admin"
    And I see the administrator dashboard

  Scenario: Create an Article
    Given There is a add content link
    When I create new content with field title as "Article One" and content as a "This is my first article"
    And I save an article
    Then I should see the article "Article One" is created

  Scenario: Feature an Article
    Given I search and select content article with title "Article One"
    When I featured the article
    Then I should see the article is now featured

  Scenario: Modify an article
    Given I select the content article with title "Article One"
    When I set access level as a "Registered"
    And I save the article
    Then I should see the "Registered" as article access level

  Scenario: Unpublish an article
    Given I have article with name "Article One"
    When I unpublish the article
    Then I should see the article is now unpublished

  Scenario: Trash an article
    Given I have "Article One" content article which needs to be Trash
    When  I Trash the article
    Then  I should see the article "Article One" in trash
