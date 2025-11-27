<?php

namespace Features;

use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
use Behat\Behat\Context\Context as BehatContext;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFiles;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesRequest;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\File\FileId;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryInMemory;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class FileUploadContext implements BehatContext
{
    private AssistantId $assistantId;
    private UpdateAssistantFilesRequest $request;
    private UpdateAssistantFiles $service;
    private FileId $filesId;
    private AssistantRepositoryInMemory $assitantRepository;
    private Assistant $assistant;

    // Background
    #[Given('the assistant exists')]
    public function theAssistantExists(): void
    {
        $this->assistantId = new AssistantId();
        $name = "Assistant Documentation";
        $instructions = "Tu es un assistant spécialisé dans la documentation";
        $externalAssistantId = "asst_123456";

        $this->assistant = new Assistant($this->assistantId, $name, $instructions, $externalAssistantId);
        $this->assitantRepository = new AssistantRepositoryInMemory();
        $this->assitantRepository->add($this->assistant);
    }

    //Scenario 1
    #[Given('a file to upload')]
    public function aFileToUpload(): void
    {
        $this->filesId = new FileId("file-xxxx");
    }

    #[When('the file is uploaded')]
    public function theFileIsUploaded(): void
    {
        $this->request = new UpdateAssistantFilesRequest(
            $this->assistantId,
            [$this->filesId]
        );

        $bodyPath = __DIR__ . '/ressources/bodyForMockResponse.json';

        /** @var string $body */
        $body = file_get_contents($bodyPath);
        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient([$mockResponse, $mockResponse]);
        $assistantApi = new AssistantApi($client);
        $this->service = new UpdateAssistantFiles($this->assitantRepository, $assistantApi);
        $this->service->execute($this->request);
    }

    #[Then('the upload is a success')]
    public function theUploadIsASuccess(): void
    {
        $this->service->getResponse();
    }

     #[Then('the assistant now has new knowledge')]
    public function theAssistantNowHasNewKnowledge(): void
    {
        Assert::assertEquals($this->assistant->getFileIds(), [$this->filesId], "error on fileID");
    }

    // Scenario 2
    #[When('the file is uploaded without being linked to an assistant')]
    public function theFileIsUploadedWithoutBeingLinkedToAnAssistant(): void
    {
         $this->assistantId = new AssistantId();

         $this->request = new UpdateAssistantFilesRequest(
             $this->assistantId,
             [$this->filesId]
         );
    }

    #[Then('the upload fails')]
    public function theUploadFails(): void
    {
        $bodyPath = __DIR__ . '/ressources/bodyFailForMockResponse.json';

        /** @var string $body */
        $body = file_get_contents($bodyPath);
        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient([$mockResponse, $mockResponse]);
        $assistantApi = new AssistantApi($client);
        $this->service = new UpdateAssistantFiles($this->assitantRepository, $assistantApi);

        try {
            $this->service->execute($this->request);
            Assert::fail('Expected InvalidArgumentException to be thrown');
        } catch (\InvalidArgumentException $e) {
            Assert::assertSame(
                'Assistant not found: ' . $this->assistantId->getId(),
                $e->getMessage()
            );
        }
    }
}
