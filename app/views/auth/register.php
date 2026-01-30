<?php
$pageTitle = 'Rejestracja';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">🚀</div>
            <h1 class="auth-title">Dołącz do nas!</h1>
            <p class="auth-subtitle">Utwórz konto w SocialAI Pro</p>
        </div>

        <?php
        $flash = Session::getFlash();
        if ($flash):
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form action="/index.php?action=register" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username" class="form-label">Nazwa użytkownika</label>
                <input type="text" id="username" name="username" class="form-input"
                    placeholder="Wybierz nazwę użytkownika" required autofocus pattern="[a-zA-Z0-9]+"
                    title="Tylko litery i cyfry">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="twoj@email.com" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Hasło</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Minimum 6 znaków"
                    required minlength="6">
            </div>

            <div class="form-group">
                <label for="password_confirm" class="form-label">Potwierdź hasło</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input"
                    placeholder="Powtórz hasło" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <span>Utwórz konto</span>
                <span class="btn-icon">→</span>
            </button>
        </form>

        <div class="auth-footer">
            <p>Masz już konto? <a href="/index.php?page=login" class="auth-link">Zaloguj się</a></p>
        </div>
    </div>

    <div class="auth-decoration">
        <div class="decoration-circle decoration-circle-1"></div>
        <div class="decoration-circle decoration-circle-2"></div>
        <div class="decoration-circle decoration-circle-3"></div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>