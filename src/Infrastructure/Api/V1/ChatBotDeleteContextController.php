<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\DeleteContext\DeleteContext;
use Chatbot\Application\Service\DeleteContext\DeleteContextRequest;
use Chatbot\Application\Service\DeleteContext\DeleteContextResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function Safe\json_decode;

class ChatBotDeleteContextController extends AbstractController
{
    public function __construct(
        private ContextRepositoryInterface $repository,
        private ConversationRepositoryInterface $convrepositry,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/context/Delete', 'deleteContext', methods: ['POST'])]
    public function deleteContext(Request $request): Response
    {
        $request = $this->buildDeleteContextRequest($request);
        $context = new DeleteContext($this->repository, $this->convrepositry);
        try {
            $context->execute($request);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $context->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function buildDeleteContextRequest(Request $request): DeleteContextRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */
        $data = json_decode($content, true);

        /** @var ContextId */
        $context = new ContextId($data['Id']);

        return new DeleteContextRequest($context);
    }
}
