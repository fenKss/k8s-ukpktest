;(function ($){
  toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-bottom-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "50000000000000000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut",

  }
  const types = [
    'success',
    'info',
    'warning',
    'error',
  ];
  const title = `Заголовок`
  const $messages = $('#messages .message');
  $messages.each(function () {

    const $message = $(this),
          message = $message.html();
    let type = $message.data('type');
    if (!types.includes(type)){
      type='info';
    }
    toastr[type](message, title);

  })

})($)