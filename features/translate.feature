Feature: Text Translation via ChatGPT
  As a user of the microservice API
  I want to submit texts for translation
  In order to obtain accurate translations in the target language

  Scenario Outline: Initialize a conversation
    Given I want to translate in language <targetLanguage> with the language model "ChatGPT"
    When I prompt <textToTranslate>
    Then I get a translation <expectedAnswer>

    Examples:
      | textToTranslate           | targetLanguage | expectedAnswer         |
      | "Bonjour, comment ça va?" | "english"      | "Hello, how are you?"  |
      | "Il pleut des cordes"     | "english"      | "It's raining cats and dogs"|
      | "Bonjour"                 | "spanish"      | "Hola"|
