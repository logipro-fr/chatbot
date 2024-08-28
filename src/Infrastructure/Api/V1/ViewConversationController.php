<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ViewConversation\ViewConversation;
use Chatbot\Application\Service\ViewConversation\ViewConversationRequest;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ViewConversationController extends AbstractController
{
    public function __construct(
        private ConversationRepositoryInterface $convRepository,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/conversations', 'viewConversations', methods: ['GET'])]
    public function viewConversation(Request $request): Response
    {
        $request = $this->buildViewConversationRequest($request);
        $context = new ViewConversation($this->convRepository);
        try {
            $context->execute($request);
            $eventFlush = new EventFlush($this->entityManager);
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
