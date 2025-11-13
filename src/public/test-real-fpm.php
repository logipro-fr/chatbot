<?php
header("Content-Type: text/plain");
echo "=== Test RÉEL dans contexte PHP-FPM (via nginx) ===\n\n";
echo "getenv(\"CHATBOT_KEY_API\"): " . (getenv("CHATBOT_KEY_API") ? "OUI (" . getenv("CHATBOT_KEY_API") . ")" : "NON") . "\n";
echo "isset(\$_ENV[\"CHATBOT_KEY_API\"]): " . (isset($_ENV["CHATBOT_KEY_API"]) ? "OUI (" . $_ENV["CHATBOT_KEY_API"] . ")" : "NON") . "\n";
echo "\nToutes les variables \$_ENV:\n";
foreach ($_ENV as $key => $value) {
    if (strpos($key, "CHATBOT") !== false || strpos($key, "KEY") !== false) {
        echo "  $key = " . substr($value, 0, 20) . "...\n";
    }
}
