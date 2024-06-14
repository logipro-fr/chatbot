<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Application\Service\ChatbotApiInterface;
use Chatbot\Application\Service\Exception\BadInstanceException;
use Chatbot\Application\Service\Exception\BadRequest;
use Chatbot\Application\Service\Exception\ExcesRequest;
use Chatbot\Application\Service\Exception\Other;
use Chatbot\Application\Service\Exception\UnhautorizeKey;
use Chatbot\Application\Service\RequestInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;

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
            $userprompt = $request->prompt->prompt;
            $context = $request->context->context;

            $content = <<<EOF
            {
                "model": "gpt-3.5-turbo",
                "messages": [
                    {
                        "role": "system",
                        "content": "$context"
                    },
                    {
                        "role": "user",
                        "content": "$userprompt"
                    }
                ]
            }
            EOF;

            $response = $this->client->request(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                $this->paramsHeader($content)
            );
            $code = $response->getStatusCode();
            if ($code == 200) {
                $contentJson = $response->getContent();
                /** @var \stdClass{"choices": array<int,\stdClass>} $content */
                $content = json_decode($contentJson);
                $choices = $content->choices;
                $messageContent = $choices[0]->message->content ;
                //$content['choices'][0]['message']['content'];
                $contentModel = strval($messageContent);
                return new ResponseGPT($contentModel, $code);
            } elseif ($code == 401) {
                throw new UnhautorizeKey("Bad Key");
            } elseif ($code == 400) {
                throw new BadRequest("Bad Request");
            } elseif ($code == 429) {
                throw new ExcesRequest("Exceeded quota");
            }
                throw new Other("Other error");
        } else {
            throw new BadInstanceException("BadInstance");
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
}
