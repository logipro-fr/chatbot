<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Safe\json_decode;

class ContinueConversationController extends AbstractController
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
        private ContextRepositoryInterface $contextRepository,
        private LanguageModelAbstractFactory $factory,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/conversations/Continue', 'continueConversation', methods: ['POST'])]
    public function continueConversation(Request $request): Response
    {
        try {
            $request = $this->buildContinueconversationRequest($request);
            $conversation = new ContinueConversation($this->repository, $this->contextRepository, $this->factory);
            $conversation->execute($request);
            $this->entityManager->flush();
            $response = $conversation->getResponse();
            $this->entityManager->flush();
            $eventFlush = new EventFlush($this->entityManager);
            $eventFlush->flushAndDistribute();
            return $this->writeSuccessfulResponse($response);
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
    }

    private function buildContinueconversationRequest(Request $request): ContinueConversationRequest
    {
        $content = $request->getContent();

        /** @var array<string> $data */
        $data = json_decode($content, true);

        $prompt = new Prompt($data['Prompt']);
        /** @var string */
        $id = $data["convId"];
        $convId = new ConversationId($id);
        /** @var string */
        $lmName = $data['lmName'];


        return new ContinueConversationRequest($prompt, $convId, $lmName);
    }
}
