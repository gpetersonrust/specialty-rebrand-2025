import '../scss/physicians.scss';
import ExpertFilterUI from './includes/expert-filter-ui';
import SpecialtyDropdown from './includes/specialty-dropdown-builder';
 
 
 
document.addEventListener('DOMContentLoaded', function () {
  const specialtyDropdown = new SpecialtyDropdown(
    'ul.x-menu-first-level',      // Adjust if your main nav UL has a different unique selector
    '.expert-filter-container'  // The class of the div containing your custom dropdown
  );
  
  const{ labels, elements: {trigger: triggerButton} }  = specialtyDropdown;
  const expertFilterUI = new ExpertFilterUI({
    labels,
    triggerButton,
    });
  });