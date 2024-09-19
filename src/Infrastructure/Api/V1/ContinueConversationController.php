<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Phariscope\MultiTenant\Doctrine\DatabaseTools;
use Phariscope\MultiTenant\Doctrine\EntityManagerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Safe\json_decode;

class ContinueConversationController extends AbstractController
{
    private EntityManagerResolver $entityManagerResolver;

    public function __construct(
        private LanguageModelAbstractFactory $factory,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManagerResolver = new EntityManagerResolver($entityManager);
    }

    #[Route('api/v1/conversations/Continue', 'continueConversation', methods: ['POST'])]
    public function continueConversation(Request $request): Response
    {
        try {
            $entityManager = $this->entityManagerResolver->getEntityManagerByRequest($request);
            (new DatabaseTools())->createDatabaseIfNotExists($entityManager);
            
            $request = $this->buildContinueconversationRequest($request);
            $conversationRepository = new ConversationRepositoryDoctrine($entityManager);
            $contextRepository = new ContextRepositoryDoctrine($entityManager);
            $conversation = new ContinueConversation($conversationRepository, $contextRepository, $this->factory);
            $conversation->execute($request);

            $eventFlush = new EventFlush($entityManager);
            $eventFlush->flushAndDistribute();

            $response = $conversation->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
    }

    private function buildContinueconversationRequest(Request $request): ContinueConversationRequest
    {
        $content = $request->getContent();

        /** @var array<string> $data */
        $data = json_decode($content, true);

        $prompt = new Prompt($data['Prompt']);
        /** @var string */
        $id = $data["convId"];
        $convId = new ConversationId($id);
        /** @var string */
        $lmName = $data['lmName'];


        return new ContinueConversationRequest($prompt, $convId, $lmName);
    }
}
