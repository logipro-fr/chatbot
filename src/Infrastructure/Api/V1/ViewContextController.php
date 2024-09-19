<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ViewContext\ViewContext;
use Chatbot\Application\Service\ViewContext\ViewContextRequest;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Phariscope\MultiTenant\Doctrine\DatabaseTools;
use Phariscope\MultiTenant\Doctrine\EntityManagerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ViewContextController extends AbstractController
{
    private EntityManagerResolver $entityManagerResolver;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManagerResolver = new EntityManagerResolver($entityManager);
    }

    #[Route('api/v1/contexts', 'viewContext', methods: ['GET'])]
    public function viewContext(Request $request): Response
    {
        try {
            $entityManager = $this->entityManagerResolver->getEntityManagerByRequest($request);
            (new DatabaseTools())->createDatabaseIfNotExists($entityManager);

            $viewContextRequest = $this->buildViewContextRequest($request);
            $context = new viewContext(
                new ContextRepositoryDoctrine($entityManager)
            );

            $context->execute($viewContextRequest);
            $eventFlush = new EventFlush($entityManager);
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
