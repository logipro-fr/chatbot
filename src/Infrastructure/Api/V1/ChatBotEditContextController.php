<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\EditContext\EditContext;
use Chatbot\Application\Service\EditContext\EditContextRequest;
use Chatbot\Application\Service\EditContext\EditContextResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function Safe\json_decode;

class ChatBotEditContextController extends AbstractController
{
    public function __construct(
        private ContextRepositoryInterface $repository,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/contexts', 'editContext', methods: ['PATCH'])]
    public function editContext(Request $request): Response
    {
        $request = $this->buildEditContextRequest($request);
        $context = new EditContext($this->repository);
        try {
            $context->execute($request);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $context->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function buildEditContextRequest(Request $request): EditContextRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */
        $data = json_decode($content, true);

        /** @var ContextId */
        $context = new ContextId($data['Id']);
        /** @var ContextMessage */
        $message = new ContextMessage($data['NewMessage']);

        return new EditContextRequest($message, $context);
    }
}
