const stylesEnabled = () => {
  const root = document.documentElement.dataset.deyvoCoreStyles;
  const marker = document.querySelector('[data-deyvo-core-styles]')?.getAttribute('data-deyvo-core-styles');

  return (root ?? marker ?? 'enabled') !== 'disabled';
};

const loadStyles = () => {
  if (stylesEnabled()) {
    import('../css/core.css');
  }
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', loadStyles, { once: true });
} else {
  loadStyles();
}

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

const editorOverlay = document.querySelector('[data-deyvo-editor-overlay]');

const centreEditorOverlay = () => {
  if (!(editorOverlay instanceof HTMLElement)) {
    return;
  }

  editorOverlay.style.setProperty('inset', 'auto', 'important');
  editorOverlay.style.setProperty('position', 'fixed', 'important');
  editorOverlay.style.setProperty('top', '16px', 'important');
  editorOverlay.style.setProperty('right', 'auto', 'important');
  editorOverlay.style.setProperty('bottom', 'auto', 'important');
  editorOverlay.style.setProperty('left', '50vw', 'important');
  editorOverlay.style.setProperty('translate', 'none', 'important');
  editorOverlay.style.setProperty('transform', 'translateX(-50%)', 'important');
};

if (editorOverlay instanceof HTMLElement) {
  if (editorOverlay.parentElement !== document.body) {
    document.body.append(editorOverlay);
  }

  centreEditorOverlay();
  window.addEventListener('resize', centreEditorOverlay);
}

const editor = document.querySelector('[data-deyvo-editor]');

if (editor instanceof HTMLElement) {
  let pendingSave;
  let activeField;
  const editorStatus = document.querySelector('[data-deyvo-editor-status]');

  if (editor.parentElement !== document.body) {
    document.body.append(editor);
  }

  const position = (field) => {
    const padding = 16;
    const gap = 12;
    const fieldBounds = field.getBoundingClientRect();
    const editorBounds = editor.getBoundingClientRect();
    const maxLeft = Math.max(padding, window.innerWidth - editorBounds.width - padding);
    const left = Math.min(Math.max(fieldBounds.left, padding), maxLeft);
    const below = fieldBounds.bottom + gap;
    const above = fieldBounds.top - editorBounds.height - gap;
    const top = below + editorBounds.height <= window.innerHeight - padding || above < padding
      ? Math.min(below, window.innerHeight - editorBounds.height - padding)
      : above;

    editor.style.setProperty('left', `${Math.round(left)}px`, 'important');
    editor.style.setProperty('top', `${Math.max(padding, Math.round(top))}px`, 'important');
  };

  const reposition = () => {
    if (activeField instanceof HTMLElement && !editor.hidden) {
      position(activeField);
    }
  };

  window.addEventListener('resize', reposition);
  window.addEventListener('scroll', reposition, true);

  const save = async (field, control, status) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const value = control instanceof HTMLInputElement && control.type === 'checkbox'
      ? control.checked
      : control.value;

    status.textContent = 'Opslaan...';
    editorStatus?.replaceChildren('Opslaan...');

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
      editorStatus?.replaceChildren('Opslaan mislukt');
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
    editorStatus?.replaceChildren(`Concept v${payload.revision}`);
  };

  const open = (field) => {
    const type = field.dataset.deyvoType ?? 'text';
    const options = JSON.parse(field.dataset.deyvoOptions ?? '[]');
    const value = field.textContent ?? '';
    const label = document.createElement('p');
    const status = document.createElement('p');
    const close = document.createElement('button');
    let control;

    activeField = field;
    editor.hidden = false;
    editor.className = '';
    editor.replaceChildren();

    label.setAttribute('data-deyvo-editor-label', '');
    label.textContent = field.dataset.deyvoField ?? 'Veld';
    status.setAttribute('data-deyvo-editor-status-message', '');
    status.textContent = 'Conceptmodus';
    close.type = 'button';
    close.setAttribute('data-deyvo-editor-close', '');
    close.textContent = 'Sluiten';
    close.addEventListener('click', () => {
      editor.hidden = true;
      activeField = undefined;
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

    control.setAttribute('data-deyvo-editor-control', '');
    control.addEventListener(type === 'select' || type === 'boolean' ? 'change' : 'input', () => {
      clearTimeout(pendingSave);
      pendingSave = setTimeout(() => {
        save(field, control, status).catch(() => {
          status.textContent = 'Opslaan mislukt';
          editorStatus?.replaceChildren('Opslaan mislukt');
        });
      }, type === 'select' || type === 'boolean' ? 0 : 500);
    });

    editor.append(label, close, control, status);
    position(field);
    control.focus();
  };

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const field = event.target.closest('[data-deyvo-field]');

    if (field instanceof HTMLElement) {
      event.preventDefault();
      event.stopPropagation();
      open(field);
    }
  }, true);
}

const builder = document.querySelector('[data-deyvo-builder]');

if (builder instanceof HTMLElement) {
  const readJson = (selector, fallback) => {
    const element = builder.querySelector(selector);

    if (!(element instanceof HTMLScriptElement)) {
      return fallback;
    }

    try {
      return JSON.parse(element.textContent ?? '');
    } catch {
      return fallback;
    }
  };
  const initialBlocks = readJson('[data-deyvo-builder-blocks]', []);
  const blockTypes = readJson('[data-deyvo-builder-types]', []);
  const input = builder.querySelector('[data-deyvo-builder-input]');
  const list = builder.querySelector('[data-deyvo-builder-list]');
  const inspector = builder.querySelector('[data-deyvo-builder-inspector]');
  const catalogue = builder.querySelector('[data-deyvo-builder-catalogue]');
  const count = builder.querySelector('[data-deyvo-builder-count]');
  const dialog = builder.querySelector('[data-deyvo-builder-dialog]');
  const openButton = builder.querySelector('[data-deyvo-builder-open]');
  const closeButton = builder.querySelector('[data-deyvo-builder-close]');
  const types = new Map(Array.isArray(blockTypes) ? blockTypes.map((type) => [type.key, type]) : []);
  let blocks = Array.isArray(initialBlocks) ? initialBlocks : [];
  let selectedId = blocks[0]?.id ?? null;

  const createId = () => {
    if (typeof globalThis.crypto?.randomUUID === 'function') {
      return `block-${globalThis.crypto.randomUUID()}`;
    }

    return `block-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 10)}`;
  };

  const selectedBlock = () => blocks.find((block) => block.id === selectedId) ?? null;

  const button = (label, title, action) => {
    const element = document.createElement('button');
    element.type = 'button';
    element.className = 'inline-flex size-8 items-center justify-center text-sm text-neutral-500 transition hover:bg-neutral-100 hover:text-neutral-950 disabled:cursor-not-allowed disabled:opacity-40';
    element.textContent = label;
    element.title = title;
    element.setAttribute('aria-label', title);
    element.addEventListener('click', action);

    return element;
  };

  const serialise = () => {
    if (input instanceof HTMLInputElement) {
      input.value = JSON.stringify(blocks);
    }

    if (count instanceof HTMLElement) {
      count.textContent = `${blocks.length} ${blocks.length === 1 ? 'blok' : 'blokken'}`;
    }
  };

  const move = (id, direction) => {
    const index = blocks.findIndex((block) => block.id === id);
    const target = index + direction;

    if (index < 0 || target < 0 || target >= blocks.length) {
      return;
    }

    [blocks[index], blocks[target]] = [blocks[target], blocks[index]];
    render();
  };

  const remove = (id) => {
    const index = blocks.findIndex((block) => block.id === id);

    if (index < 0) {
      return;
    }

    blocks.splice(index, 1);
    selectedId = blocks[index]?.id ?? blocks[index - 1]?.id ?? null;
    render();
  };

  const duplicate = (id) => {
    const index = blocks.findIndex((block) => block.id === id);

    if (index < 0) {
      return;
    }

    const copy = JSON.parse(JSON.stringify(blocks[index]));
    copy.id = createId();
    blocks.splice(index + 1, 0, copy);
    selectedId = copy.id;
    render();
  };

  const renderList = () => {
    if (!(list instanceof HTMLElement)) {
      return;
    }

    list.replaceChildren();

    if (blocks.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'border border-dashed border-neutral-300 bg-white px-6 py-12 text-center';
      empty.textContent = 'Voeg het eerste blok toe aan deze pagina.';
      list.append(empty);

      return;
    }

    blocks.forEach((block, index) => {
      const type = types.get(block.type);
      const article = document.createElement('article');
      const header = document.createElement('div');
      const details = document.createElement('div');
      const title = document.createElement('h3');
      const category = document.createElement('p');
      const summary = document.createElement('p');
      const actions = document.createElement('div');
      const values = Object.values(block.data ?? {})
        .filter((value) => typeof value === 'string' && value.trim() !== '')
        .slice(0, 2)
        .join(' - ');

      article.className = block.id === selectedId
        ? 'cursor-pointer border border-sky-700 bg-white shadow-sm'
        : 'cursor-pointer border border-neutral-200 bg-white shadow-sm transition hover:border-neutral-400';
      article.addEventListener('click', () => {
        selectedId = block.id;
        render();
      });
      header.className = 'flex items-start justify-between gap-4 px-4 py-3';
      details.className = 'min-w-0';
      title.className = 'text-sm font-semibold text-neutral-950';
      title.textContent = type?.label ?? block.type;
      category.className = 'mt-1 text-xs font-medium text-neutral-500';
      category.textContent = type?.category ?? 'Aangepast blok';
      summary.className = 'mt-3 border-t border-neutral-100 px-4 py-3 text-sm text-neutral-600';
      summary.textContent = values || 'Nog geen inhoud ingevuld.';
      actions.className = 'flex shrink-0 items-center gap-1';
      actions.addEventListener('click', (event) => event.stopPropagation());
      actions.append(
        button('\u2191', 'Omhoog verplaatsen', () => move(block.id, -1)),
        button('\u2193', 'Omlaag verplaatsen', () => move(block.id, 1)),
        button('\u29c9', 'Dupliceren', () => duplicate(block.id)),
        button('\u00d7', 'Verwijderen', () => remove(block.id))
      );
      details.append(title, category);
      header.append(details, actions);
      article.append(header, summary);
      list.append(article);

      const controls = actions.querySelectorAll('button');
      controls[0].disabled = index === 0;
      controls[1].disabled = index === blocks.length - 1;
    });
  };

  const controlFor = (field, value, update) => {
    let control;

    if (field.type === 'textarea') {
      control = document.createElement('textarea');
      control.rows = 6;
      control.value = typeof value === 'string' ? value : '';
    } else if (field.type === 'select') {
      control = document.createElement('select');

      if (!field.required) {
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecteer een optie';
        control.append(placeholder);
      }

      field.options.forEach((option) => {
        const choice = document.createElement('option');
        choice.value = option.value;
        choice.textContent = option.label;
        choice.selected = option.value === value;
        control.append(choice);
      });
    } else if (field.type === 'boolean') {
      control = document.createElement('input');
      control.type = 'checkbox';
      control.checked = Boolean(value);
      control.className = 'size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950';
    } else {
      control = document.createElement('input');
      control.type = ['email', 'url'].includes(field.type) ? field.type : 'text';
      control.value = typeof value === 'string' ? value : '';
      control.placeholder = field.placeholder ?? '';
    }

    if (field.type !== 'boolean') {
      control.className = 'mt-2 block w-full border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950';
    }

    control.addEventListener('input', () => {
      update(control instanceof HTMLInputElement && control.type === 'checkbox' ? control.checked : control.value);
      serialise();
    });
    control.addEventListener('change', () => {
      update(control instanceof HTMLInputElement && control.type === 'checkbox' ? control.checked : control.value);
      serialise();
    });

    return control;
  };

  const renderInspector = () => {
    if (!(inspector instanceof HTMLElement)) {
      return;
    }

    inspector.replaceChildren();
    const block = selectedBlock();

    if (!block) {
      const heading = document.createElement('h2');
      const text = document.createElement('p');
      heading.className = 'text-sm font-semibold text-neutral-950';
      heading.textContent = 'Blokinstellingen';
      text.className = 'mt-2 text-sm leading-6 text-neutral-600';
      text.textContent = 'Selecteer een blok om de inhoud te bewerken.';
      inspector.append(heading, text);

      return;
    }

    const type = types.get(block.type);
    const heading = document.createElement('h2');
    const description = document.createElement('p');
    const fields = document.createElement('div');
    heading.className = 'text-sm font-semibold text-neutral-950';
    heading.textContent = type?.label ?? block.type;
    description.className = 'mt-1 text-sm leading-6 text-neutral-600';
    description.textContent = type?.description ?? 'Bewerk de inhoud van dit blok.';
    fields.className = 'mt-6 space-y-5';
    inspector.append(heading, description, fields);

    (type?.fields ?? []).forEach((field) => {
      const group = document.createElement('div');
      const label = document.createElement('label');
      const help = document.createElement('p');
      const control = controlFor(field, block.data?.[field.key], (value) => {
        block.data = {
          ...block.data,
          [field.key]: value
        };
      });

      label.className = field.type === 'boolean'
        ? 'flex items-center gap-3 text-sm font-medium text-neutral-900'
        : 'block text-sm font-medium text-neutral-900';
      label.textContent = field.label;

      if (field.type === 'boolean') {
        label.prepend(control);
      } else {
        label.htmlFor = `deyvo-builder-${block.id}-${field.key}`;
        control.id = label.htmlFor;
        group.append(label, control);
      }

      if (field.type === 'boolean') {
        group.append(label);
      }

      if (field.help) {
        help.className = 'mt-2 text-sm leading-6 text-neutral-500';
        help.textContent = field.help;
        group.append(help);
      }

      fields.append(group);
    });
  };

  const add = (type) => {
    const data = {};

    (type.fields ?? []).forEach((field) => {
      data[field.key] = field.type === 'boolean' ? false : '';
    });

    const block = {
      id: createId(),
      type: type.key,
      data
    };
    blocks.push(block);
    selectedId = block.id;

    if (dialog instanceof HTMLDialogElement) {
      dialog.close();
    }

    render();
  };

  const renderCatalogue = () => {
    if (!(catalogue instanceof HTMLElement)) {
      return;
    }

    catalogue.replaceChildren();
    const groups = new Map();

    types.forEach((type) => {
      const category = type.category ?? 'Algemeen';
      groups.set(category, [...(groups.get(category) ?? []), type]);
    });

    groups.forEach((group, category) => {
      const section = document.createElement('section');
      const heading = document.createElement('h3');
      const items = document.createElement('div');
      heading.className = 'text-xs font-semibold uppercase tracking-wide text-neutral-500';
      heading.textContent = category;
      items.className = 'mt-3 grid gap-2 sm:grid-cols-2';
      section.className = 'mt-6 first:mt-0';
      section.append(heading, items);

      group.forEach((type) => {
        const choice = document.createElement('button');
        const label = document.createElement('span');
        const description = document.createElement('span');
        choice.type = 'button';
        choice.className = 'border border-neutral-200 px-4 py-3 text-left transition hover:border-sky-700 hover:bg-sky-50';
        label.className = 'block text-sm font-semibold text-neutral-950';
        label.textContent = type.label;
        description.className = 'mt-1 block text-sm leading-5 text-neutral-600';
        description.textContent = type.description ?? 'Voeg dit blok toe.';
        choice.append(label, description);
        choice.addEventListener('click', () => add(type));
        items.append(choice);
      });

      catalogue.append(section);
    });
  };

  const render = () => {
    serialise();
    renderList();
    renderInspector();
  };

  openButton?.addEventListener('click', () => {
    if (dialog instanceof HTMLDialogElement && !dialog.open) {
      dialog.showModal();
    }
  });
  closeButton?.addEventListener('click', () => {
    if (dialog instanceof HTMLDialogElement) {
      dialog.close();
    }
  });
  dialog?.addEventListener('click', (event) => {
    if (event.target === dialog && dialog instanceof HTMLDialogElement) {
      dialog.close();
    }
  });

  renderCatalogue();
  render();
}
