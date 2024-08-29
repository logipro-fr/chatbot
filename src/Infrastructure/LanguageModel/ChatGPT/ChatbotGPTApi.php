<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Application\Service\ChatbotApiInterface;
use Chatbot\Application\Service\Exception\BadInstanceException;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\ExcesRequestException;
use Chatbot\Application\Service\Exception\OtherException;
use Chatbot\Application\Service\Exception\UnhautorizeKeyException;
use Chatbot\Application\Service\RequestInterface;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;
use function Safe\json_encode;

class ChatbotGPTApi implements ChatbotApiInterface
{
    private string $CHATBOT_KEY_API;

    public function __construct(
        private HttpClientInterface $client,
        ?string $apiKey = null
    ) {
        if ($apiKey == null) {
            $apiKey = $_ENV["CHATBOT_API_KEY"];
        }
        $this->CHATBOT_KEY_API = $apiKey;
    }


    /**
     * @param RequestInterface $request
     */
    public function request(RequestInterface $request): ResponseGPT
    {

        /**
         * @var string $userprompt */
        $userprompt = "";

        if ($request instanceof RequestGPT) {
            $userprompt = $request->prompt->getUserResquest();
            $context = $request->context->getContext()->getMessage();


            $response = $this->client->request(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                $this->paramsHeader($this->createContent($request->conversation, $context, $userprompt))
            );
            $code = $response->getStatusCode();
            if ($code == 200) {
                $contentJson = $response->getContent();
                /** @var \stdClass{"choices": array<int,\stdClass>} $content */
                $content = json_decode($contentJson);
                $choices = $content->choices;
                $messageContent = $choices[0]->message->content ;
                $contentModel = strval($messageContent);
                return new ResponseGPT($contentModel, $code);
            } elseif ($code == 401) {
                throw new UnhautorizeKeyException("Bad Key");
            } elseif ($code == 400) {
                throw new BadRequestException("Bad Request");
            } elseif ($code == 429) {
                throw new ExcesRequestException("Exceeded quota");
            }
                throw new OtherException("Other error $code");
        } else {
            throw new BadInstanceException("Bad Instance");
        }
    }

    /**  @return  array<string, array<string, string>|string> */
    public function paramsHeader(string $content): array
    {
        $paramHeader = [
        'headers' =>
        ['Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API

        ],
        'body' => $content
        ];

        return $paramHeader;
    }

    public function createContent(Conversation $conversation, string $context, string $userprompt): string
    {
        $message = [];
        $message[] = ["role" => "system","content" => $context];
        for ($i = 0; $i < $conversation->countPair(); $i++) {
            $pair = $conversation->getPair($i);
            $message[] = ["role" => "user","content" => $pair->getPrompt()->getUserResquest()];
            $message[] = ["role" => "assistant","content" => $pair->getAnswer()->getMessage()];
        }
        $message[] = ["role" => "user","content" => $userprompt];
        $content = [
            "model" => "gpt-4-turbo",
            "messages" => $message

        ];
        return json_encode($content);
    }
}
