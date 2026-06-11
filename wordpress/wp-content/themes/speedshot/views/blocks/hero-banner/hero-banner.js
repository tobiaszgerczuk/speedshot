export default class HeroBanner {
  static selector = '.block-hero-banner';

  constructor(element) {
    this.element = element;
    this.slides = Array.from(this.element.querySelectorAll('.block-hero-banner__background-slide'));
    this.currentIndex = 0;
    this.timer = null;
    this.duration = Number.parseInt(this.element.dataset.heroSliderDuration, 10) || 6500;
    this.init();
  }

  init() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    if (this.slides.length === 1) {
      this.slides[0].classList.add('is-single');
      return;
    }

    this.timer = window.setInterval(() => this.showNextSlide(), this.duration);
  }

  showNextSlide() {
    const previousSlide = this.slides[this.currentIndex];
    this.currentIndex = (this.currentIndex + 1) % this.slides.length;
    const nextSlide = this.slides[this.currentIndex];

    previousSlide.classList.remove('is-active');
    nextSlide.classList.remove('is-active');

    window.requestAnimationFrame(() => {
      nextSlide.classList.add('is-active');
    });
  }
}
