export default function initContactForms() {
  document.querySelectorAll('.block-contact-section__form').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      handleSubmit(form);
    });
  });
}

async function handleSubmit(form) {
  const btn = form.querySelector('.block-contact-section__submit');
  const notice = form.closest('.block-contact-section__form-card').querySelector('.block-contact-section__notice');

  setLoading(form, btn, true);
  clearNotice(notice);

  const data = new FormData(form);
  data.set('action', 'speedshot_contact_submit');

  try {
    const res = await fetch(SPEEDSHOT.ajaxUrl, { method: 'POST', body: data });
    const json = await res.json();

    if (json.success) {
      showNotice(notice, json.data.message, 'success');
      form.reset();
    } else {
      showNotice(notice, json.data?.message ?? 'Wystąpił błąd. Spróbuj ponownie.', 'error');
    }
  } catch {
    showNotice(notice, 'Błąd połączenia. Sprawdź internet i spróbuj ponownie.', 'error');
  } finally {
    setLoading(form, btn, false);
  }
}

function setLoading(form, btn, loading) {
  form.classList.toggle('is-loading', loading);
  btn.disabled = loading;
}

function showNotice(el, message, type) {
  if (!el) return;
  el.textContent = message;
  el.className = `block-contact-section__notice block-contact-section__notice--${type}`;
  el.hidden = false;
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function clearNotice(el) {
  if (!el) return;
  el.textContent = '';
  el.hidden = true;
}
