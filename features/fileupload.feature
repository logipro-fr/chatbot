Feature: file upload
    In order to give more knowledge to an AI agent
    As a chatbot api user
    I want to add a file

    Background:
        Given the assistant exists with "id"

    Scenario: uploaded file is linked to an assistant
        Given a file to upload
        When the file is uploaded
        Then the upload is a success
        And the assistant now has new knowledge

    Scenario: uploaded file is NOT linked to an assistant
        When the file is uploaded
        And the file is not linked to the assistant
        Then the upload fails