<?php
require_once __DIR__ . '/../models/Favorite.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Response.php';
class FavoritesController
{
    private $favoriteModel;
    private $postModel;
    public function __construct()
    {
        $this->favoriteModel = new Favorite();
        $this->postModel = new Post();
    }
    public function index()
    {
        Session::requireAuth();
        $userId = Session::getUserId();
        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $perPage = 12;
        $favorites = $this->favoriteModel->getByUser($userId, $page, $perPage);
        $totalFavorites = $this->favoriteModel->countByUser($userId);
        $totalPages = ceil($totalFavorites / $perPage);
        require_once __DIR__ . '/../views/favorites/index.php';
    }
    public function toggle()
    {
        Session::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $postId = $data['post_id'] ?? null;
        if (!$postId) {
            Response::error('Post ID jest wymagane', 400);
        }
        $post = $this->postModel->findById($postId, Session::getUserId());
        if (!$post) {
            Response::error('Post nie został znaleziony', 404);
        }
        $isFavorite = $this->favoriteModel->toggle(Session::getUserId(), $postId);
        Response::success([
            'is_favorite' => $isFavorite
        ], $isFavorite ? 'Dodano do ulubionych! ⭐' : 'Usunięto z ulubionych');
    }
    public function add()
    {
        Session::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $postId = $data['post_id'] ?? null;
        if (!$postId) {
            Response::error('Post ID jest wymagane', 400);
        }
        $success = $this->favoriteModel->add(Session::getUserId(), $postId);
        if (!$success) {
            Response::error('Nie udało się dodać do ulubionych', 500);
        }
        Response::success(null, 'Dodano do ulubionych! ⭐');
    }
    public function remove()
    {
        Session::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $postId = $data['post_id'] ?? null;
        if (!$postId) {
            Response::error('Post ID jest wymagane', 400);
        }
        $success = $this->favoriteModel->remove(Session::getUserId(), $postId);
        if (!$success) {
            Response::error('Nie udało się usunąć z ulubionych', 500);
        }
        Response::success(null, 'Usunięto z ulubionych');
    }
}
