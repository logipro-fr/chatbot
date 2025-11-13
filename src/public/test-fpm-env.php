<?php
header("Content-Type: text/plain");
echo "=== Test CHATBOT_KEY_API dans contexte PHP-FPM ===\n\n";
echo "getenv(\"CHATBOT_KEY_API\"): " . (getenv("CHATBOT_KEY_API") ? "OUI (longueur: " . strlen(getenv("CHATBOT_KEY_API")) . ")" : "NON") . "\n";
echo "isset(\$_ENV[\"CHATBOT_KEY_API\"]): " . (isset($_ENV["CHATBOT_KEY_API"]) ? "OUI (longueur: " . strlen($_ENV["CHATBOT_KEY_API"]) . ")" : "NON") . "\n";
if (isset($_ENV["CHATBOT_KEY_API"]) && strlen($_ENV["CHATBOT_KEY_API"]) > 0) {
    echo "Préfixe: " . substr($_ENV["CHATBOT_KEY_API"], 0, 10) . "...\n";
    echo "✓ Variable accessible dans PHP-FPM\n";
} else {
    echo "✗ Variable non accessible dans PHP-FPM\n";
}
