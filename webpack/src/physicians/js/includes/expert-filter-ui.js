class ExpertFilterUI {
  constructor(args) {
    this.filters = {
      specialty: '',
      location: '',
      title: ''
    };


    this.dropdownLocation = document.querySelector('#location-dropdown');
    this.searchInput = document.querySelector('.expert-filter-search-box');
    this.container = document.querySelector('#expert-grid-container');
    this.loader = document.querySelector('#expert-loader');


    this.labels = args.labels || {};
    this.triggerButton = args.triggerButton || null;


    

    if (!this.dropdownLocation || !this.searchInput || !this.container || !this.loader) {
      console.warn('ExpertFilterUI: Required elements not found.');
      return;
    }

    this.init();
  }

  init() {
    this.parseURLParams();
    this.syncUI();
    this.bindEvents();
    this.fetchAndRenderExperts();
  }

  parseURLParams() {
    const urlParams = new URLSearchParams(window.location.search);
    this.filters.specialty = urlParams.get('specialty') || 'all';
    let pathname = window.location.pathname;
    console.log({pathname}, 'pathname from expert filter UI');
    

   
    
    if (!this.filters.location) {
      this.dropdownLocation.selectedIndex = 0;
      this.filters.location = this.dropdownLocation.value;
    } else {
      this.dropdownLocation.value = this.filters.location;
    }
    this.filters.title = (urlParams.get('title') || '').toLowerCase();
  }

  syncUI() {
    this.dropdownLocation.value = this.filters.location;
    this.searchInput.value = this.filters.title;

    if (this.triggerButton && this.filters.specialty !== 'all') {
      const specialtyLabel = this.labels[this.filters.specialty] || this.filters.specialty;
      this.triggerButton.textContent = specialtyLabel;
    } else if (this.triggerButton) {
      this.triggerButton.textContent = this.labels.all || 'All Specialties';
    }
  }

  bindEvents() {

    console.log(this.container, ' container from expert filter UI');
    document.addEventListener('specialtyFilterChanged', (e) => {
      const { filter } = e.detail;
    
      console.log('Specialty filter changed event received:', e.detail);

      this.filters.specialty = filter.replace(/^\./, '');

      this.updateURLParams();
      this.fetchAndRenderExperts();
    });

    this.dropdownLocation.addEventListener('change', (e) => {
      this.filters.location = e.target.value;
      this.updateURLParams();
      this.fetchAndRenderExperts();
    });

    this.searchInput.addEventListener('input', this.debounce((e) => {
      this.filters.title = e.target.value.toLowerCase();
      this.updateURLParams();
      this.fetchAndRenderExperts();
    }, 300));
  }

  updateURLParams() {
    const params = new URLSearchParams();
    if (this.filters.specialty && this.filters.specialty !== 'all') {
      params.set('specialty', this.filters.specialty);
      console.log('Not all specialty, setting URL param:', this.filters.specialty);
    }
    if (this.filters.location) {
      params.set('location', this.filters.location);
    }
    if (this.filters.title) {
      params.set('title', this.filters.title);
    }
    const newURL = `${window.location.pathname}?${params.toString()}`;
    history.replaceState(null, '', newURL);
  }

  async fetchAndRenderExperts() {
    this.container.innerHTML = '';
    this.loader.style.display = 'block';
      console.log({ query: ""})
    try {
      const query = new URLSearchParams();
      if (this.filters.specialty && this.filters.specialty !== 'all') {
        query.set('specialty', this.filters.specialty);
      }
      if (this.filters.location && this.filters.location.toLowerCase() !== 'all locations') {
        query.set('location', this.filters.location);
      }
      if (this.filters.title) {
        query.set('name', this.filters.title);
      }

      const url = `/wp-json/specialty-rebrand/v1/physicians?${query.toString()}`;
      const res = await fetch(url);
      const data = await res.json();
      const { physicians = [], term_children = [], page_subtitle } = data;
      const specialty = this.filters.specialty;

      if (physicians.length) {
        if (['spine-neck-back', 'sports-medicine', 'elbow-sports', 'foot-ankle-sports', 'hip-sports', 'hand-wrist-sports', 'knee-sports', 'shoulder-sports'].includes(specialty)) {
          this.addSectionHeading('Surgeons');
        }
        const grid = this.createExpertGrid(physicians);
        this.container.appendChild(grid);
      }

      term_children.forEach(group => {
        if (group.posts.length) {
          let termName = group.term?.name || '';
          const containsSurgical = termName.toLowerCase().includes('surgical');
          let headingText = !containsSurgical
            ? termName.replace(/&amp;/g, '&')
            : "Non-Surgical Physicians";
          headingText = headingText.replace(/Procedures/g, '').trim();
          this.addSectionHeading(headingText);
          const grid = this.createExpertGrid(group.posts);
          this.container.appendChild(grid);
        }
      });

      if (page_subtitle) {
        this.addSectionHeading(page_subtitle, 'h2', 'physician-subtitle');
      }

    } catch (err) {
      this.container.innerHTML = `<p>Error loading physician data. Please try again.</p>`;
      console.error('Fetch failed:', err);
    }

    this.loader.style.display = 'none';
  }

  createExpertGrid(physicians) {
    const grid = document.createElement('div');
    grid.className = 'expert-grid';

    physicians.forEach(doc => {
      const card = document.createElement('div');
      card.className = 'expert-card';
      card.dataset.location = this.slugify(doc.locations);
      card.dataset.specialties = doc.specialties.map(this.slugify).join(' ');
      card.innerHTML = `
        <a href="${doc.permalink}">
          <img src="${doc.featured_image}" alt="${doc.name}">
          <div class="expert-grid-title">
            ${doc.name}<br>${doc.job_title}
          </div>
        </a>
      `;
      grid.appendChild(card);
    });

    return grid;
  }

  slugify(str) {
    return (str || '')
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  debounce(fn, delay) {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  addSectionHeading(text, heading = "h3", clasName = "expert-section-heading") {
    const headingEl = document.createElement(heading);
    headingEl.className = clasName;
    headingEl.textContent = text;
    this.container.appendChild(headingEl);
  }
}

export default ExpertFilterUI;
