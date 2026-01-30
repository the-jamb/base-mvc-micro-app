<?php
$pageTitle = 'Historia Postów';
$currentPage = 'history';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>
<main class="main-content">
    <div class="content-header">
        <div>
            <h1 class="page-title">Historia Postów 📝</h1>
            <p class="page-subtitle">Przeglądaj i zarządzaj wszystkimi wygenerowanymi postami</p>
        </div>
        <div class="header-actions">
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="Szukaj postów...">
                <span class="search-icon">🔍</span>
            </div>
        </div>
    </div>
    <div class="filter-bar">
        <a href="/index.php?page=history"
            class="filter-btn <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">Wszystkie</a>
        <a href="/index.php?page=history&category=instagram"
            class="filter-btn <?php echo ($_GET['category'] ?? '') === 'instagram' ? 'active' : ''; ?>">Instagram</a>
        <a href="/index.php?page=history&category=twitter"
            class="filter-btn <?php echo ($_GET['category'] ?? '') === 'twitter' ? 'active' : ''; ?>">Twitter</a>
        <a href="/index.php?page=history&category=linkedin"
            class="filter-btn <?php echo ($_GET['category'] ?? '') === 'linkedin' ? 'active' : ''; ?>">LinkedIn</a>
        <a href="/index.php?page=history&category=facebook"
            class="filter-btn <?php echo ($_GET['category'] ?? '') === 'facebook' ? 'active' : ''; ?>">Facebook</a>
        <a href="/index.php?page=history&category=tiktok"
            class="filter-btn <?php echo ($_GET['category'] ?? '') === 'tiktok' ? 'active' : ''; ?>">TikTok</a>
    </div>
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3 class="empty-title">Brak postów</h3>
            <p class="empty-text">Nie masz jeszcze żadnych wygenerowanych postów w tej kategorii.</p>
            <a href="/index.php?page=generator" class="btn btn-primary">
                <span>Wygeneruj pierwszy post</span>
                <span class="btn-icon">✨</span>
            </a>
        </div>
    <?php else: ?>
        <div class="posts-grid" id="postsGrid">
            <?php foreach ($posts as $post): ?>
                <div class="post-card" data-post-id="<?php echo $post['id']; ?>">
                    <div class="post-card-header">
                        <span class="post-category"><?php echo strtoupper($post['category']); ?></span>
                        <button onclick="toggleFavorite(<?php echo $post['id']; ?>, this)"
                            class="btn-icon favorite-btn <?php echo $post['is_favorite'] ? 'active' : ''; ?>">
                            <span><?php echo $post['is_favorite'] ? '⭐' : '☆'; ?></span>
                        </button>
                    </div>
                    <div class="post-card-content">
                        <div class="post-prompt"><?php echo htmlspecialchars($post['prompt']); ?></div>
                        <div class="post-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                    </div>
                    <div class="post-card-footer">
                        <div class="post-date"><?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?></div>
                        <div class="post-actions">
                            <button onclick="copyPost(<?php echo $post['id']; ?>)" class="btn-icon"
                                title="Kopiuj"><span>📋</span></button>
                            <button onclick="editPost(<?php echo $post['id']; ?>)" class="btn-icon"
                                title="Edytuj"><span>✏️</span></button>
                            <button onclick="exportPost(<?php echo $post['id']; ?>)" class="btn-icon"
                                title="Eksportuj"><span>💾</span></button>
                            <button onclick="deletePost(<?php echo $post['id']; ?>)" class="btn-icon btn-danger"
                                title="Usuń"><span>🗑️</span></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=history&p=<?php echo $page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?>"
                        class="pagination-btn">← Poprzednia</a>
                <?php endif; ?>
                <div class="pagination-info">Strona <?php echo $page; ?> z <?php echo $totalPages; ?></div>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=history&p=<?php echo $page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?>"
                        class="pagination-btn">Następna →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeEditModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edytuj Post</h3>
            <button onclick="closeEditModal()" class="btn-icon"><span>✕</span></button>
        </div>
        <div class="modal-body">
            <textarea id="editContent" class="form-textarea" rows="10"></textarea>
        </div>
        <div class="modal-footer">
            <button onclick="closeEditModal()" class="btn btn-secondary">Anuluj</button>
            <button onclick="saveEdit()" class="btn btn-primary">Zapisz zmiany</button>
        </div>
    </div>
</div>
<script>
    let currentEditPostId = null;
    async function toggleFavorite(postId, btn) {
        try {
            const response = await fetch('/index.php?action=favorite_toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId })
            });
            const result = await response.json();
            if (result.success) {
                const icon = btn.querySelector('span');
                icon.textContent = result.data.is_favorite ? '⭐' : '☆';
                btn.classList.toggle('active', result.data.is_favorite);
                showToast(result.message, 'success');
            }
        } catch (error) { showToast('Wystąpił błąd', 'error'); }
    }
    function copyPost(postId) {
        const card = document.querySelector(`[data-post-id="${postId}"]`);
        const content = card.querySelector('.post-text').textContent;
        navigator.clipboard.writeText(content).then(() => {
            showToast('Skopiowano do schowka! 📋', 'success');
        });
    }
    async function editPost(postId) {
        try {
            const response = await fetch(`/index.php?action=get_post&id=${postId}`);
            const result = await response.json();
            if (result.success) {
                currentEditPostId = postId;
                document.getElementById('editContent').value = result.data.content;
                document.getElementById('editModal').style.display = 'flex';
            }
        } catch (error) { showToast('Nie udało się pobrać posta', 'error'); }
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        currentEditPostId = null;
    }
    async function saveEdit() {
        if (!currentEditPostId) return;
        const content = document.getElementById('editContent').value;
        try {
            const response = await fetch('/index.php?action=update_post', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: currentEditPostId, content: content })
            });
            const result = await response.json();
            if (result.success) {
                const card = document.querySelector(`[data-post-id="${currentEditPostId}"]`);
                card.querySelector('.post-text').textContent = content;
                closeEditModal();
                showToast(result.message, 'success');
            } else { showToast(result.message, 'error'); }
        } catch (error) { showToast('Wystąpił błąd podczas zapisywania', 'error'); }
    }
    async function deletePost(postId) {
        if (!confirm('Czy na pewno chcesz usunąć ten post?')) return;
        try {
            const response = await fetch('/index.php?action=delete_post', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: postId })
            });
            const result = await response.json();
            if (result.success) {
                const card = document.querySelector(`[data-post-id="${postId}"]`);
                card.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => card.remove(), 300);
                showToast(result.message, 'success');
            } else { showToast(result.message, 'error'); }
        } catch (error) { showToast('Wystąpił błąd podczas usuwania', 'error'); }
    }
    function exportPost(postId) {
        window.location.href = `/index.php?action=export_post&id=${postId}`;
    }
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const query = e.target.value;
            if (query.length >= 2) {
                searchPosts(query);
            } else if (query.length === 0) {
                location.reload();
            }
        }, 500);
    });
    async function searchPosts(query) {
        try {
            const response = await fetch(`/index.php?action=search_posts&q=${encodeURIComponent(query)}`);
            const result = await response.json();
            if (result.success) {
                displaySearchResults(result.data);
            }
        } catch (error) { showToast('Błąd wyszukiwania', 'error'); }
    }
    function displaySearchResults(posts) {
        const grid = document.getElementById('postsGrid');
        if (posts.length === 0) {
            grid.innerHTML = '<div class="empty-state"><p>Nie znaleziono postów</p></div>';
            return;
        }
        location.reload();
    }
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>