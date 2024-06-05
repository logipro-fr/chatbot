<?php

namespace Chatbot;

use Chatbot\ChatbotApiInterface;
use SebastianBergmann\Type\MixedType;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;

class ChatbotGeminiApi implements ChatbotApiInterface
{
    private const PROJECT_ID = "Mon projet";
    private const CONTEXT = "You're helpfull asistant";

    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    /**  @return  array<string, array<string, string>|string> */
    public function paramsHeader(string $content): array
    {
        $paramHeader = [

        'headers' =>
        ['Content-Type' => 'application/json',
        'Authorization' => 'Bearer '

        ],
        'body' => $content
        ];

        return $paramHeader;
    }


    public function url(): string
    {
        $url = 'https://{REGION}-aiplatform.googleapis.com/v1/projects/' . self::PROJECT_ID .
        '/locations/{REGION}/publishers/google/models/gemini-1.0-pro:streamGenerateContent';
        return $url;
    }

    public function request(RequestInterface $request): ResponseGPT
    {
        $content = <<<EOF
        {
            "contents": [
              {
                "role": "USER",
                "parts": { "text": "Hello!" }
              },
              {
                "role": "MODEL",
                "parts": { "text": "Argh! What brings ye to my ship?" }
              },
              {
                "role": "USER",
                "parts": { "text": "Wow! You are a real-life priate!" }
              }
            ],
            "safety_settings": {
              "category": "HARM_CATEGORY_SEXUALLY_EXPLICIT",
              "threshold": "BLOCK_LOW_AND_ABOVE"
            },
            "generation_config": {
              "temperature": 0.2,
              "topP": 0.8,
              "topK": 40,
              "maxOutputTokens": 200,
            }
          }
        EOF;

        $response = $this->client->request(
            'POST',
            $this->url(),
            $this->paramsHeader($content)
        );


        $contentJson = $response->getContent();
        /** @var array<int, \stdClass>*/
        $content = json_decode($contentJson);
        $messageContent = $content["0"]->candidates[0]->content->parts[0]->text ;
        $contentModel = strval($messageContent);
        return new ResponseGPT($contentModel);
    }
}
