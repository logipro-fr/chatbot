<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
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

class MakeConversationController extends AbstractController
{
    private EntityManagerResolver $entityManagerResolver;

    public function __construct(
        private LanguageModelAbstractFactory $factory,
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManagerResolver = new EntityManagerResolver($entityManager);
    }

    #[Route('api/v1/conversations/Make', 'makeConversation', methods: ['POST'])]
    public function makeConversation(Request $request): Response
    {
        try {
            $entityManager = $this->entityManagerResolver->getEntityManagerByRequest($request);
            (new DatabaseTools())->createDatabaseIfNotExists($entityManager);

            $makeConversationRequest = $this->buildMakeconversationRequest($request);

            $conversation = new MakeConversation(
                new ConversationRepositoryDoctrine($entityManager),
                $this->factory,
                new ContextRepositoryDoctrine($entityManager)
            );
            $conversation->execute($makeConversationRequest);

            $eventFlush = new EventFlush($entityManager);
            $eventFlush->flushAndDistribute();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $conversation->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function buildMakeconversationRequest(Request $request): MakeConversationRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */

        $data = json_decode($content, true);


        /** @var Prompt */
        $prompt = new Prompt($data["Prompt"]);
        /** @var string */
        $lmName = $data['lmName'];
        /** @var ContextId */
        $context = new ContextId($data['context']);

        return new MakeConversationRequest($prompt, $lmName, $context);
    }
}
