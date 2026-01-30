<?php
class Config
{
    private static $config = null;
    public static function load()
    {
        if (self::$config === null) {
            $envFile = dirname(__DIR__) . '/.env';
            if (!file_exists($envFile)) {
                return;
            }
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0)
                    continue;
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    if (preg_match('/^"(.*)"$/', $value, $matches)) {
                        $value = $matches[1];
                    }
                    self::$config[$name] = $value;
                }
            }
        }
    }
    public static function get($key, $default = null)
    {
        if (self::$config === null)
            self::load();
        return self::$config[$key] ?? $default;
    }
    public static function isDebug()
    {
        return self::get('APP_DEBUG') === 'true';
    }
}
