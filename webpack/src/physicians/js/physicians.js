import '../scss/physicians.scss';
class KOC_ExpertFilterUI {
  constructor(config = {}) {
    // Allow customization via constructor config (optional)
    this.buttonSelector = config.buttonSelector || '.subspecialty-filter-button';
    this.sectionSelector = config.sectionSelector || '.subspecialty-section';
    this.activeClass = config.activeClass || 'active';     // Class for active subspecialty button
    this.hiddenClass = config.hiddenClass || 'hidden';     // Class to hide elements

    // References to filter input elements
    this.specialtyDropdown = document.querySelector('#specialty-dropdown');
    this.locationDropdown = document.querySelector('#location-dropdown');
    this.searchBox = document.querySelector('.expert-filter-search-box');

    // State object to track current filter values
    this.filters = {
      location: this.locationDropdown?.value || '',
      name: this.searchBox?.value.trim().toLowerCase() || '',
      subspecialty: 'all' // Default: show all subspecialty sections
    };

    // Store DOM references
    this.buttons = document.querySelectorAll(this.buttonSelector);
    this.sections = document.querySelectorAll(this.sectionSelector);

    // Will store structured info about each subspecialty section and its expert cards
    this.subSpecialtyCards = [];

 
    
    // Initialize the system
    this.init();

       console.log(this.subSpecialtyCards, ' Subspecialty Cards');
  }

  /**
   * Entry point for the component setup:
   * - Load expert card data
   * - Attach event listeners
   * - Restore filters from the URL (deep linking support)
   */
  init() {
    this.initializeCards();
    this.attachSubspecialtyListeners();
    this.attachFilterListeners();
    this.restoreFiltersFromUrl();
    this.filterCards(); // Run filtering immediately after init
  }

  /**
   * Parse and store expert cards inside each subspecialty section.
   * This allows filtering by subspecialty later.
   */
  initializeCards() {
    const createCardObject = (card) => ({
      element: card,
      location: card.dataset.location?.toLowerCase() || '',
      name: card.querySelector('.expert-grid-title')?.textContent.trim().toLowerCase() || ''
    });

    // Each subspecialty section gets its own list of expert cards
    // Handle both subspecialty sections and single expert grid cases
    if (this.sections.length > 0) {
      // Multiple subspecialty sections case
      this.subSpecialtyCards = Array.from(this.sections).map(section => ({
        id: section.id,
        type: section.dataset.subspecialty,
        parentTier: section.dataset.parentTier || null, // Store parentTier info
        element: section,
        cards: Array.from(section.querySelectorAll('.expert-card')).map(createCardObject)
      }));
    } else {
      // Single expert grid case
      const expertGrid = document.querySelector('.expert-grid');
      if (expertGrid) {
        this.subSpecialtyCards = [{
          id: 'expert-grid',
          type: 'all',
          parentTier: null,
          element: expertGrid,
          cards: Array.from(expertGrid.querySelectorAll('.expert-card')).map(createCardObject)
        }];
      }
    }
  }

  /**
   * Adds event listeners to all subspecialty filter buttons.
   * Updates visible sections and filters when a button is clicked.
   */
  attachSubspecialtyListeners() {
    this.buttons.forEach(button => {
      button.addEventListener('click', () => {
        const selected = button.getAttribute('data-subspecialty');
        this.filters.subspecialty = selected;

        // Update active state on buttons
        this.buttons.forEach(btn => btn.classList.remove(this.activeClass));
        button.classList.add(this.activeClass);

        // Show relevant sections and filter cards again
        this.updateVisibleSections();
        this.filterCards();
      });
    });
  }

  /**
   * Attaches listeners to the location dropdown, name search box,
   * and the specialty dropdown for page redirection.
   */
  attachFilterListeners() {
    this.locationDropdown?.addEventListener('change', () => {
      this.filters.location = this.locationDropdown.value;
      this.updateURL();
      this.filterCards();
    });

    this.searchBox?.addEventListener('input', () => {
      this.filters.name = this.searchBox.value.trim().toLowerCase();
      this.updateURL();
      this.filterCards();
    });

    this.specialtyDropdown?.addEventListener('change', () => this.redirectWithFilters());
  }

  /**
   * Show/hide subspecialty sections based on the selected filter.
   * "Sports" filter can include both sports and non-surgical types.
   */
  updateVisibleSections() {
    const selected = this.filters.subspecialty;

    this.sections.forEach(section => {
      const parentTier = section?.dataset?.parentTier || null;
      const type = parentTier || section.dataset.subspecialty;
      const match = selected === 'all' || type === selected ||
        (selected === 'sports' && type.includes('non-surgical'));

      section.classList.toggle(this.hiddenClass, !match);
    });
  }

  /**
   * Helper method to determine the effective type for a section (parentTier or type)
   */
  getEffectiveType(section) {
    return section.parentTier || section.type;
  }

  /**
   * Filter expert cards based on current location, name, and active subspecialty section.
   */
  filterCards() {
    const locFilter = this.filters.location.toLowerCase().replace(/\s+/g, '-');

    console.log(locFilter, ' Location Filter');
    
    const locationMatch = locFilter === 'all-locations' ? '' : locFilter.replace('.', '');
    const nameFilter = this.filters.name;

    // Get target sections to apply filters to (either all or the visible one)
    const targetSections = this.filters.subspecialty === 'all'
      ? this.subSpecialtyCards
      : this.subSpecialtyCards.filter(sec => {
          const effectiveType = this.getEffectiveType(sec); // Use helper method
          return effectiveType === this.filters.subspecialty ||
                 (this.filters.subspecialty === 'sports' && effectiveType.includes('non-surgical'));
        });

    targetSections.forEach(section => {
      let visibleCount = 0;

      section.cards.forEach(card => {
        const matchLoc = !locationMatch || card.location.includes(locationMatch);
        const matchName = !nameFilter || card.name.includes(nameFilter);
        const show = matchLoc && matchName;

        card.element.classList.toggle(this.hiddenClass, !show);
        if (show) visibleCount++;
      });

      // Hide section entirely if none of its cards are visible
      section.element.classList.toggle(this.hiddenClass, visibleCount === 0);
    });
  }

  /**
   * Check if the current URL has filters, and apply them to UI.
   */
  restoreFiltersFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const loc = params.get('expert_location');
    const name = params.get('expert_name');

    if (loc && this.locationDropdown) {
      this.locationDropdown.value = loc;
      this.filters.location = loc;
    }

    if (name && this.searchBox) {
      this.searchBox.value = name;
      this.filters.name = name.toLowerCase();
    }
  }

  /**
   * Updates the current URL with the latest filter state without reloading the page.
   */
  updateURL() {
    const params = new URLSearchParams();

    if (this.filters.location && this.filters.location !== '.') {
      params.set('expert_location', this.filters.location);
    }

    if (this.filters.name) {
      params.set('expert_name', this.filters.name);
    }

    const url = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState({}, '', url);
  }

  /**
   * If a new specialty is selected (from the dropdown),
   * redirect the user to the appropriate page and carry over current filters.
   */
  redirectWithFilters() {
    if (!this.specialtyDropdown) return;
    // Show loading spinner before redirect
    this.createLoadingSpinner();
    const baseUrl = this.specialtyDropdown.value.replace(/\/+$/, '');
    const currentBase = window.location.href.split('?')[0].replace(/\/+$/, '');
    const params = new URLSearchParams();

    if (this.filters.location && this.filters.location !== '.') {
      params.set('expert_location', this.filters.location);
    }

    if (this.filters.name) {
      params.set('expert_name', this.filters.name);
    }

    const fullUrl = `${baseUrl}?${params.toString()}`;
    if (baseUrl !== currentBase) {
      setTimeout(() => {
        window.location.href = fullUrl;
      }, 1000);
    }
  }

  /**
   * Creates and manages a loading spinner with animated text
   */
  createLoadingSpinner() {
    console.log('Creating loading spinner...');
    const spinner = document.createElement('div');
    spinner.innerHTML = `
      <style>
        .koc-spinner-overlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(255, 255, 255, 0.9);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          z-index: 9999;
        }
        .koc-spinner {
          width: 50px;
          height: 50px;
          border: 4px solid #236194;
          border-top: 4px solid #56a554;
          border-radius: 50%;
          animation: spin 1s linear infinite;
        }
        .koc-spinner-text {
          margin-top: 20px;
          color: #717171;
          font-size: 18px;
        }
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        @keyframes dots {
          0% { content: '.'; }
          33% { content: '..'; }
          66% { content: '...'; }
        }
        .koc-spinner-dots::after {
          content: '.';
          animation: dots 1.5s steps(1) infinite;
        }
      </style>
      <div class="koc-spinner-overlay">
        <div class="koc-spinner"></div>
        <div class="koc-spinner-text">
          Physicians Loading<span class="koc-spinner-dots"></span>
        </div>
      </div>
    `;
    const container = document.querySelector('#expert-grid-container');
    if (container) {
      container.appendChild(spinner);
    }
    return spinner;
  }
}

// When DOM is fully loaded, start the filter UI system
document.addEventListener('DOMContentLoaded', () => {
  new KOC_ExpertFilterUI();
});