<?php
    ob_start();
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/app/helpers/Session.php';
    require_once __DIR__ . '/app/helpers/Validator.php';
    require_once __DIR__ . '/app/helpers/Response.php';
    Session::start();
    $page = $_GET['page'] ?? 'login';
    $action = $_GET['action'] ?? null;
    if ($action) {
        switch ($action) {
            case 'login':
                require_once __DIR__ . '/app/controllers/AuthController.php';
                $controller = new AuthController();
                $controller->login();
                break;
            case 'register':
                require_once __DIR__ . '/app/controllers/AuthController.php';
                $controller = new AuthController();
                $controller->register();
                break;
            case 'logout':
                require_once __DIR__ . '/app/controllers/AuthController.php';
                $controller = new AuthController();
                $controller->logout();
                break;
            case 'toggle_theme':
                require_once __DIR__ . '/app/controllers/AuthController.php';
                $controller = new AuthController();
                $controller->toggleTheme();
                break;
            case 'generate':
                require_once __DIR__ . '/app/controllers/GeneratorController.php';
                $controller = new GeneratorController();
                $controller->generate();
                break;
            case 'regenerate':
                require_once __DIR__ . '/app/controllers/GeneratorController.php';
                $controller = new GeneratorController();
                $controller->regenerate();
                break;
            case 'get_post':
                require_once __DIR__ . '/app/controllers/HistoryController.php';
                $controller = new HistoryController();
                $controller->getPost();
                break;
            case 'update_post':
                require_once __DIR__ . '/app/controllers/HistoryController.php';
                $controller = new HistoryController();
                $controller->update();
                break;
            case 'delete_post':
                require_once __DIR__ . '/app/controllers/HistoryController.php';
                $controller = new HistoryController();
                $controller->delete();
                break;
            case 'export_post':
                require_once __DIR__ . '/app/controllers/HistoryController.php';
                $controller = new HistoryController();
                $controller->export();
                break;
            case 'search_posts':
                require_once __DIR__ . '/app/controllers/HistoryController.php';
                $controller = new HistoryController();
                $controller->search();
                break;
            case 'favorite_toggle':
                require_once __DIR__ . '/app/controllers/FavoritesController.php';
                $controller = new FavoritesController();
                $controller->toggle();
                break;
            case 'favorite_add':
                require_once __DIR__ . '/app/controllers/FavoritesController.php';
                $controller = new FavoritesController();
                $controller->add();
                break;
            case 'favorite_remove':
                require_once __DIR__ . '/app/controllers/FavoritesController.php';
                $controller = new FavoritesController();
                $controller->remove();
                break;
            default:
                http_response_code(404);
                Response::error('Action not found', 404);
        }
        exit;
    }
    switch ($page) {
        case 'login':
            if (Session::isLoggedIn()) {
                header('Location: /index.php?page=generator');
                exit;
            }
            require_once __DIR__ . '/app/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->showLogin();
            break;
        case 'register':
            if (Session::isLoggedIn()) {
                header('Location: /index.php?page=generator');
                exit;
            }
            require_once __DIR__ . '/app/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->showRegister();
            break;
        case 'generator':
            require_once __DIR__ . '/app/controllers/GeneratorController.php';
            $controller = new GeneratorController();
            $controller->index();
            break;
        case 'history':
            require_once __DIR__ . '/app/controllers/HistoryController.php';
            $controller = new HistoryController();
            $controller->index();
            break;
        case 'favorites':
            require_once __DIR__ . '/app/controllers/FavoritesController.php';
            $controller = new FavoritesController();
            $controller->index();
            break;
        default:
            if (Session::isLoggedIn()) {
                header('Location: /index.php?page=generator');
            } else {
                header('Location: /index.php?page=login');
            }
            exit;
    }
    ob_end_flush();
