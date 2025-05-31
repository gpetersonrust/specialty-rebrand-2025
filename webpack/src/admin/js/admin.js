import '../scss/admin.scss';
import SpecialtySortManager from './includes/SpecialtySortManager';
 

document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    
    tabLinks.forEach(function(tabLink) {
        tabLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and content
            tabLinks.forEach(link => link.classList.remove('active'));
            document.querySelectorAll('.specialty-tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding content
            const target = this.getAttribute('href');
            document.querySelector(target).classList.add('active');
        });
    });
});




// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    new SpecialtySortManager();
});