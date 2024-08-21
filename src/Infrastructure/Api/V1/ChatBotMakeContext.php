<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\MakeContext\MakeContext;
use Chatbot\Application\Service\MakeContext\MakeContextRequest;
use Chatbot\Application\Service\MakeContext\MakeContextResponse;
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

class ChatBotMakeContext extends AbstractController
{
    public function __construct(
        private ContextRepositoryInterface $repository,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/context/Make', 'makeContext', methods: ['POST'])]
    public function makeContext(Request $request): Response
    {
        $request = $this->buildMakeContextRequest($request);
        $context = new MakeContext($this->repository);
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


    private function buildMakeContextRequest(Request $request): MakeContextRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */
        $data = json_decode($content, true);
        /** @var ContextMessage */
        $context = new ContextMessage($data['ContextMessage']);

        return new MakeContextRequest($context);
    }
}
