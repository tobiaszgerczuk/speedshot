export default class Header {
  constructor() {
    this.header = document.querySelector('header');
    this.menuToggles = document.querySelectorAll('[data-toggle="menu"]');
    this.menu = document.querySelector('[data-menu]');

    if (this.menu && this.menuToggles.length) {
      this.menuToggles.forEach((toggle) => {
        toggle.addEventListener('click', this.toggleMenu.bind(this));
      });
    }

    this.initMobileAccordion();
    window.addEventListener('scroll', this.handleScroll.bind(this));
  }

  toggleMenu() {
    const isOpen = this.menu.classList.toggle('is-open');
    this.menuToggles.forEach((toggle) => toggle.classList.toggle('is-open'));
    this.header?.classList.toggle('is-menu-open', isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';

    if (!isOpen) {
      this.closeAllSubMenus();
    }
  }

  initMobileAccordion() {
    if (!this.menu) return;

    this.menu.querySelectorAll('.menu-item-has-children > a').forEach((link) => {
      link.addEventListener('click', (e) => {
        if (!this.isMobile()) return;

        e.preventDefault();
        const item = link.closest('.menu-item-has-children');
        const subMenu = item.querySelector(':scope > .sub-menu');
        if (!subMenu) return;

        const isOpen = item.classList.contains('is-open');

        // Zamknij rodzeństwo na tym samym poziomie
        item.closest('ul')?.querySelectorAll(':scope > .menu-item-has-children.is-open').forEach((sibling) => {
          if (sibling !== item) {
            sibling.classList.remove('is-open');
            sibling.querySelector(':scope > .sub-menu')?.classList.remove('is-open');
          }
        });

        item.classList.toggle('is-open', !isOpen);
        subMenu.classList.toggle('is-open', !isOpen);
      });
    });
  }

  closeAllSubMenus() {
    this.menu?.querySelectorAll('.menu-item-has-children.is-open, .sub-menu.is-open').forEach((el) => {
      el.classList.remove('is-open');
    });
  }

  isMobile() {
    return window.innerWidth < 768;
  }

  handleScroll() {
    if (!this.header) return;
    this.header.classList.toggle('is-scrolled', window.scrollY > 50);
  }
}
