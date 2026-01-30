<?php
class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $domain = $_SERVER['HTTP_HOST'] ?? '';
            $domain = explode(':', $domain)[0];
            session_set_cookie_params([
                'lifetime' => 7200,
                'path' => '/',
                'domain' => ($domain === 'localhost') ? '' : $domain,
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }
    public static function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    public static function requireAuth()
    {
        if (!self::isLoggedIn()) {
            header('Location: /index.php?page=login');
            exit;
        }
    }
    public static function getUserId()
    {
        return self::get('user_id');
    }
    public static function flash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    public static function getFlash()
    {
        $flash = self::get('flash') ?? null;
        self::remove('flash');
        return $flash;
    }
}
