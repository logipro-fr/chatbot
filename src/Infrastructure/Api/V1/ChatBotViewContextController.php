<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ViewContext\ViewContext;
use Chatbot\Application\Service\ViewContext\ViewContextRequest;
use Chatbot\Application\Service\ViewContext\ViewContextResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function Safe\json_decode;

class ChatBotViewContextController
{
    public function __construct(
        private ContextRepositoryInterface $repository,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/context/View', 'viewContext', methods: ['POST'])]
    public function viewContext(Request $request): Response
    {
        $request = $this->buildViewContextRequest($request);
        $context = new ViewContext($this->repository);
        try {
            $context->execute($request);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $context->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function writeSuccessfulResponse(ViewContextResponse $contextResponse): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => true,
                'errorCode' => "",
                'data' => [
                    'context' => $contextResponse->contextMessage->getMessage(),
                ],
                    'message' => "",
            ],
            200
        );
    }

    private function writeUnSuccessFulResponse(Throwable $e): JsonResponse
    {
        $className = (new \ReflectionClass($e))->getShortName();
        return new JsonResponse(
            [
                'success' => false,
                'ErrorCode' => $className,
                'data' => '',
                'message' => $e->getMessage(),
            ],
        );
    }

    private function buildViewContextRequest(Request $request): ViewContextRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */
        $data = json_decode($content, true);
        /** @var ContextId */
        $context = new ContextId($data['Id']);

        return new ViewContextRequest($context);
    }
}
