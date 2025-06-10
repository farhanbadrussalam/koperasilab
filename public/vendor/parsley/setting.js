window.Parsley.addMessages('id', {
    defaultMessage: "Kolom ini harus diisi.",
    type: {
        email: "Format email tidak valid",
        url: "Kolom ini harus berupa URL.",
        number: "Kolom ini harus berupa angka.",
        integer: "Kolom ini harus berupa angka.",
        digits: "Kolom ini harus berupa angka.",
        alphanum: "Kolom ini harus berupa alfanumerik.",
        minlength: "Minimal %s karakter",
        maxlength: "Maksimal %s karakter",
    },
});

window.Parsley.setLocale('id');

// ganti warna saat error
window.Parsley.on('field:validated', function (field) {
    const $element = field.$element;
    const $errorsList = $element.siblings('.parsley-errors-list');

    if ($element.closest('.input-group').length) {
        $element.closest('.input-group').append($errorsList);
    }

    if (field.isValid()) {
        $element.removeClass('is-invalid').addClass('is-valid');
        $errorsList.removeClass('invalid-feedback'); // Reset color
    } else {
        $element.removeClass('is-valid').addClass('is-invalid');
        $errorsList.addClass('invalid-feedback'); // Change color to red
    }
});
