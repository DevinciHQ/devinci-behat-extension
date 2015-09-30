@api
Feature: DevinciExtension
  In order to prove the Devinci Extension is working properly
  As a developer
  I need to use the step definitions of this context

  Scenario: I can grab the html
    Given I am on "/index.html"
      And grab the html with a filename "test-file"
    Then file "assets/*test-file.html" should exist

  @javascript
  Scenario: I can grab a screenshot
    Given I am on "/index.html"
      And grab a screenshot with a filename "test-shot"
    Then file "assets/*test-shot*.png" should exist

  @javascript
  Scenario: I can wait for javascript to load
    Given I am on "/index.html"
    When I press "clickMe"
    Then I wait for "Hello World"
