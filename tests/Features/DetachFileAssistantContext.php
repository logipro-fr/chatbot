<?php

namespace Features;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFiles;
use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFilesRequest;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\File\FileId;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryInMemory;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DetachFileAssistantContext implements Context
{
    private AssistantRepositoryInMemory $assitantRepository;
    private Assistant $assistant;
    private AssistantId $assistant_id;
    private DetachAssistantFiles $service;
    private DetachAssistantFilesRequest $request;

    #[Given('un assistant avec l\'id :assistantId est attaché au fichier :nameFile')]
    public function unAssistantAvecLidEstAttacheAuFichier(string $assistantId, string $nameFile): void
    {
        $name = "Assistant Documentation";
        $instructions = "Tu es un assistant spécialisé dans la documentation";
        $externalAssistantId = "asst_123456";
        $vectorId = "vs_123";

        $fileId = new FileId($nameFile);
        $this->assistant_id = new AssistantId($assistantId);

        $this->assistant = new Assistant(
            $this->assistant_id,
            $name,
            $instructions,
            $externalAssistantId
        );

        $this->assitantRepository = new AssistantRepositoryInMemory();
        $this->assistant->setVectorId($vectorId);
        $this->assistant->addFileId($fileId);
        $this->assitantRepository->add($this->assistant);
    }

    #[When('l\'utilisateur détache l\'assistant du fichier :deleteFile')]
    public function lutilisateurDetacheLassistantDuFichier(string $deleteFile): void
    {
        $deletefileId = new FileId($deleteFile);

        $this->request = new DetachAssistantFilesRequest(
            $this->assistant_id,
            $deletefileId
        );

        putenv('CHATBOT_KEY_API=fake-test-key');
        $_ENV['CHATBOT_KEY_API'] = 'fake-test-key';

        // On mocke l'API comme dans FileUploadContext
        $body = '{"result": "success"}'; // contenu JSON fictif, adapté à ce que ton service attend
        $mockResponse = new MockResponse($body);
        $client = new MockHttpClient([$mockResponse]);

        $assistantApi = new AssistantApi($client);
        $this->service = new DetachAssistantFiles($this->assitantRepository, $assistantApi);

        $this->service->execute($this->request);
    }

    #[Then('l\'assistant :assistantId ne doit plus être attaché au fichier :nameFile')]
    public function lassistantNeDoitPlusEtreAttacheAuFichier(string $assistantId, string $nameFile): void
    {
        $this->service->getResponse();

        $assistant = $this->assitantRepository->findById($this->assistant_id);

        // Sécurise le cas où l'assistant n'est pas trouvé
        if (!$assistant instanceof Assistant) {
            Assert::fail('Assistant %s introuvable dans le repository');
        }

        /** @var Assistant $assistant */
        $fileId = new FileId($nameFile);

        Assert::assertNotContains(
            $fileId,
            $assistant->getFileIds(),
        );
    }
}
