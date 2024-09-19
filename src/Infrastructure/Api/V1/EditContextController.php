<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\EditContext\EditContext;
use Chatbot\Application\Service\EditContext\EditContextRequest;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Phariscope\MultiTenant\Doctrine\DatabaseTools;
use Phariscope\MultiTenant\Doctrine\EntityManagerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Safe\json_decode;

class EditContextController extends AbstractController
{
    private EntityManagerResolver $entityManagerResolver;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManagerResolver = new EntityManagerResolver($entityManager);
    }

    #[Route('api/v1/contexts', 'editContext', methods: ['PATCH'])]
    public function editContext(Request $request): Response
    {

        try {
            $entityManager = $this->entityManagerResolver->getEntityManagerByRequest($request);
            (new DatabaseTools())->createDatabaseIfNotExists($entityManager);

            $request = $this->buildEditContextRequest($request);
            $context = new EditContext(
                new ContextRepositoryDoctrine($entityManager)
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
