<?php
require_once __DIR__ . '/../../config/database.php';
class User
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    public function create($username, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);
    }
    public function findByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    public function findByUsernameOrEmail($identity)
    {
        $sql = "SELECT * FROM users WHERE username = :identity OR email = :identity LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['identity' => $identity]);
        return $stmt->fetch();
    }
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    public function updateTheme($userId, $theme)
    {
        $sql = "UPDATE users SET theme = :theme WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['theme' => $theme, 'id' => $userId]);
    }
    public function updateLastLogin($userId)
    {
        $sql = "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $userId]);
    }
}
