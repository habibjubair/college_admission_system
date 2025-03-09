// assets/js/dashboard.js

// Dashboard-specific functions
document.addEventListener('DOMContentLoaded', function () {
    // Add event listeners for dashboard actions
    const buttons = document.querySelectorAll('.action-button');
    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const action = this.dataset.action;
            if (action === 'approve' || action === 'reject') {
                showToast(`Student admission ${action}d.`, 'success');
            }
        });
    });
});