<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ViewContext\ViewContext;
use Chatbot\Application\Service\ViewContext\ViewContextRequest;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ViewContextController extends AbstractController
{
    public function __construct(
        private ContextRepositoryInterface $repository,
        private ConversationRepositoryInterface $convrepository,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/contexts', 'viewContext', methods: ['GET'])]
    public function viewContext(Request $request): Response
    {
        $request = $this->buildViewContextRequest($request);
        $context = new viewContext($this->repository, $this->convrepository);
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

    private function buildViewContextRequest(Request $request): ViewContextRequest
    {
        /** @var string */
        $context = $request->query->get('Id');

        return new ViewContextRequest($context);
    }
}
