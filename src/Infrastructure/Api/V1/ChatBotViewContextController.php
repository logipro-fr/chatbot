<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ViewContext\ViewContext;
use Chatbot\Application\Service\ViewContext\ViewContextRequest;
use Chatbot\Application\Service\ViewContext\ViewContextResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function Safe\json_decode;

class ChatBotViewContextController extends AbstractController
{
    public function __construct(
        private ContextRepositoryInterface $repository,
        private ConversationRepositoryInterface $convrepository,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/context/View', 'viewContext', methods: ['POST'])]
    public function viewContext(Request $request): Response
    {
        $request = $this->buildViewContextRequest($request);
        $context = new viewContext($this->repository, $this->convrepository);
        try {
            $context->execute($request);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $context->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function buildViewContextRequest(Request $request): ViewContextRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */
        $data = json_decode($content, true);
        /** @var string */
        $context = $data['Id'];
        $type = $data['IdType'];

        return new ViewContextRequest($context, $type);
    }
}
