function initModulePage() {
    // scroll to bonus activity
    const accordionContainer = document.getElementById('accordionDays');
    const dayId = accordionContainer.dataset.accordionDay;
    if (dayId) {
        console.log('override found');
        var dayElement = document.getElementById('day_' + dayId);
        if (dayElement) {
            console.log('day element found');
            setTimeout(function() {
                var bonusActivity = dayElement.querySelector('.activity-tag-optional');
                if (bonusActivity) {
                    console.log('bonus activity found');
                    var offset = 125;
                    var elementPosition = bonusActivity.getBoundingClientRect().top;
                    var offsetPosition = elementPosition + window.pageYOffset - offset;
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }, 100);
        }
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