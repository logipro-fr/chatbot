<?php

namespace Features;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
use Behat\Behat\Context\Context as BehatContext;

use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFiles;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\File\FileId;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryInMemory;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpClient\MockHttpClient;

class FileUploadContext implements BehatContext
{
    private AssistantRepositoryInMemory $repository;
    private AssistantId $assistantId;
    private UpdateAssistantFilesRequest $request;

    #[Given('the assistant exists with :id')]
    public function theAssistantExistsWith(string $id): void
    {
        $this->assistantId = new AssistantId($id);

    }

    #[Given('a file to upload')]
    public function aFileToUpload(): void
    {
        throw new PendingException();
    }

    #[When('the file is uploaded')]
    public function theFileIsUploaded(): void
    {

        
        $filesId = [ new FileId("monfichier") ];

        $this->request = new UpdateAssistantFilesRequest(
            $this->assistantId,
            $filesId
        );

        $assitantRepository = new AssistantRepositoryInMemory();
        $client = new MockHttpClient();

        $assistantApi = new AssistantApi( $client);

        $service = new UpdateAssistantFiles($assitantRepository, $assistantApi);

        $service->execute($this->request);
    }

    #[Then('the upload is a success')]
    public function theUploadIsASuccess(): void
    {
        throw new PendingException();
    }

     #[Then('the assistant now has new knowledge')]
    public function theAssistantNowHasNewKnowledge(): void
    {
        throw new PendingException();
    }

    #[When('the file is not linked to the assistant')]
    public function theFileIsNotLinkedToTheAssistant(): void
    {
        throw new PendingException();
    }

    #[Then('the upload fails')]
    public function theUploadFails(): void
    {
        throw new PendingException();
    }
}