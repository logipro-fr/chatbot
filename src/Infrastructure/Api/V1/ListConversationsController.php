<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ListConversations\ListConversations;
use Chatbot\Application\Service\ListConversations\ListConversationsRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Thread\ThreadRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Phariscope\MultiTenant\Doctrine\DatabaseTools;
use Phariscope\MultiTenant\Doctrine\EntityManagerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListConversationsController extends AbstractController
{
    private EntityManagerResolver $entityManagerResolver;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManagerResolver = new EntityManagerResolver($entityManager);
    }

    #[Route('api/v1/conversations/list', 'listConversations', methods: ['GET'])]
    public function listConversations(Request $request): Response
    {
        try {
            $entityManager = $this->entityManagerResolver->getEntityManagerByRequest($request);
            (new DatabaseTools())->createDatabaseIfNotExists($entityManager);

            $listRequest = $this->buildListConversationsRequest($request);
            $service = new ListConversations(
                new ThreadRepositoryDoctrine($entityManager),
                new ConversationRepositoryDoctrine($entityManager)
            );

            $service->execute($listRequest);

            $eventFlush = new EventFlush($entityManager);
            $eventFlush->flushAndDistribute();
        } catch (Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
        $response = $service->getResponse();
        return $this->writeSuccessfulResponse($response->toArray());
    }

    private function buildListConversationsRequest(Request $request): ListConversationsRequest
    {
        /** @var string */
        $assistantId = $request->query->get('assistantId');

        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

        return new ListConversationsRequest(new AssistantId($assistantId));
    }
}
