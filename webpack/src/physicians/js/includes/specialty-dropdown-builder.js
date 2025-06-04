class SpecialtyDropdown {
  constructor(sourceMenuSelector, customDropdownContainerSelector) {
    this.sourceMenu = document.querySelector(sourceMenuSelector);
    this.container = document.querySelector(customDropdownContainerSelector);
       this.labels = {}

    if (!this.sourceMenu) {
      console.error(`SpecialtyDropdown: Source menu "${sourceMenuSelector}" not found.`);
      return;
    }
    if (!this.container) {
      console.error(`SpecialtyDropdown: Custom dropdown container "${customDropdownContainerSelector}" not found.`);
      return;
    }

    this.elements = {
      wrapper: this.container.querySelector('#specialty-filter-wrapper'),
      action_wrapper: this.container.querySelector('.action-wrapper'),
      trigger: this.container.querySelector('#specialty-trigger'),
      arrow: this.container.querySelector('#specialty-arrow-down'),
      menu: this.container.querySelector('#specialty-menu'),
      input: this.container.querySelector('.quick-filter-input'), // Ensure this selector is correct for your HTML
      optionsList: this.container.querySelector('#specialty-options'),
      allOptions: null, // Will include main and sub-options after population
      mainOptions: null, // Will include only .specialty-option elements
    };

    let pathname = window.location.pathname;
    const hasExperts = pathname.includes('experts');
    if (hasExperts) {
      this.initialSpecialty = pathname.split('/').reverse()[1]; // Get the last segment of the URL
      this.initialSpecialty = this.initialSpecialty === 'physicians' ? null : this.initialSpecialty;
      let convertedHumanReadbleText = this.initialSpecialty.replace(/-/g, ' ').replace(/\b\w/g, char => char.toUpperCase());

       console.log(this.initialSpecialty, 'initial specialty from pathname');
       
      
    }
  
    
 

    if (!this.elements.trigger || !this.elements.menu || !this.elements.optionsList) {
      console.error("SpecialtyDropdown: Essential dropdown elements not found (trigger, menu, or optionsList).");
      return;
    }
    if (!this.elements.wrapper) {
        console.warn("SpecialtyDropdown: Wrapper element '#specialty-filter-wrapper' not found. Some features might not work as expected.");
    }


    this.selectedFilter = '.'; // Default filter, often for "All"
    this.init();
  }

  init() {
    if (!this.elements.wrapper) { // Safety check if wrapper is critical for mouseleave
        console.warn("SpecialtyDropdown init: Wrapper not found, skipping mouseleave listener for container.");
    } else {
        this.elements.menu.addEventListener('mouseleave', () => { // Changed from this.container to this.elements.wrapper
            this.toggleDropdown(false);
        });
    }

    // Preserve and re-add "All Specialties" if it exists in the initial HTML
    const allSpecialtiesLi = this.elements.optionsList.querySelector('.all-specialties');
    this.elements.optionsList.innerHTML = ''; // Clear existing options before populating
    if (allSpecialtiesLi) {
      this.elements.optionsList.appendChild(allSpecialtiesLi);
    }

    this.populateCustomDropdown();

    // Query for options *after* populating
    this.elements.allOptions = this.elements.optionsList.querySelectorAll('li[data-filter]');
    this.elements.mainOptions = this.elements.optionsList.querySelectorAll('li.specialty-option');

    this.addEventListeners();
    this.elements.menu.classList.add('hidden'); // Start with the menu hidden
  }

  getSpecialtyFromHref(href) {
    if (!href) return null;
    try {
      const url = new URL(href, window.location.origin);
      return url.searchParams.get("specialty") || null;
    } catch (e) {
      console.error(`Error parsing URL: ${href}`, e);
      return null;
    }
  }

  populateCustomDropdown() {
    let specialtiesTopLevelItem = null;
    // Find the 'Specialties' top-level menu item in the source menu
    this.sourceMenu.querySelectorAll(':scope > li.menu-item').forEach(item => {
      const anchorText = item.querySelector(':scope > a .x-anchor-text-primary');
      if (anchorText && anchorText.textContent.trim().toLowerCase() === 'specialties') {
        specialtiesTopLevelItem = item;
      }
    });

    if (!specialtiesTopLevelItem) {
      // console.warn("SpecialtyDropdown: 'Specialties' top-level item not found in source menu.");
      return;
    }

    const specialtiesSubMenu = specialtiesTopLevelItem.querySelector(':scope > .sub-menu');
    if (!specialtiesSubMenu) {
      // console.warn("SpecialtyDropdown: Sub-menu for 'Specialties' not found.");
      return;
    }

    // Iterate over parent specialty items
    specialtiesSubMenu.querySelectorAll(':scope > li.menu-item').forEach(parentItem => {
      const parentAnchor = parentItem.querySelector(':scope > a');
      const parentLabel = parentAnchor?.querySelector('.x-anchor-text-primary')?.textContent.trim();
      const parentHref = parentAnchor?.getAttribute('href');
      let parentSlug = this.getSpecialtyFromHref(parentHref);

      // Fallback to generating slug from label if not in href
      if (!parentSlug && parentLabel) {
        parentSlug = parentLabel.toLowerCase().replace(/&/g, 'and').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
      }

      if (!parentLabel || !parentSlug) return; // Skip if essential data is missing

      const parentLi = document.createElement('li');
      parentLi.className = `specialty-option ${parentSlug}`;
      parentLi.setAttribute('data-filter', `.${parentSlug}`);
    

    const labelWrapper = document.createElement('div');
    labelWrapper.className = 'option-label';

    const linkElem = document.createElement('a');
    linkElem.href = parentHref;
    linkElem.textContent = parentLabel;
    linkElem.className = 'option-text';

    const arrowSpan = document.createElement('span');
    arrowSpan.className = 'sub-arrow';
    arrowSpan.innerHTML = `
      <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 52 52">
        <path d="M8.3,14h35.4c1,0,1.7,1.3,0.9,2.2L27.3,37.4c-0.6,0.8-1.9,0.8-2.5,0L7.3,16.2C6.6,15.3,7.2,14,8.3,14z"/>
      </svg>`;

    labelWrapper.appendChild(linkElem);
    // labelWrapper.appendChild(arrowSpan);
    parentLi.appendChild(labelWrapper);

    this.labels[parentSlug] = parentLabel; // Store the label for later use

      // const childMenu = parentItem.querySelector(':scope > .sub-menu');
      // if (childMenu) {
      //   const subListUl = document.createElement('ul');
      //   subListUl.className = 'subspecialty-list';

      //   childMenu.querySelectorAll(':scope > li.menu-item').forEach(childItem => {
      //     const childAnchor = childItem.querySelector(':scope > a');
      //     const childLabel = childAnchor?.querySelector('.x-anchor-text-primary')?.textContent.trim();
      //     const childHref = childAnchor?.getAttribute('href');
      //     const childSlug = this.getSpecialtyFromHref(childHref);

      //     this.labels[childSlug] = childLabel; // Store the label for later use

      //     if (childLabel && childSlug) {
      //       const childLi = document.createElement('li');
      //       childLi.className = `subspecialty-option ${childSlug}`;
      //       childLi.setAttribute('data-filter', `.${childSlug}`);
      //       childLi.textContent = childLabel;
      //       subListUl.appendChild(childLi);
      //     }
      //   });

      //   if (subListUl.childNodes.length > 0) {
      //     parentLi.appendChild(subListUl);
      //   } else {
      //       // If no sub-items, remove the arrow as it's not needed
      //       arrowSpan.remove();
      //   }
      // } else {
      //   // If no childMenu, also remove the arrow
      //   arrowSpan.remove();
      // }


      this.elements.optionsList.appendChild(parentLi);
    });
  }

  selectOption(optionElement) {
    if (!optionElement || !this.elements.trigger || !this.elements.allOptions) return;

    let label;
    const optionTextSpan = optionElement.querySelector('.option-text');
    if (optionTextSpan) { // Main specialty option
        label = optionTextSpan.textContent.trim();
    } else { // Subspecialty option or "All Specialties" (which might not have .option-text)
        label = optionElement.textContent.trim();
    }
    
    const filterValue = optionElement.getAttribute('data-filter');

    if (!label || !filterValue) {
        console.warn("SpecialtyDropdown: Could not determine label or filter for", optionElement);
        return;
    }

    this.elements.trigger.textContent = label;

    this.elements.allOptions.forEach(opt => opt.classList.remove('active'));
    optionElement.classList.add('active');

    this.selectedFilter = filterValue;
    this.toggleDropdown(false); // Close dropdown after selection

    // Reset accordion states for main options when any selection is made
    if (this.elements.mainOptions) {
        this.elements.mainOptions.forEach(opt => {
            opt.classList.remove('open');
            const arrow = opt.querySelector('.sub-arrow'); // The span containing the SVG
            if (arrow) arrow.classList.remove('rotated');
        });
    }

    console.log('SpecialtyDropdown: Dispatching specialtyFilterChanged', { filter: filterValue, label });
    this.container.dispatchEvent(new CustomEvent('specialtyFilterChanged', {
      bubbles: true,
      detail: {
        filter: filterValue,
        label: label
      }
    }));
  }

  addEventListeners() {
    this.elements.action_wrapper.addEventListener('click', (e) => {
      e.stopPropagation(); // Prevent document click listener from immediately closing
      const isHidden = this.elements.menu.classList.contains('hidden');
      this.toggleDropdown();
      
      // Rotate arrow when menu is shown
      if (this.elements.arrow) {
        this.elements.arrow.classList.toggle('rotated', !isHidden);
      }
    });

    // 

    // Close dropdown if clicked outside
    document.addEventListener('click', (e) => {
      if (!this.elements.menu.classList.contains('hidden') && 
          this.elements.wrapper && !this.elements.wrapper.contains(e.target)) {
        this.toggleDropdown(false);
      }
    });

    this.elements.optionsList.addEventListener('click', (e) => {
      const clickedElement = e.target;
      const targetLi = clickedElement.closest('li[data-filter]');

      if (!targetLi) return; // Click was not on an option item

      // Case 1: Clicked on a subspecialty option
      if (targetLi.classList.contains('subspecialty-option')) {
        this.selectOption(targetLi);
        return; // Action complete
      }

      // Case 2: Clicked on a main specialty option (class="specialty-option")
      if (targetLi.classList.contains('specialty-option')) {
        const mainSpecialtyLi = targetLi;
        const subList = mainSpecialtyLi.querySelector('ul.subspecialty-list');
        
        // Determine if the click was on the interactive part of the label or arrow
        const clickedArrowSpan = clickedElement.closest('.sub-arrow');
        const clickedWithinLabelArea = clickedElement.closest('.option-label');

        if (subList && subList.childNodes.length > 0) {
          // This main specialty HAS children. It's for toggling only.
          // Only toggle if the click was on its arrow or label area.
          if (clickedArrowSpan || clickedWithinLabelArea) {
            const isOpen = mainSpecialtyLi.classList.contains('open');

            // Close other open main specialty accordions
            this.elements.mainOptions.forEach(opt => {
              if (opt !== mainSpecialtyLi) {
                opt.classList.remove('open');
                const arrow = opt.querySelector('.sub-arrow');
                if (arrow) arrow.classList.remove('rotated');
              }
            });

            // Toggle the clicked main specialty's accordion
            mainSpecialtyLi.classList.toggle('open', !isOpen);
            const arrow = mainSpecialtyLi.querySelector('.sub-arrow');
            if (arrow) arrow.classList.toggle('rotated', !isOpen);
          }
          // Regardless of where on the parent (with children) was clicked,
          // we do NOT select it for filtering if it has children. So we return here.
          return;
        } else {
          // This main specialty has NO children (or "All Specialties").
          // It is directly selectable for filtering.
          this.selectOption(mainSpecialtyLi);
          return; // Action complete
        }
      }
    });

    // Add input event listener for filtering if the input element exists
    if (this.elements.input) {
      this.elements.input.addEventListener('input', (e) => {
        this.filterOptions(e.target.value);
      });
    }
  }

  toggleDropdown(forceShow = null) {
    const isHidden = this.elements.menu.classList.contains('hidden');
    const shouldShow = forceShow !== null ? forceShow : isHidden;

    this.elements.menu.classList.toggle('hidden', !shouldShow);
    if (this.elements.arrow) { // Main dropdown arrow on trigger
        this.elements.arrow.classList.toggle('active', shouldShow);
    }

    if (!shouldShow && this.elements.input) {
      this.elements.input.value = ''; // Clear search text when hiding
      this.filterOptions(''); // Reset filter display
    }
  }

  filterOptions(searchText) {
    if (!this.elements.mainOptions || !this.elements.input) return;

    const filterText = searchText.toLowerCase().trim();

    this.elements.mainOptions.forEach(mainOption => {
      const labelElement = mainOption.querySelector('.option-text');
      const label = labelElement ? labelElement.textContent.toLowerCase().trim() : '';
      const subList = mainOption.querySelector('.subspecialty-list');

      // Check if the main option's label matches
      const mainLabelMatches = label.includes(filterText);
      let subOptionMatches = false;

      // If there's a sublist, check if any sub-option matches
      if (subList) {
        subList.querySelectorAll('li.subspecialty-option').forEach(subOption => {
          const subLabel = subOption.textContent.toLowerCase().trim();
          if (subLabel.includes(filterText)) {
            subOptionMatches = true;
            subOption.style.display = ''; // Show matching sub-option
          } else {
            subOption.style.display = 'none'; // Hide non-matching sub-option
          }
        });
      }
      
      // Determine if the main option should be shown
      // Show if main label matches, OR if any sub-option matches, OR if filter is empty
      const shouldShowMainOption = mainLabelMatches || subOptionMatches || filterText === '';
      mainOption.classList.toggle('filtered-out', !shouldShowMainOption);

      // If showing the main option due to a sub-option match, ensure the sublist is visible
      if (subList) {
        if (shouldShowMainOption) {
          // If the main option itself matches, or any sub-option matches, ensure sublist is block
          // If the main option itself does not match, but a sub-option does, open it.
          if (subOptionMatches && !mainLabelMatches && filterText !== '') {
             mainOption.classList.add('open'); // Auto-open if child matches
             const arrow = mainOption.querySelector('.sub-arrow');
             if (arrow) arrow.classList.add('rotated');
          }
          // Sublist display is managed by 'open' class or direct style if needed
        } else {
          mainOption.classList.remove('open'); // Close if no matches in main or sub
          const arrow = mainOption.querySelector('.sub-arrow');
          if (arrow) arrow.classList.remove('rotated');
        }
      }
    });
  }
}

export default SpecialtyDropdown;