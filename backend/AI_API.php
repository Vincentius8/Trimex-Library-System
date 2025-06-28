<?php
/**
 * DeepSeek API Integration for Library Systems
 * 
 * @package    TrimexLibraryAI
 * @version    1.2
 * @author     Your Name
 * @license    MIT
 */

// ======================
// Configuration Settings
// ======================
define('DEEPSEEK_API_ENDPOINT', 'https://api.deepseek.com/v1/chat/completions');
define('DEEPSEEK_MODEL', 'deepseek-chat');
define('API_TIMEOUT', 1000); // Seconds
define('MAX_RESPONSE_TOKENS', 500);

// ==================
// Security Settings
// ==================
class ApiConfig {
    public static function getApiKey() {
        return getenv('DEEPSEEK_API_KEY'); // Store in environment variable
    }
}

// =====================
// Core API Function
// =====================
function queryDeepSeek(string $userPrompt, string $language = 'en'): array {
    try {
        // Validate input
        $cleanPrompt = htmlspecialchars(strip_tags(trim($userPrompt)));
        if (empty($cleanPrompt)) {
            throw new InvalidArgumentException('Empty prompt provided');
        }

        // Get secure API key
        $apiKey = ApiConfig::getApiKey();
        if (!$apiKey) {
            throw new RuntimeException('API key not configured');
        }

        // Prepare system message based on language
        $systemMessage = [
            'role' => 'system',
            'content' => "You are TrimexLib, the AI assistant for Trimex Library. " .
                "Respond in " . ($language === 'tl' ? "Filipino" : "English") . ". " .
                "Focus on: " .
                "- Book recommendations\n" .
                "- Research assistance\n" .
                "- Library services info\n" .
                "- Educational resources\n" .
                "Keep responses under 300 words."
        ];

        // Build request payload
        $payload = [
            'model' => DEEPSEEK_MODEL,
            'messages' => [
                $systemMessage,
                ['role' => 'user', 'content' => $cleanPrompt]
            ],
            'max_tokens' => MAX_RESPONSE_TOKENS,
            'temperature' => 0.7
        ];

        // Initialize cURL with modern settings
        $ch = curl_init();
        $curlOptions = [
            CURLOPT_URL => DEEPSEEK_API_ENDPOINT,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => API_TIMEOUT,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => '/path/to/cacert.pem' // Update this path
        ];

        curl_setopt_array($ch, $curlOptions);

        // Execute with multi-handler for better timeout control
        $mh = curl_multi_init();
        curl_multi_add_handle($mh, $ch);
        
        $active = null;
        do {
            $status = curl_multi_exec($mh, $active);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

        $response = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_multi_remove_handle($mh, $ch);
        curl_multi_close($mh);

        // Handle errors
        if ($error) {
            throw new RuntimeException("cURL Error: $error");
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("API returned HTTP $httpCode");
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response');
        }

        // Validate API response structure
        if (!isset($data['choices'][0]['message']['content'])) {
            error_log('Invalid API response structure: ' . print_r($data, true));
            throw new RuntimeException('Unexpected API response format');
        }

        return [
            'success' => true,
            'response' => trim($data['choices'][0]['message']['content']),
            'usage' => $data['usage'] ?? []
        ];

    } catch (Exception $e) {
        // Log detailed error
        error_log('AI_API Error: ' . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Sorry, the library assistant is currently unavailable. ' .
                       'Please try again later or contact library staff.',
            'error_code' => $e->getCode()
        ];
    }
}

// ======================
// Example Usage (Test)
// ======================
if (php_sapi_name() === 'cli') {
    // Test query when run from command line
    $testQuery = "Recommend books about Philippine history";
    $result = queryDeepSeek($testQuery);
    
    echo "=== Test Query ===\n";
    echo "Input: $testQuery\n\n";
    echo "=== AI Response ===\n";
    echo ($result['success']) ? $result['response'] : "Error: " . $result['error'];
    echo "\n\n";
}
?>