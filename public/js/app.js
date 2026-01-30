function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    const themeIcon = document.querySelector('.theme-icon');
    if (themeIcon) {
        themeIcon.textContent = newTheme === 'dark' ? '🌙' : '☀️';
    }
    fetch('/index.php?action=toggle_theme', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    }).catch(err => console.error('Failed to save theme:', err));
    showToast(`Motyw zmieniony na ${newTheme === 'dark' ? 'ciemny' : 'jasny'}! 🎨`, 'success');
}
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
        const themeIcon = document.querySelector('.theme-icon');
        if (themeIcon) {
            themeIcon.textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        }
    }
});
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    }[type] || 'ℹ️';
    toast.innerHTML = `
        <span class="toast-icon">${icon}</span>
        <span class="toast-message">${message}</span>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            container.removeChild(toast);
        }, 300);
    }, duration);
}
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
function validatePassword(password) {
    return password.length >= 6;
}
function validateUsername(username) {
    const re = /^[a-zA-Z0-9]+$/;
    return re.test(username) && username.length >= 3;
}
const loginForm = document.querySelector('form[action*="login"]');
if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        if (!username) {
            e.preventDefault();
            showToast('Nazwa użytkownika jest wymagana', 'error');
            return false;
        }
        if (!password) {
            e.preventDefault();
            showToast('Hasło jest wymagane', 'error');
            return false;
        }
    });
}
const registerForm = document.querySelector('form[action*="register"]');
if (registerForm) {
    registerForm.addEventListener('submit', (e) => {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        if (!validateUsername(username)) {
            e.preventDefault();
            showToast('Nazwa użytkownika musi mieć minimum 3 znaki i zawierać tylko litery i cyfry', 'error');
            return false;
        }
        if (!validateEmail(email)) {
            e.preventDefault();
            showToast('Nieprawidłowy format adresu email', 'error');
            return false;
        }
        if (!validatePassword(password)) {
            e.preventDefault();
            showToast('Hasło musi mieć minimum 6 znaków', 'error');
            return false;
        }
        if (password !== passwordConfirm) {
            e.preventDefault();
            showToast('Hasła muszą być identyczne', 'error');
            return false;
        }
    });
}
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pl-PL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
function truncateText(text, maxLength = 100) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Skopiowano do schowka! 📋', 'success');
        return true;
    } catch (err) {
        console.error('Failed to copy:', err);
        showToast('Nie udało się skopiować', 'error');
        return false;
    }
}
function setButtonLoading(button, isLoading, originalText = null) {
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.innerHTML = '<span>Ładowanie...</span>';
    } else {
        button.disabled = false;
        button.textContent = originalText || button.dataset.originalText || 'Submit';
    }
}
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
        }
    }
    if (e.key === 'Escape') {
        const modal = document.querySelector('.modal[style*="display: flex"]');
        if (modal && typeof closeEditModal === 'function') {
            closeEditModal();
        }
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('.form-textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
});
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');
    }
}
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card, .post-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
});
async function handleFetchError(response) {
    if (!response.ok) {
        const error = await response.json().catch(() => ({
            message: 'Wystąpił nieznany błąd'
        }));
        throw new Error(error.message || `HTTP error! status: ${response.status}`);
    }
    return response.json();
}
const storage = {
    set: (key, value) => {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return false;
        }
    },
    get: (key, defaultValue = null) => {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return defaultValue;
        }
    },
    remove: (key) => {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return false;
        }
    },
    clear: () => {
        try {
            localStorage.clear();
            return true;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return false;
        }
    }
};
window.SocialAI = {
    showToast,
    copyToClipboard,
    toggleTheme,
    storage,
    validateEmail,
    validatePassword,
    validateUsername,
    formatDate,
    truncateText,
    debounce,
    setButtonLoading,
    handleFetchError
};
