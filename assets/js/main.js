// RewardZone - Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Toast notifications
    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification alert alert-${type}`;
        toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    };

    // Social proof notifications (random)
    const names = ['Ali', 'Sara', 'Ahmed', 'Fatima', 'Hassan', 'Ayesha'];
    const amounts = [50, 100, 150, 200, 250];
    
    setInterval(() => {
        const name = names[Math.floor(Math.random() * names.length)];
        const amount = amounts[Math.floor(Math.random() * amounts.length)];
        showToast(`${name} just earned PKR ${amount}!`, 'success');
    }, 10000);
});
