import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Initialize Alpine.js
Alpine.start();

// Global helper functions
window.confirmDelete = function(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
};

window.showNotification = function(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    } text-white`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
};

// Auto-hide flash messages
document.addEventListener('DOMContentLoaded', () => {
    const flashMessages = document.querySelectorAll('[data-auto-hide]');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.display = 'none';
        }, 5000);
    });
});
