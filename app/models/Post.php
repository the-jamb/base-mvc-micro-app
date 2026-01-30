<?php
require_once __DIR__ . '/../../config/database.php';
class Post
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    public function create($userId, $category, $prompt, $content, $tone, $length)
    {
        try {
            $sql = "INSERT INTO posts (user_id, category, prompt, content, tone, length) 
                    VALUES (:user_id, :category, :prompt, :content, :tone, :length)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'category' => $category ?? 'general',
                'prompt' => $prompt,
                'content' => $content,
                'tone' => $tone,
                'length' => $length
            ]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    public function findById($id, $userId = null)
    {
        $sql = "SELECT p.*, (SELECT COUNT(*) FROM favorites WHERE post_id = p.id AND user_id = :user_id) as is_favorite FROM posts p WHERE p.id = :id";
        if ($userId !== null) {
            $sql .= " AND p.user_id = :user_id_check";
        }
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $params = ['id' => $id, 'user_id' => $userId ?? 0];
        if ($userId !== null) {
            $params['user_id_check'] = $userId;
        }
        $stmt->execute($params);
        return $stmt->fetch();
    }
    public function getByUser($userId, $page = 1, $perPage = 10, $category = null)
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, (SELECT COUNT(*) FROM favorites WHERE post_id = p.id AND user_id = :user_id) as is_favorite FROM posts p WHERE p.user_id = :user_id";
        $params = ['user_id' => $userId];
        if ($category) {
            $sql .= " AND p.category = :category";
            $params['category'] = $category;
        }
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function countByUser($userId, $category = null)
    {
        $sql = "SELECT COUNT(*) FROM posts WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        if ($category) {
            $sql .= " AND category = :category";
            $params['category'] = $category;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    public function update($id, $userId, $data)
    {
        $fields = [];
        $params = ['id' => $id, 'user_id' => $userId];
        if (isset($data['content'])) {
            $fields[] = "content = :content";
            $params['content'] = $data['content'];
        }
        if (isset($data['category'])) {
            $fields[] = "category = :category";
            $params['category'] = $data['category'];
        }
        if (empty($fields)) {
            return false;
        }
        $sql = "UPDATE posts SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    public function delete($id, $userId)
    {
        $sql = "DELETE FROM posts WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
    public function getRecent($userId, $limit = 5)
    {
        $sql = "SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getByCategory($userId, $category, $limit = 10)
    {
        $sql = "SELECT * FROM posts WHERE user_id = :user_id AND category = :category ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':category', $category);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function search($userId, $query)
    {
        $sql = "SELECT * FROM posts WHERE user_id = :user_id AND (content LIKE :query OR prompt LIKE :query) ORDER BY created_at DESC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'query' => "%{$query}%"]);
        return $stmt->fetchAll();
    }
}
