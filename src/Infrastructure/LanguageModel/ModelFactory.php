<?php

namespace Chatbot\Infrastructure\LanguageModel;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModel;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Exception\BadKeywords;
use Chatbot\Domain\Model\Conversation\Context;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModelTranslate;
use Chatbot\Infrastructure\LanguageModel\Exception\BadLanguageModelName;
use Chatbot\Infrastructure\LanguageModel\Exception\MissingContext;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ModelFactory extends LanguageModelAbstractFactory
{
    private HttpClientInterface $client;
    public function __construct(private string $API_KEY, ?HttpClientInterface $client = null)
    {

        if ($client == null) {
            $this->client = new CurlHttpClient();
        } else {
            $this->client = $client;
        }
    }

    public function create(string $lmName, string $context): LanguageModelInterface
    {

        switch ($lmName) {
            case "GPTModel":
                return new GPTModel($this->client, new Context($context), $this->API_KEY);
            case "GPTModelTranslate":
                return new GPTModelTranslate($this->client, $this->API_KEY, $context);
            case "Parrot":
                return new Parrot();
            case "ParrotTranslate":
                return new ParrotTranslate($context);
            default:
                throw new BadLanguageModelName();
        }
    }
}
