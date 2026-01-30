<?php
class OpenRouterAPI
{
    private $apiKey;
    private $model;
    private $baseUrl = 'https://openrouter.ai/api/v1';
    private $fallbackModels = [
        'openai/gpt-4o-mini',
        'openai/gpt-4o',
        'google/gemini-flash-1.5',
        'anthropic/claude-3-haiku',
        'meta-llama/llama-3.1-405b-instruct'
    ];
    public function __construct()
    {
        $this->apiKey = Config::get('OPENROUTER_API_KEY');
        $this->model = Config::get('OPENROUTER_MODEL', 'openai/gpt-4o-mini');
        if (empty($this->apiKey)) {
            throw new Exception('OpenRouter API key not configured');
        }
    }
    public function generate($prompt, $options = [])
    {
        $modelsToTry = array_merge([$this->model], $this->fallbackModels);
        $modelsToTry = array_unique($modelsToTry);
        $lastError = "";
        foreach ($modelsToTry as $modelToTry) {
            try {
                return $this->generateWithModel($modelToTry, $prompt, $options);
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                $this->logError("Model {$modelToTry} attempt failed: " . $lastError);
                continue;
            }
        }
        $this->logError("ALL Models failed. Last error: " . $lastError);
        return [
            'content' => "BŁĄD API: Nie udało się połączyć z żadnym modelem. Sprawdź logi lub klucz API.\n\nPróba dla tematu: " . $prompt,
            'model' => 'error-fallback',
            'usage' => null,
            'finish_reason' => 'error'
        ];
    }
    private function generateWithModel($model, $prompt, $options = [])
    {
        $systemPrompt = $options['system'] ?? 'Jesteś profesjonalnym copywriterem specjalizującym się w tworzeniu angażujących postów na social media w języku polskim. Twórz kreatywne, przyciągające uwagę treści.';
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 800;
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];
        $ch = curl_init($this->baseUrl . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . Config::get('APP_URL', 'http://localhost'),
                'X-Title: ' . Config::get('APP_NAME', 'SocialAI Pro')
            ],
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 15
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        if ($curlError) {
            throw new Exception('CURL error: ' . $curlError);
        }
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $msg = $errorData['error']['message'] ?? ($errorData['error'] ?? $response);
            throw new Exception("HTTP {$httpCode}: " . (is_array($msg) ? json_encode($msg) : $msg));
        }
        $data = json_decode($response, true);
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception('Invalid API response structure');
        }
        return [
            'content' => trim($data['choices'][0]['message']['content']),
            'model' => $data['model'] ?? $model,
            'usage' => $data['usage'] ?? null,
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? null
        ];
    }
    public function generatePost($topic, $category = 'general', $tone = 'professional', $length = 'medium')
    {
        $platform = [
            'instagram' => 'Instagram (używaj emoji, dodaj hashtagi)',
            'twitter' => 'Twitter/X (do 280 znaków)',
            'linkedin' => 'LinkedIn (profesjonalny styl)',
            'facebook' => 'Facebook (angażujący styl)',
            'tiktok' => 'TikTok (kreatywne napisy do video)',
            'general' => 'ogólny post'
        ][$category] ?? 'ogólny post';
        $prompt = "Napisz post na {$platform} o temacie: {$topic}. Ton wypowiedzi: {$tone}. Długość: {$length}. Napisz TYLKO treść posta w języku polskim.";
        $result = $this->generate($prompt);
        return $result['content'];
    }
    private function logError($message)
    {
        $logFile = __DIR__ . '/../../logs/api_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }
}
