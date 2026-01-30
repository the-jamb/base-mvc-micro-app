<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Response.php';
class HistoryController
{
    private $postModel;
    public function __construct()
    {
        $this->postModel = new Post();
    }
    public function index()
    {
        Session::requireAuth();
        $userId = Session::getUserId();
        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $category = $_GET['category'] ?? null;
        $perPage = 12;
        $posts = $this->postModel->getByUser($userId, $page, $perPage, $category);
        $totalPosts = $this->postModel->countByUser($userId, $category);
        $totalPages = ceil($totalPosts / $perPage);
        require_once __DIR__ . '/../views/history/index.php';
    }
    public function getPost()
    {
        Session::requireAuth();
        $postId = $_GET['id'] ?? null;
        if (!$postId) {
            Response::error('Post ID jest wymagane', 400);
        }
        $post = $this->postModel->findById($postId, Session::getUserId());
        if (!$post) {
            Response::error('Post nie został znaleziony', 404);
        }
        Response::success($post);
    }
    public function update()
    {
        Session::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $postId = $data['id'] ?? null;
        $content = $data['content'] ?? null;
        if (!$postId || !$content) {
            Response::error('Post ID i treść są wymagane', 400);
        }
        $success = $this->postModel->update($postId, Session::getUserId(), [
            'content' => $content
        ]);
        if (!$success) {
            Response::error('Nie udało się zaktualizować posta', 500);
        }
        Response::success(null, 'Post zaktualizowany pomyślnie! ✅');
    }
    public function delete()
    {
        Session::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $postId = $data['id'] ?? null;
        if (!$postId) {
            Response::error('Post ID jest wymagane', 400);
        }
        $success = $this->postModel->delete($postId, Session::getUserId());
        if (!$success) {
            Response::error('Nie udało się usunąć posta', 500);
        }
        Response::success(null, 'Post usunięty pomyślnie! 🗑️');
    }
    public function export()
    {
        Session::requireAuth();
        $postId = $_GET['id'] ?? null;
        $format = $_GET['format'] ?? 'txt';
        if (!$postId) {
            Response::error('Post ID jest wymagane', 400);
        }
        $post = $this->postModel->findById($postId, Session::getUserId());
        if (!$post) {
            Response::error('Post nie został znaleziony', 404);
        }
        if ($format === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="post_' . $postId . '.json"');
            echo json_encode($post, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="post_' . $postId . '.txt"');
            echo "=== SocialAI Pro - Eksport Posta ===\n\n";
            echo "Kategoria: " . strtoupper($post['category']) . "\n";
            echo "Temat: " . $post['prompt'] . "\n";
            echo "Data: " . $post['created_at'] . "\n";
            echo "\n--- Treść ---\n\n";
            echo $post['content'];
        }
        exit;
    }
    public function search()
    {
        Session::requireAuth();
        $query = $_GET['q'] ?? '';
        if (strlen($query) < 2) {
            Response::error('Zapytanie musi mieć minimum 2 znaki', 400);
        }
        $posts = $this->postModel->search(Session::getUserId(), $query);
        Response::success($posts);
    }
}
