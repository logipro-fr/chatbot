Feature: Query a Conversational Model

    Scenario: Initalize a conversation
    Given I want to speak with the langage model "Parrot"
    When I start a conversation prompting with "Hello, how are you?"
    Then I get an answer "Hello, how are you?"
    And I have a conversation identifier

    Scenario: Continue a conversation 
    Given I started a conversation
    When I ask "continue our conversation"
    Then I have an answer 
    And the number of token has increased
