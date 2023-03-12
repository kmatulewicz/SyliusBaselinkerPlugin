@test
Feature: Command Test

  Scenario: Test a command
    Given I am the system
    When I run a test command
    Then I should see "Hello World!"