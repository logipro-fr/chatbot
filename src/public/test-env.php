<?php
header("Content-Type: text/plain");
echo "Test CHATBOT_KEY_API dans contexte web:\n";
echo "isset(\$_ENV[\"CHATBOT_KEY_API\"]): " . (isset($_ENV["CHATBOT_KEY_API"]) ? "OUI" : "NON") . "\n";
echo "getenv(\"CHATBOT_KEY_API\"): " . (getenv("CHATBOT_KEY_API") ? "OUI" : "NON") . "\n";
if (isset($_ENV["CHATBOT_KEY_API"])) {
    echo "Longueur: " . strlen($_ENV["CHATBOT_KEY_API"]) . "\n";
    echo "Préfixe: " . substr($_ENV["CHATBOT_KEY_API"], 0, 10) . "...\n";
}
