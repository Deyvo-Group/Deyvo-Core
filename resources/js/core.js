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
