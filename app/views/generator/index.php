<?php
$pageTitle = 'Generator AI';
$currentPage = 'generator';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>
<main class="main-content">
    <div class="content-header">
        <div>
            <h1 class="page-title">Generator AI ✨</h1>
            <p class="page-subtitle">Twórz profesjonalne posty na social media w sekundach</p>
        </div>
    </div>
    <?php
    $flash = Session::getFlash();
    if ($flash):
        ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
    <div class="generator-container">
        <div class="generator-form-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Nowy Post</h2>
                </div>
                <form id="generatorForm" class="generator-form">
                    <div class="form-group">
                        <label for="topic" class="form-label">O czym ma być post?</label>
                        <textarea id="topic" name="topic" class="form-textarea" rows="4"
                            placeholder="Np. 'Korzyści z regularnego ćwiczenia jogi' lub 'Nowy produkt - ekologiczne butelki'"
                            required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category" class="form-label">Platforma</label>
                            <select id="category" name="category" class="form-select">
                                <option value="general">Ogólny</option>
                                <option value="instagram">Instagram</option>
                                <option value="twitter">Twitter/X</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="facebook">Facebook</option>
                                <option value="tiktok">TikTok</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tone" class="form-label">Ton</label>
                            <select id="tone" name="tone" class="form-select">
                                <option value="professional">Profesjonalny</option>
                                <option value="casual">Swobodny</option>
                                <option value="funny">Humorystyczny</option>
                                <option value="inspiring">Inspirujący</option>
                                <option value="educational">Edukacyjny</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="length" class="form-label">Długość</label>
                            <select id="length" name="length" class="form-select">
                                <option value="short">Krótki</option>
                                <option value="medium" selected>Średni</option>
                                <option value="long">Długi</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" id="generateBtn">
                        <span id="btnText">Generuj Post</span>
                        <span class="btn-icon">✨</span>
                    </button>
                </form>
            </div>
            <?php if (!empty($recentPosts)): ?>
                <div class="card recent-posts-card">
                    <div class="card-header">
                        <h3 class="card-title">Ostatnie posty</h3>
                    </div>
                    <div class="recent-posts-list">
                        <?php foreach ($recentPosts as $post): ?>
                            <div class="recent-post-item">
                                <div class="recent-post-category">
                                    <?php echo strtoupper($post['category']); ?>
                                </div>
                                <div class="recent-post-content">
                                    <?php echo htmlspecialchars(substr($post['content'], 0, 80)) . '...'; ?>
                                </div>
                                <div class="recent-post-date">
                                    <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="generator-result-section">
            <div class="card result-card" id="resultCard" style="display: none;">
                <div class="card-header">
                    <h2 class="card-title">Wygenerowany Post</h2>
                    <div class="card-actions">
                        <button onclick="regeneratePost()" class="btn-icon" title="Regeneruj">
                            <span>🔄</span>
                        </button>
                        <button onclick="copyToClipboard()" class="btn-icon" title="Kopiuj">
                            <span>📋</span>
                        </button>
                        <button onclick="toggleFavorite()" class="btn-icon" id="favoriteBtn"
                            title="Dodaj do ulubionych">
                            <span id="favoriteIcon">⭐</span>
                        </button>
                    </div>
                </div>
                <div class="result-content" id="resultContent"></div>
                <div class="result-footer">
                    <div class="result-meta" id="resultMeta"></div>
                    <button onclick="saveAndNew()" class="btn btn-secondary">
                        <span>Generuj kolejny</span>
                        <span class="btn-icon">→</span>
                    </button>
                </div>
            </div>
            <div class="placeholder-card" id="placeholderCard">
                <div class="placeholder-icon">🎯</div>
                <h3 class="placeholder-title">Gotowy na magię AI?</h3>
                <p class="placeholder-text">Wypełnij formularz po lewej i wygeneruj profesjonalny post w sekundach!</p>
            </div>
        </div>
    </div>
</main>
<script>
    let currentPostId = null;
    let isFavorite = false;
    document.getElementById('generatorForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('generateBtn');
        const btnText = document.getElementById('btnText');
        const originalText = btnText.textContent;
        btn.disabled = true;
        btnText.textContent = 'Generuję...';
        const formData = {
            topic: document.getElementById('topic').value,
            category: document.getElementById('category').value,
            tone: document.getElementById('tone').value,
            length: document.getElementById('length').value
        };
        try {
            const response = await fetch('/index.php?action=generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            const result = await response.json();
            if (result.success) {
                currentPostId = result.data.id;
                isFavorite = false;
                document.getElementById('resultContent').textContent = result.data.content;
                document.getElementById('resultMeta').innerHTML = `
                <span class="meta-badge">${result.data.category.toUpperCase()}</span>
                <span class="meta-date">${new Date(result.data.created_at).toLocaleString('pl-PL')}</span>
            `;
                document.getElementById('placeholderCard').style.display = 'none';
                document.getElementById('resultCard').style.display = 'block';
                updateFavoriteIcon();
                showToast(result.message, 'success');
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Wystąpił błąd podczas generowania', 'error');
        } finally {
            btn.disabled = false;
            btnText.textContent = originalText;
        }
    });
    async function regeneratePost() {
        if (!currentPostId) return;
        const btn = event.target.closest('button');
        btn.disabled = true;
        try {
            const response = await fetch('/index.php?action=regenerate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ post_id: currentPostId })
            });
            const result = await response.json();
            if (result.success) {
                document.getElementById('resultContent').textContent = result.data.content;
                showToast(result.message, 'success');
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Wystąpił błąd podczas regeneracji', 'error');
        } finally {
            btn.disabled = false;
        }
    }
    async function toggleFavorite() {
        if (!currentPostId) return;
        try {
            const response = await fetch('/index.php?action=favorite_toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ post_id: currentPostId })
            });
            const result = await response.json();
            if (result.success) {
                isFavorite = result.data.is_favorite;
                updateFavoriteIcon();
                showToast(result.message, 'success');
            }
        } catch (error) {
            showToast('Wystąpił błąd', 'error');
        }
    }
    function updateFavoriteIcon() {
        const icon = document.getElementById('favoriteIcon');
        icon.textContent = isFavorite ? '⭐' : '☆';
    }
    function copyToClipboard() {
        const content = document.getElementById('resultContent').textContent;
        navigator.clipboard.writeText(content).then(() => {
            showToast('Skopiowano do schowka! 📋', 'success');
        });
    }
    function saveAndNew() {
        document.getElementById('topic').value = '';
        document.getElementById('topic').focus();
        document.getElementById('placeholderCard').style.display = 'block';
        document.getElementById('resultCard').style.display = 'none';
        currentPostId = null;
    }
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>