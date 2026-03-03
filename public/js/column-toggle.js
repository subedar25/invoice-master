document.addEventListener('DOMContentLoaded', function () {
  const store = new Map();
  function forEachColClass(fn) {
    document.querySelectorAll('[class]').forEach(el => {
      el.classList.forEach(c => {
        if (c.endsWith('-col')) fn(el, c);
      });
    });
  }
  forEachColClass((el, colClass) => {
    const key = colClass;
    if (!store.has(el)) store.set(el, el.style.display || '');
  });
  function setColumnVisibility(colName, visible) {
    document.querySelectorAll('.' + colName + '-col').forEach(el => {
      if (visible) {
        const prev = store.get(el);
        el.style.display = prev === undefined ? '' : prev;
      } else {
        el.style.display = 'none';
      }
    });
  }
  document.querySelectorAll('.column-toggle').forEach(chk => {
    chk.addEventListener('change', function () {
      setColumnVisibility(this.value, this.checked);
    });
    setColumnVisibility(chk.value, chk.checked);
  });
});

// $.noConflict();
// $(document).on('change', '.row-check', function () {
//     const row = $(this).closest('tr');

//     if (this.checked) {
//         row.addClass('row-selected');
//     } else {
//         row.removeClass('row-selected');
//     }
// });
// $(document).on('change', '#selectAll', function () {
//     const checked = this.checked;

//     $('.row-check').prop('checked', checked).trigger('change');
// });

