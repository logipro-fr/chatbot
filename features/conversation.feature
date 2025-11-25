# language: en

Feature: Conversation with a chatbot
Give answers to my questions

  Background: Chatbot
    Given the assistant use model "Parrot"

    Scenario: User asks a simple question
    Given User prepares the request with this prompt "What is the capital of France?" to the assistant
    When the assistant receives the prompt
    Then the assistant responds "What is the capital of France?"
