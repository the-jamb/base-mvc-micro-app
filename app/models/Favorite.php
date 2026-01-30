<?php
require_once __DIR__ . '/../../config/database.php';
class Favorite
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    public function add($userId, $postId)
    {
        try {
            $sql = "INSERT INTO favorites (user_id, post_id) VALUES (:user_id, :post_id)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['user_id' => $userId, 'post_id' => $postId]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000)
                return true;
            error_log($e->getMessage());
            return false;
        }
    }
    public function remove($userId, $postId)
    {
        $sql = "DELETE FROM favorites WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['user_id' => $userId, 'post_id' => $postId]);
    }
    public function isFavorite($userId, $postId)
    {
        $sql = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'post_id' => $postId]);
        return $stmt->fetchColumn() > 0;
    }
    public function getByUser($userId, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, f.created_at as favorited_at, 1 as is_favorite FROM favorites f INNER JOIN posts p ON f.post_id = p.id WHERE f.user_id = :user_id ORDER BY f.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function countByUser($userId)
    {
        $sql = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }
    public function toggle($userId, $postId)
    {
        if ($this->isFavorite($userId, $postId)) {
            $this->remove($userId, $postId);
            return false;
        } else {
            $this->add($userId, $postId);
            return true;
        }
    }
}
