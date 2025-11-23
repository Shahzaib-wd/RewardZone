// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    }

    // Mission completion
    window.completeMission = function(missionId) {
        fetch('api/complete_mission.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mission_id: missionId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(`Mission completed! Earned PKR ${data.reward}`, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'warning');
            }
        });
    };

    // Spin wheel
    window.spinWheel = function() {
        const wheel = document.querySelector('.spin-wheel');
        const btn = document.getElementById('spinBtn');
        
        btn.disabled = true;
        wheel.classList.add('spinning');
        
        fetch('api/spin.php', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            setTimeout(() => {
                wheel.classList.remove('spinning');
                if (data.success) {
                    showToast(`You won PKR ${data.reward}!`, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(data.message, 'warning');
                    btn.disabled = false;
                }
            }, 3000);
        });
    };
});
