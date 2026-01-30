<?php
$pageTitle = 'Logowanie';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">🚀</div>
            <h1 class="auth-title">Witaj ponownie!</h1>
            <p class="auth-subtitle">Zaloguj się do SocialAI Pro</p>
        </div>

        <?php
        $flash = Session::getFlash();
        if ($flash):
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form action="/index.php?action=login" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username" class="form-label">Nazwa użytkownika lub email</label>
                <input type="text" id="username" name="username" class="form-input"
                    placeholder="Wpisz nazwę użytkownika lub email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Hasło</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Wpisz hasło"
                    required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <span>Zaloguj się</span>
                <span class="btn-icon">→</span>
            </button>
        </form>

        <div class="auth-footer">
            <p>Nie masz konta? <a href="/index.php?page=register" class="auth-link">Zarejestruj się</a></p>
        </div>
    </div>

    <div class="auth-decoration">
        <div class="decoration-circle decoration-circle-1"></div>
        <div class="decoration-circle decoration-circle-2"></div>
        <div class="decoration-circle decoration-circle-3"></div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>