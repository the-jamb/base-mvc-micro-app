<?php
$pageTitle = 'Ulubione Posty';
$currentPage = 'favorites';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>
<main class="main-content">
    <div class="content-header">
        <div>
            <h1 class="page-title">Ulubione Posty ⭐</h1>
            <p class="page-subtitle">Twoje najlepsze i ulubione treści w jednym miejscu</p>
        </div>
    </div>
    <?php if (empty($favorites)): ?>
        <div class="empty-state">
            <div class="empty-icon">⭐</div>
            <h3 class="empty-title">Brak ulubionych postów</h3>
            <p class="empty-text">Dodaj posty do ulubionych, aby łatwo do nich wracać!</p>
            <a href="/index.php?page=history" class="btn btn-primary">
                <span>Przeglądaj historię</span>
                <span class="btn-icon">→</span>
            </a>
        </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($favorites as $post): ?>
                <div class="post-card favorite-card" data-post-id="<?php echo $post['id']; ?>">
                    <div class="post-card-header">
                        <span class="post-category"><?php echo strtoupper($post['category']); ?></span>
                        <button onclick="removeFavorite(<?php echo $post['id']; ?>, this)" class="btn-icon favorite-btn active">
                            <span>⭐</span>
                        </button>
                    </div>
                    <div class="post-card-content">
                        <div class="post-prompt"><?php echo htmlspecialchars($post['prompt']); ?></div>
                        <div class="post-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                    </div>
                    <div class="post-card-footer">
                        <div class="post-date">Dodano: <?php echo date('d.m.Y H:i', strtotime($post['favorited_at'])); ?></div>
                        <div class="post-actions">
                            <button onclick="copyPost(<?php echo $post['id']; ?>)" class="btn-icon"
                                title="Kopiuj"><span>📋</span></button>
                            <button onclick="exportPost(<?php echo $post['id']; ?>)" class="btn-icon"
                                title="Eksportuj"><span>💾</span></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=favorites&p=<?php echo $page - 1; ?>" class="pagination-btn">← Poprzednia</a>
                <?php endif; ?>
                <div class="pagination-info">Strona <?php echo $page; ?> z <?php echo $totalPages; ?></div>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=favorites&p=<?php echo $page + 1; ?>" class="pagination-btn">Następna →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<script>
    async function removeFavorite(postId, btn) {
        try {
            const response = await fetch('/index.php?action=favorite_remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId })
            });
            const result = await response.json();
            if (result.success) {
                const card = document.querySelector(`[data-post-id="${postId}"]`);
                card.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => card.remove(), 300);
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
    function exportPost(postId) {
        window.location.href = `/index.php?action=export_post&id=${postId}`;
    }
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>