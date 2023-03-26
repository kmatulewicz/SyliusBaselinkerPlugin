
Feature: getLastLogId
    As developer
    I want to run getLastLogId function
    In order to receive last log id from BaselinkerAPI

    Scenario: Send correct query with orders in Baselinker
        Given Baselinker API is "up", token is "correct" and number orders in Baselinker is "3"
        When I send query
        Then I should receive int

    Scenario: Send correct query without orders in Baselinker
        Given Baselinker API is "up", token is "correct" and number orders in Baselinker is "0"
        When I send query
        Then I should receive null

    Scenario: Send incorrect query
        Given Baselinker API is "up", token is "incorrect" and number orders in Baselinker is "0"
        When I send query
        Then exception should be thrown

    Scenario: Send query when Baselinker is offline
        Given Baselinker API is "down", token is "correct" and number orders in Baselinker is "0"
        When I send query
        Then exception should be thrown
