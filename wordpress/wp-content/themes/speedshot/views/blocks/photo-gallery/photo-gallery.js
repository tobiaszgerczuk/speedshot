class PhotoGallery {
  constructor(el) {
    this.el        = el;
    this.lightbox  = el.querySelector('[data-gallery-lightbox]');
    this.img       = el.querySelector('[data-gallery-img]');
    this.counter   = el.querySelector('[data-gallery-counter]');
    this.items     = Array.from(el.querySelectorAll('[data-gallery-open]'));
    this.current   = 0;

    try {
      const raw    = el.querySelector('[data-gallery-data]');
      this.images  = raw ? JSON.parse(raw.textContent) : [];
    } catch (e) {
      this.images  = [];
    }

    if (!this.images.length) return;

    this.bindEvents();
  }

  bindEvents() {
    this.items.forEach(btn => {
      btn.addEventListener('click', () => this.open(parseInt(btn.dataset.galleryOpen, 10)));
    });

    this.el.querySelector('[data-gallery-prev]')?.addEventListener('click', () => this.prev());
    this.el.querySelector('[data-gallery-next]')?.addEventListener('click', () => this.next());
    this.el.querySelectorAll('[data-gallery-close]').forEach(el => {
      el.addEventListener('click', () => this.close());
    });

    document.addEventListener('keydown', this._onKey = (e) => {
      if (!this.lightbox.classList.contains('is-open')) return;
      if (e.key === 'ArrowLeft')  this.prev();
      if (e.key === 'ArrowRight') this.next();
      if (e.key === 'Escape')     this.close();
    });
  }

  open(index) {
    this.current = index;
    this.show();
    this.lightbox.classList.add('is-open');
    this.lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  close() {
    this.lightbox.classList.remove('is-open');
    this.lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  prev() {
    this.current = (this.current - 1 + this.images.length) % this.images.length;
    this.show();
  }

  next() {
    this.current = (this.current + 1) % this.images.length;
    this.show();
  }

  show() {
    const data = this.images[this.current];
    if (!data) return;

    this.img.classList.add('is-loading');
    const newImg = new Image();
    newImg.onload = () => {
      this.img.src = data.url;
      this.img.alt = data.alt || '';
      this.img.classList.remove('is-loading');
    };
    newImg.src = data.url;

    if (this.counter) {
      this.counter.textContent = `${this.current + 1} / ${this.images.length}`;
    }
  }
}

document.querySelectorAll('[data-gallery]').forEach(el => new PhotoGallery(el));
