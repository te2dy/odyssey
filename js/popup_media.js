'use strict';
$(function () {
  $('#media-select-cancel').on('click', function () {
    window.close();
  });
  $('#media-select-ok').on('click', function () {
    const main = window.opener;
    const href = $("input[name='src']:checked").val();
    main.$('#freelancer_user_image').prop('value', href).trigger('change');

    window.close();
  });
});
