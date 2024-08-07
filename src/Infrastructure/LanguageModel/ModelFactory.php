<?php

namespace Chatbot\Infrastructure\LanguageModel;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModel;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModelTranslate;
use Chatbot\Infrastructure\LanguageModel\Exception\BadLanguageModelName;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ModelFactory extends LanguageModelAbstractFactory
{
    public function __construct(private HttpClientInterface $client = new CurlHttpClient())
    {
    }

    public function create(string $lmName, string $context): LanguageModelInterface
    {

        switch ($lmName) {
            case "GPTModel":
                return new GPTModel($this->client, new Context(new ContextMessage($context)));
            case "GPTModelTranslate":
                return new GPTModelTranslate($this->client, $context);
            case "Parrot":
                return new Parrot();
            case "ParrotTranslate":
                return new ParrotTranslate($context);
            default:
                throw new BadLanguageModelName();
        }
    }
}
