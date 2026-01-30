<?php
class Validator
{
    private $errors = [];
    private $data = [];
    public function __construct($data = [])
    {
        $this->data = $data;
    }
    public function required($field, $message = null)
    {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? "Pole {$field} jest wymagane.";
        }
        return $this;
    }
    public function email($field, $message = null)
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Nieprawidłowy format adresu email.";
        }
        return $this;
    }
    public function min($field, $min, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? "Pole {$field} musi mieć minimum {$min} znaków.";
        }
        return $this;
    }
    public function max($field, $max, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? "Pole {$field} może mieć maksymalnie {$max} znaków.";
        }
        return $this;
    }
    public function match($field, $matchField, $message = null)
    {
        if (
            isset($this->data[$field]) && isset($this->data[$matchField])
            && $this->data[$field] !== $this->data[$matchField]
        ) {
            $this->errors[$field] = $message ?? "Pola {$field} i {$matchField} muszą być identyczne.";
        }
        return $this;
    }
    public function alphanumeric($field, $message = null)
    {
        if (isset($this->data[$field]) && !ctype_alnum($this->data[$field])) {
            $this->errors[$field] = $message ?? "Pole {$field} może zawierać tylko litery i cyfry.";
        }
        return $this;
    }
    public function custom($field, $callback, $message = null)
    {
        if (isset($this->data[$field]) && !$callback($this->data[$field])) {
            $this->errors[$field] = $message ?? "Pole {$field} nie przeszło walidacji.";
        }
        return $this;
    }
    public function passes()
    {
        return empty($this->errors);
    }
    public function fails()
    {
        return !$this->passes();
    }
    public function errors()
    {
        return $this->errors;
    }
    public function firstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    public static function sanitize($value)
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    public static function sanitizeArray($data)
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = is_array($value)
                ? self::sanitizeArray($value)
                : self::sanitize($value);
        }
        return $sanitized;
    }
}
