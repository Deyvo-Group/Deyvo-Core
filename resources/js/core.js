document.addEventListener('click', (event) => {
  if (!(event.target instanceof Element)) {
    return;
  }

  const opener = event.target.closest('[data-deyvo-modal-open]');

  if (opener) {
    const selector = opener.getAttribute('data-deyvo-modal-open');
    const modal = selector ? document.querySelector(selector) : null;

    if (modal instanceof HTMLDialogElement && !modal.open) {
      modal.showModal();
    }

    return;
  }

  const closer = event.target.closest('[data-deyvo-modal-close]');

  if (closer) {
    const modal = closer.closest('dialog[data-deyvo-modal]');

    if (modal instanceof HTMLDialogElement) {
      modal.close();
    }

    return;
  }

  const alert = event.target.closest('[data-deyvo-alert-dismiss]');

  if (alert) {
    alert.closest('[role="alert"]')?.remove();
    return;
  }

  const modal = event.target.closest('dialog[data-deyvo-modal]');

  if (modal instanceof HTMLDialogElement && event.target === modal) {
    modal.close();
  }
});

const editor = document.querySelector('[data-deyvo-editor]');

if (editor instanceof HTMLElement) {
  let pendingSave;

  const save = async (field, control, status) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const value = control instanceof HTMLInputElement && control.type === 'checkbox'
      ? control.checked
      : control.value;

    status.textContent = 'Opslaan...';

    const response = await fetch(field.dataset.deyvoUrl ?? '', {
      method: 'PATCH',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token ?? ''
      },
      body: JSON.stringify({
        field: field.dataset.deyvoField,
        value
      })
    });

    if (!response.ok) {
      status.textContent = 'Opslaan mislukt';
      return;
    }

    const payload = await response.json();
    const renderedValue = typeof payload.value === 'boolean'
      ? (payload.value ? '1' : '0')
      : String(payload.value ?? '');
    const selector = '[data-deyvo-field="' + CSS.escape(payload.field) + '"]';

    document.querySelectorAll(selector).forEach((marker) => {
      marker.textContent = renderedValue;
    });

    status.textContent = 'Opgeslagen';
  };

  const open = (field) => {
    const type = field.dataset.deyvoType ?? 'text';
    const options = JSON.parse(field.dataset.deyvoOptions ?? '[]');
    const value = field.textContent ?? '';
    const label = document.createElement('p');
    const status = document.createElement('p');
    const close = document.createElement('button');
    let control;

    editor.hidden = false;
    editor.className = 'fixed inset-x-4 bottom-4 z-50 border border-neutral-200 bg-white p-5 shadow-xl sm:left-auto sm:w-96 relative';
    editor.replaceChildren();

    label.className = 'text-sm font-semibold text-neutral-950';
    label.textContent = field.dataset.deyvoField ?? 'Veld';
    status.className = 'mt-2 text-sm text-neutral-500';
    status.textContent = 'Conceptmodus';
    close.type = 'button';
    close.className = 'absolute right-4 top-4 text-sm font-medium text-neutral-600 hover:text-neutral-950';
    close.textContent = 'Sluiten';
    close.addEventListener('click', () => {
      editor.hidden = true;
    });

    if (type === 'textarea') {
      control = document.createElement('textarea');
      control.rows = 6;
      control.value = value;
    } else if (type === 'select') {
      control = document.createElement('select');

      options.forEach((option) => {
        const element = document.createElement('option');
        element.value = option.value;
        element.textContent = option.label;
        element.selected = option.value === value;
        control.append(element);
      });
    } else {
      control = document.createElement('input');
      control.type = type === 'boolean' ? 'checkbox' : type;

      if (control.type === 'checkbox') {
        control.checked = value === '1' || value === 'true';
      } else {
        control.value = value;
      }
    }

    control.className = type === 'boolean'
      ? 'mt-5 size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950'
      : 'mt-5 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950';
    control.addEventListener(type === 'select' || type === 'boolean' ? 'change' : 'input', () => {
      clearTimeout(pendingSave);
      pendingSave = setTimeout(() => {
        save(field, control, status).catch(() => {
          status.textContent = 'Opslaan mislukt';
        });
      }, type === 'select' || type === 'boolean' ? 0 : 500);
    });

    editor.append(label, close, control, status);
    control.focus();
  };

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const field = event.target.closest('[data-deyvo-field]');

    if (field instanceof HTMLElement) {
      open(field);
    }
  });
}
