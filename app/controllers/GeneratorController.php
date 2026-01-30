<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../helpers/OpenRouterAPI.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Response.php';
class GeneratorController
{
    private $postModel;
    private $apiClient;
    public function __construct()
    {
        $this->postModel = new Post();
        $this->apiClient = new OpenRouterAPI();
    }
    public function index()
    {
        Session::requireAuth();
        $recentPosts = $this->postModel->getByUser(Session::getUserId(), 1, 5);
        require_once __DIR__ . '/../views/generator/index.php';
    }
    public function generate()
    {
        Session::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $topic = $data['topic'] ?? '';
        $category = $data['category'] ?? 'general';
        $tone = $data['tone'] ?? 'professional';
        $length = $data['length'] ?? 'medium';
        if (empty($topic)) {
            Response::error('Temat jest wymagany', 400);
        }
        if (!$this->checkRateLimit()) {
            Response::error('Zbyt wiele żądań. Spróbuj za chwilę.', 429);
        }
        try {
            $content = $this->apiClient->generatePost($topic, $category, $tone, $length);
            $postId = $this->postModel->create(
                Session::getUserId(),
                $category,
                $topic,
                $content,
                $tone,
                $length
            );
            if ($postId) {
                Response::success([
                    'id' => $postId,
                    'content' => $content,
                    'category' => $category,
                    'created_at' => date('Y-m-d H:i:s')
                ], 'Post wygenerowany pomyślnie! ✨');
            } else {
                Response::error('Błąd podczas zapisywania posta', 500);
            }
        } catch (Exception $e) {
            Response::error('Błąd generatora: ' . $e->getMessage(), 500);
        }
    }
    public function regenerate()
    {
        Session::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $postId = $data['post_id'] ?? null;
        if (!$postId) {
            Response::error('Post ID jest wymagany', 400);
        }
        $post = $this->postModel->findById($postId, Session::getUserId());
        if (!$post) {
            Response::error('Post nie został znaleziony', 404);
        }
        try {
            $content = $this->apiClient->generatePost(
                $post['prompt'],
                $post['category'],
                $post['tone'] ?? 'professional',
                $post['length'] ?? 'medium'
            );
            $this->postModel->update($postId, Session::getUserId(), $content);
            Response::success(['content' => $content], 'Zregenerowano post! 🔄');
        } catch (Exception $e) {
            Response::error('Błąd podcza regeneracji: ' . $e->getMessage(), 500);
        }
    }
    private function checkRateLimit()
    {
        $userId = Session::getUserId();
        $logFile = __DIR__ . "/../../logs/rate_limit_{$userId}.json";
        $limit = Config::get('RATE_LIMIT_REQUESTS', 10);
        $window = Config::get('RATE_LIMIT_WINDOW', 60);
        $data = ['count' => 0, 'start' => time()];
        if (file_exists($logFile)) {
            $data = json_decode(file_get_contents($logFile), true);
        }
        if (time() - $data['start'] > $window) {
            $data = ['count' => 1, 'start' => time()];
        } else {
            if ($data['count'] >= $limit) {
                return false;
            }
            $data['count']++;
        }
        file_put_contents($logFile, json_encode($data));
        return true;
    }
}
