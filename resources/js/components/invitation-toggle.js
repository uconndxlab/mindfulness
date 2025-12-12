function initInvitationToggle() {
    const toggleButton = document.getElementById('invitation_toggle_btn');
    if (!toggleButton) return;

    const toggleIcon = document.getElementById('invitation_icon');
    const toggleText = document.getElementById('invitation_text');
    const route = toggleButton.dataset.route;
    if (!route) return;

    toggleButton.addEventListener('click', async function() {
        if (this.disabled) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            this.disabled = true;
            
            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                const isEnabled = data.status;
                this.classList.toggle('btn-primary', isEnabled);
                this.classList.toggle('btn-warning', !isEnabled);
                toggleIcon.classList.toggle('bi-envelope-slash', isEnabled);
                toggleIcon.classList.toggle('bi-envelope-check', !isEnabled);
                toggleText.textContent = isEnabled ? 'Disable Invitation-Only Mode' : 'Enable Invitation-Only Mode';
            } else {
                throw new Error(data.error || 'Failed to toggle invitation mode');
            }
        } catch (error) {
            console.error('Error:', error);
        } finally {
            this.disabled = false;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInvitationToggle);
} else {
    initInvitationToggle();
}

