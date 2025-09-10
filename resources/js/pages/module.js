function initModulePage() {
    // scroll to bonus activity
    const accordionContainer = document.getElementById('accordionDays');
    const activityId = accordionContainer.dataset.accordionActivity;
    if (activityId) {
        setTimeout(function() {
            var scrollActivity = document.getElementById('moduleLink_' + activityId);
            if (scrollActivity) {
                var offset = 125;
                var elementPosition = scrollActivity.getBoundingClientRect().top;
                var offsetPosition = elementPosition + window.pageYOffset - offset;
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }, 100);
    }

    document.querySelectorAll('.activity-link').forEach(function (activity) {
        activity.addEventListener('click', function (event) {
            event.preventDefault();
            var activityId = this.getAttribute('data-id');
            return new Promise((resolve, reject) => {
                axios.get(`/checkActivity/${activityId}`)
                    .then(response => {
                        if (response.data.locked) {
                            showModal(response.data.modalContent);
                        } else {
                            window.location.href = `/explore/activity/${activityId}`;
                        }
                        resolve(true);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        reject(false);
                    });
            });
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModulePage);
} else {
    initModulePage();
}