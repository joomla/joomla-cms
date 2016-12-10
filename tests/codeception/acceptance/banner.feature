Feature: Banner
  In order to manage Banner article in the web
  I need to create modify trash archived Check-In And publish and Unpublish Banner

  Background:
    When I Login into Joomla administrator
    And I see the administrator dashboard

  Scenario: Create a Banner
    Given There is an add Banner link
    When I create a new banner with field title as "banner"
    And I save a Banner
    Then I should see the "Banner successfully saved." message

  Scenario: Modify a Banner
    Given There is a Banner listing page
    When I Click the Banner with Name "banner"
    And I have Change the Banner field title to "randombanner1"
    And I save a Banner
    Then I should see the "Banner successfully saved." message

  Scenario: publish a Banner
    Given There is a Banner listing page
    When I select the Banner with Name "randombanner" which needs to be published
    And I have publish the Banner
    Then I should see the "1 banner successfully published." message

  Scenario: Unpublish an Banner
    Given There is a Banner listing page
    When I select the Banner with Name "randombanner" which needs to be unpublished
    And I have unpublish the Banner
    Then I should see the "1 banner successfully unpublished." message

  Scenario: Check-In a Banner
    Given There is a Banner listing page
    When I select the Banner with Name "randombanner" which needs to be Check-In
    And I Check-In the Banner
    Then I should see the "1 banner successfully checked in." message

  Scenario: Trash a Banner
    Given There is a Banner listing page
    When I select the Banner with Name "randombanner" which needs to be Trash
    And I have Trash the Banner
    Then  I should see the "1 banner successfully trashed." message

  Scenario: Remove trash a Banner
    Given There is a Banner listing page
    When I select the Banner with Name "randombanner" which needs to be Remove Trash
    And I Remove Trash the Banner
    Then  I should see the "1 banner successfully deleted." message
