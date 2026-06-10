export default function initContactForms() {
  document.querySelectorAll('.block-contact-section__form').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      handleSubmit(form, {
        noticeSelector: '.block-contact-section__notice',
        btnSelector: '.block-contact-section__submit',
        resetOnSuccess: true,
      });
    });
  });

  document.querySelectorAll('[data-event-reg-form]').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      handleSubmit(form, {
        noticeSelector: '.single-event__reg-notice',
        btnSelector: '.single-event__reg-submit',
        resetOnSuccess: true,
        onSuccess: () => { form.style.display = 'none'; },
      });
    });
  });
}

async function handleSubmit(form, opts = {}) {
  const {
    noticeSelector = '.block-contact-section__notice',
    btnSelector    = '.block-contact-section__submit',
    resetOnSuccess = true,
    onSuccess      = null,
  } = opts;

  const btn    = form.querySelector(btnSelector);
  const card   = form.closest('[class*="__form-card"], [class*="__reg-card"], form') ?? form.parentElement;
  const notice = card.querySelector(noticeSelector) ?? form.previousElementSibling;

  setLoading(form, btn, true);
  clearNotice(notice);

  const data = new FormData(form);

  try {
    const res  = await fetch(SPEEDSHOT.ajaxUrl, { method: 'POST', body: data });
    const json = await res.json();

    if (json.success) {
      showNotice(notice, json.data.message, 'success');
      if (resetOnSuccess) form.reset();
      if (onSuccess) onSuccess();
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
