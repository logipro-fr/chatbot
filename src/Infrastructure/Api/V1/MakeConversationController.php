<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Phariscope\MultiTenant\Doctrine\EntityManagerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Safe\json_decode;

class MakeConversationController extends AbstractController
{
    private EntityManagerResolver $entityManagerResolover;

    public function __construct(
        private ConversationRepositoryInterface $repository,
        private LanguageModelAbstractFactory $factory,
        EntityManagerInterface $entityManager,
        private ?ContextRepositoryInterface $contextRepository = null,
    ) {
        $this->entityManagerResolover = new EntityManagerResolver($entityManager);
    }

    #[Route('api/v1/conversations/Make', 'makeConversation', methods: ['POST'])]
    public function makeConversation(Request $request): Response
    {
        try {
            $request = $this->buildMakeconversationRequest($request);

            $multiTenanEntityManager = $this->entityManagerResolover->getEntityManager();

            /** @var ContextRepositoryDoctrine */
            $contextRepository = $this->contextRepository ?? $multiTenanEntityManager->getRepository(Context::class);

            $conversation = new MakeConversation($this->repository, $this->factory, $contextRepository);
            $conversation->execute($request);
            $eventFlush = new EventFlush($multiTenanEntityManager);
            $eventFlush->flushAndDistribute();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $conversation->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function buildMakeconversationRequest(Request $request): MakeConversationRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */

        $data = json_decode($content, true);


        /** @var Prompt */
        $prompt = new Prompt($data["Prompt"]);
        /** @var string */
        $lmName = $data['lmName'];
        /** @var ContextId */
        $context = new ContextId($data['context']);

        return new MakeConversationRequest($prompt, $lmName, $context);
    }
}
