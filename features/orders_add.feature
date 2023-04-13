@baselinker_command
Feature: Search for new orders not synchronized with Baselinker and add them to Baselinker.

    Scenario: Given there is new order
        Given there is a new order
        And I successfully run the command: "baselinker:orders:add"
        Then the message should be displayed: "successfully exported to Baselinker"
        And the Baselinker order number should be added to the order
