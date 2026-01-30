<?php
    require_once __DIR__ . '/../models/User.php';
    require_once __DIR__ . '/../helpers/Session.php';
    require_once __DIR__ . '/../helpers/Validator.php';
    require_once __DIR__ . '/../helpers/Response.php';

    class AuthController
    {
        private $userModel;

        public function __construct()
        {
            $this->userModel = new User();
        }

        public function showLogin()
        {
            require_once __DIR__ . '/../views/auth/login.php';
        }

        public function showRegister()
        {
            require_once __DIR__ . '/../views/auth/register.php';
        }

        public function login()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /index.php?page=login');
                exit;
            }
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $user = $this->userModel->findByUsernameOrEmail($username);
            if ($user && password_verify($password, $user['password'])) {
                Session::set('user_id', $user['id']);
                Session::set('username', $user['username']);
                Session::set('theme', $user['theme'] ?? 'dark');
                $this->userModel->updateLastLogin($user['id']);
                header('Location: /index.php?page=generator');
                exit;
            }
            Session::flash('error', 'Nieprawidłowa nazwa użytkownika lub hasło');
            header('Location: /index.php?page=login');
        }

        public function register()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /index.php?page=register');
                exit;
            }
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $validator = new Validator($_POST);
            $validator->required('username')->min('username', 3);
            $validator->required('email')->email('email');
            $validator->required('password')->min('password', 6);
            $validator->required('password_confirm')->match('password_confirm', 'password');
            if ($validator->fails()) {
                Session::flash('error', array_values($validator->errors())[0]);
                header('Location: /index.php?page=register');
                exit;
            }
            if ($this->userModel->findByUsername($username)) {
                Session::flash('error', 'Ta nazwa użytkownika jest już zajęta');
                header('Location: /index.php?page=register');
                exit;
            }
            if ($this->userModel->findByEmail($email)) {
                Session::flash('error', 'Ten adres email jest już zarejestrowany');
                header('Location: /index.php?page=register');
                exit;
            }
            try {
                if ($this->userModel->create($username, $email, $password)) {
                    Session::flash('success', 'Konto utworzone pomyślnie. Możesz się zalogować.');
                    header('Location: /index.php?page=login');
                } else {
                    throw new Exception("Create failed");
                }
            } catch (Exception $e) {
                Session::flash('error', 'Wystąpił błąd podczas rejestracji. Spróbuj inną nazwę lub email.');
                header('Location: /index.php?page=register');
            }
        }

        public function logout()
        {
            Session::destroy();
            header('Location: /index.php?page=login');
        }

        public function toggleTheme()
        {
            Session::requireAuth();
            $currentTheme = Session::get('theme', 'dark');
            $newTheme = $currentTheme === 'dark' ? 'light' : 'dark';
            $this->userModel->updateTheme(Session::getUserId(), $newTheme);
            Session::set('theme', $newTheme);
            Response::success(['theme' => $newTheme]);
        }
    }
