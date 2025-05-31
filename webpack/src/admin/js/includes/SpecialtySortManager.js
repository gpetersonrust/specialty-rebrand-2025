import Sortable from 'sortablejs';

class SpecialtySortManager {
    constructor() {
        this.init();

        console.log('SpecialtySortManager initialized');
        
    }

    init() {
        this.initFilters();
        this.initSortable();
        this.initTabToggles();
    }

    initFilters() {
        document.querySelectorAll('.sr-filter-input').forEach(input => {
            input.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                const container = this.closest('.sr-selector-container');
                const items = container.querySelectorAll('.sr-item');

                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });
    }

 initSortable() {
  console.log('sorting initialized');

  document.querySelectorAll('.sr-items.sortable').forEach(container => {
    new Sortable(container, {
      group: 'sr-items',
      animation: 150,
      onAdd: evt => {
        setTimeout(() => this.updateHiddenInputs(evt), 0);
      },
      onUpdate: evt => {
        setTimeout(() => this.updateHiddenInputs(evt), 0);
      }
    });
  });
}

updateHiddenInputs(evt) {
    const container = evt.to;
    const wrapper = container.closest('.sr-selector-container');

    const metaKey = wrapper.dataset.metaKey;
    const fieldPrefix = wrapper.dataset.fieldPrefix; // ✅ use the newly added attribute

    // Remove any old hidden inputs
    const inputs = container.querySelectorAll('input[type="hidden"]');
    inputs.forEach(input => input.remove());

    // Rebuild inputs based on new DOM order
    container.querySelectorAll('.sr-item').forEach(item => {
        const id = item.dataset.id;
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `${fieldPrefix}[${metaKey}][]`; // ✅ correct naming structure
        hiddenInput.value = id;
        item.appendChild(hiddenInput);
    });
}


    initTabToggles() {
        document.querySelectorAll('.specialty-tab-nav .tab-link').forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                const target = this.getAttribute('href');

                document.querySelectorAll('.specialty-tab-nav .tab-link').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.specialty-tab-content').forEach(c => c.classList.remove('active'));

                this.classList.add('active');
                document.querySelector(target).classList.add('active');
            });
        });
    }
}

export default SpecialtySortManager;