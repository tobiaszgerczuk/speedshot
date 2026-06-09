export default class UpcomingEvents {
  static selector = '[data-upcoming-events]';

  constructor(element) {
    this.element = element;
    this.slider = element.querySelector('[data-events-slider]');
    this.prev = element.querySelector('[data-events-prev]');
    this.next = element.querySelector('[data-events-next]');

    if (!this.slider || !this.prev || !this.next) {
      return;
    }

    this.bind();
    this.updateButtons();
  }

  bind() {
    this.prev.addEventListener('click', () => this.scroll(-1));
    this.next.addEventListener('click', () => this.scroll(1));
    this.slider.addEventListener('scroll', () => this.updateButtons(), { passive: true });
    window.addEventListener('resize', () => this.updateButtons());
  }

  scroll(direction) {
    const card = this.slider.querySelector('.block-upcoming-events__card');
    const distance = card ? card.getBoundingClientRect().width + 20 : this.slider.clientWidth * 0.8;

    this.slider.scrollBy({
      left: distance * direction,
      behavior: 'smooth',
    });
  }

  updateButtons() {
    const maxScroll = this.slider.scrollWidth - this.slider.clientWidth - 2;
    const current = this.slider.scrollLeft;

    this.prev.disabled = current <= 2;
    this.next.disabled = current >= maxScroll;
  }
}
