(function () {
  'use strict';

  const one = (selector, root = document) => root.querySelector(selector);
  const all = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  function initHeader(root = document) {
    all('.agris-header-wrap:not([data-ready])', root).forEach((header) => {
      header.dataset.ready = 'true';
      const toggle = one('.agris-nav-toggle', header);
      const nav = one('.agris-main-nav', header);
      if (toggle && nav) {
        toggle.addEventListener('click', () => {
          const open = nav.classList.toggle('is-open');
          toggle.setAttribute('aria-expanded', String(open));
        });
      }
      const lang = one('.agris-lang', header);
      const langTrigger = one('.agris-lang-trigger', header);
      if (lang && langTrigger) {
        langTrigger.addEventListener('click', () => {
          const open = lang.classList.toggle('is-open');
          langTrigger.setAttribute('aria-expanded', String(open));
        });
      }
    });
  }

  function initFilters(root = document) {
    all('.agris-document-widget:not([data-ready]), .agris-document-library:not([data-ready])', root).forEach((widget) => {
      widget.dataset.ready = 'true';
      let activeFilter = 'all';
      let term = '';
      const items = all('[data-agris-category]', widget);
      const empty = one('.agris-no-results', widget);

      const apply = () => {
        let visible = 0;
        items.forEach((item) => {
          const categories = (item.dataset.agrisCategory || '').split(' ');
          const title = (item.dataset.agrisTitle || item.textContent).toLowerCase();
          const show = (activeFilter === 'all' || categories.includes(activeFilter)) && (!term || title.includes(term));
          item.hidden = !show;
          if (show) visible += 1;
        });
        if (empty) empty.hidden = visible > 0;
      };

      all('[data-agris-filter]', widget).forEach((button) => {
        button.addEventListener('click', () => {
          activeFilter = button.dataset.agrisFilter;
          all('[data-agris-filter]', widget).forEach((other) => other.classList.toggle('is-active', other === button));
          apply();
        });
      });
      const search = one('[data-agris-doc-search]', widget);
      if (search) search.addEventListener('input', () => { term = search.value.trim().toLowerCase(); apply(); });
    });
  }

  function initContact(root = document) {
    all('.agris-contact-form:not([data-ready])', root).forEach((form) => {
      form.dataset.ready = 'true';
      form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const status = one('.agris-form-status', form);
        const button = one('[type="submit"]', form);
        if (!form.reportValidity()) return;
        const original = button.textContent;
        button.disabled = true;
        button.textContent = window.agrisWidgets?.i18n?.sending || 'Se trimite…';
        status.className = 'agris-form-status';
        const data = new FormData(form);
        data.append('action', 'agris_contact');
        data.append('nonce', window.agrisWidgets?.nonce || '');
        try {
          const response = await fetch(window.agrisWidgets?.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body: data, credentials: 'same-origin' });
          const payload = await response.json();
          if (!response.ok || !payload.success) throw new Error(payload?.data?.message || window.agrisWidgets?.i18n?.error);
          status.textContent = payload.data.message;
          status.classList.add('is-success');
          form.reset();
        } catch (error) {
          status.textContent = error.message || window.agrisWidgets?.i18n?.error || 'A apărut o eroare.';
          status.classList.add('is-error');
        } finally {
          button.disabled = false;
          button.textContent = original;
        }
      });
    });
  }

  function initAccessibility(root = document) {
    all('.agris-a11y:not([data-ready])', root).forEach((widget) => {
      widget.dataset.ready = 'true';
      const panel = one('.agris-a11y-panel', widget);
      const toggle = one('[data-agris-a11y-toggle]', widget);
      const top = one('[data-agris-top]', widget);
      let state;
      try { state = JSON.parse(localStorage.getItem('agris-a11y') || '{}'); } catch (e) { state = {}; }
      state = Object.assign({ scale: 100, contrast: false, grayscale: false, underline: false }, state);

      const apply = () => {
        document.documentElement.style.fontSize = `${state.scale}%`;
        document.body.classList.toggle('agris-high-contrast', state.contrast);
        document.body.classList.toggle('agris-grayscale', state.grayscale);
        document.body.classList.toggle('agris-underline', state.underline);
        const scaleLabel = one('[data-agris-scale-label]', widget);
        if (scaleLabel) scaleLabel.textContent = `${state.scale}%`;
        all('[data-agris-a11y]', widget).forEach((button) => {
          const on = Boolean(state[button.dataset.agrisA11y]);
          button.classList.toggle('is-on', on);
          button.setAttribute('aria-pressed', String(on));
        });
        localStorage.setItem('agris-a11y', JSON.stringify(state));
      };
      toggle?.addEventListener('click', () => {
        panel.hidden = !panel.hidden;
        toggle.setAttribute('aria-expanded', String(!panel.hidden));
      });
      all('[data-agris-scale]', widget).forEach((button) => button.addEventListener('click', () => {
        state.scale = Math.max(90, Math.min(130, state.scale + (button.dataset.agrisScale === 'up' ? 10 : -10)));
        apply();
      }));
      all('[data-agris-a11y]', widget).forEach((button) => button.addEventListener('click', () => { const key = button.dataset.agrisA11y; state[key] = !state[key]; apply(); }));
      one('[data-agris-reset]', widget)?.addEventListener('click', () => { state = { scale: 100, contrast: false, grayscale: false, underline: false }; apply(); });
      top?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
      window.addEventListener('scroll', () => top?.classList.toggle('is-visible', window.scrollY > 500), { passive: true });
      apply();
    });
  }

  function initSearch(root = document) {
    all('[data-agris-search-modal]:not([data-ready])', root).forEach((modal) => {
      modal.dataset.ready = 'true';
      one('[data-agris-search-close]', modal)?.addEventListener('click', () => { modal.hidden = true; });
      modal.addEventListener('click', (event) => { if (event.target === modal) modal.hidden = true; });
    });
  }

  function initCopy(root = document) {
    all('[data-agris-copy]:not([data-ready])', root).forEach((button) => {
      button.dataset.ready = 'true';
      button.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(button.dataset.agrisCopy); button.textContent = 'Link copiat ✓'; } catch (e) { window.prompt('Copiați linkul:', button.dataset.agrisCopy); }
      });
    });
  }

  function openSearch() {
    const modal = one('[data-agris-search-modal].is-modal');
    if (!modal) return;
    modal.hidden = false;
    setTimeout(() => one('input[type="search"]', modal)?.focus(), 20);
  }

  function init(root = document) {
    initHeader(root); initFilters(root); initContact(root); initAccessibility(root); initSearch(root); initCopy(root);
  }

  document.addEventListener('click', (event) => {
    if (event.target.closest('[data-agris-search]')) openSearch();
    if (!event.target.closest('.agris-lang')) all('.agris-lang.is-open').forEach((item) => item.classList.remove('is-open'));
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') all('[data-agris-search-modal].is-modal').forEach((modal) => { modal.hidden = true; });
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); openSearch(); }
  });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => init()); else init();
  window.addEventListener('elementor/frontend/init', () => {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/global', ($scope) => init($scope[0]));
  });
})();
