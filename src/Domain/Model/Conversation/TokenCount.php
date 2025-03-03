<?php

namespace Chatbot\Domain\Model\Conversation;

use function Safe\preg_split;

class TokenCount
{
    public function countToken(string $prompt): int
    {
        $KEYWORDS_SEPARATORS = preg_split("/[\s,.;:!?'-]+/", $prompt, -1, PREG_SPLIT_NO_EMPTY);


            $result = count($KEYWORDS_SEPARATORS);
            return $result;
    }
}
