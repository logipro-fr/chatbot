<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ViewConversation\ViewConversation;
use Chatbot\Application\Service\ViewConversation\ViewConversationRequest;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Phariscope\MultiTenant\Doctrine\DatabaseTools;
use Phariscope\MultiTenant\Doctrine\EntityManagerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ViewConversationController extends AbstractController
{
    private EntityManagerResolver $entityManagerResolver;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManagerResolver = new EntityManagerResolver($entityManager);
    }

    #[Route('api/v1/conversations', 'viewConversations', methods: ['GET'])]
    public function viewConversation(Request $request): Response
    {
        try {
            $entityManager = $this->entityManagerResolver->getEntityManagerByRequest($request);
            (new DatabaseTools())->createDatabaseIfNotExists($entityManager);

            $request = $this->buildViewConversationRequest($request);
            $context = new ViewConversation(
                new ConversationRepositoryDoctrine($entityManager)
            );

            $context->execute($request);

            $eventFlush = new EventFlush($entityManager);
            $eventFlush->flushAndDistribute();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $context->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function buildViewConversationRequest(Request $request): ViewConversationRequest
    {
        /** @var string */
        $conversation = $request->query->get('Id');


        return new ViewConversationRequest($conversation);
    }
}
