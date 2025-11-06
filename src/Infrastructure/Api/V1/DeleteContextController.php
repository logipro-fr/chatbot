<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\DeleteContext\DeleteContext;
use Chatbot\Application\Service\DeleteContext\DeleteContextRequest;
use Chatbot\Domain\Model\Context\ContextId;
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

class DeleteContextController extends AbstractController
{
    private EntityManagerResolver $entityManagerResolver;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManagerResolver = new EntityManagerResolver($entityManager);
    }

    #[Route('api/v1/contexts', 'deleteContext', methods: ['DELETE'])]
    public function deleteContext(Request $request): Response
    {

        try {
            $entityManager = $this->entityManagerResolver->getEntityManagerByRequest($request);
            (new DatabaseTools())->createDatabaseIfNotExists($entityManager);

            $request = $this->buildDeleteContextRequest($request);
            $context = new DeleteContext(
                new ContextRepositoryDoctrine($entityManager),
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
