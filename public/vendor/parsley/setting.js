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

Parsley.addValidator('lowercase', {
  validateString: function (value) {
    return /[a-z]/.test(value);
  },
  messages: { en: 'Harus ada huruf kecil.', id: 'Harus ada huruf kecil.' }
});

Parsley.addValidator('uppercase', {
  validateString: function (value) {
    return /[A-Z]/.test(value);
  },
  messages: { en: 'Harus ada huruf besar.', id: 'Harus ada huruf besar.' }
});

Parsley.addValidator('digit', {
  validateString: function (value) {
    return /\d/.test(value);
  },
  messages: { en: 'Harus ada angka.', id: 'Harus ada angka.' }
});

Parsley.addValidator('special', {
  validateString: function (value) {
    return /[^a-zA-Z0-9]/.test(value);
  },
  messages: { en: 'Harus ada simbol.', id: 'Harus ada simbol.' }
});

function rules_password(type="create", rules = {}, val = '') {
    rules = {
        minLength: rules.minLength || false,
        lowerCase: rules.lowerCase || false,
        upperCase: rules.upperCase || false,
        digit: rules.digit || false,
        special: rules.special || false,
    }

    if(type == "create") {
        for (const key in rules) {
            if (rules[key]) {
                $(val).append(`<li id="rule-${key}"></li>`)
            }
        }
        rules.minLength && $('#rule-minLength').text('❌ Minimal 8 karakter');
        rules.lowerCase && $('#rule-lowerCase').text('❌ Ada huruf kecil');
        rules.upperCase && $('#rule-upperCase').text('❌ Ada huruf besar');
        rules.digit && $('#rule-digit').text('❌ Ada angka');
        rules.special && $('#rule-special').text('❌ Ada simbol');
    } else {
        rules.minLength && $('#rule-minLength').text((val.length >= rules.minLength ? '✅' : '❌') + ' Minimal ' + rules.minLength + ' karakter');
        rules.lowerCase && $('#rule-lowerCase').text((/[a-z]/.test(val) ? '✅' : '❌') + ' Ada huruf kecil');
        rules.upperCase && $('#rule-upperCase').text((/[A-Z]/.test(val) ? '✅' : '❌') + ' Ada huruf besar');
        rules.digit && $('#rule-digit').text((/\d/.test(val) ? '✅' : '❌') + ' Ada angka');
        rules.special && $('#rule-special').text((/[^a-zA-Z0-9]/.test(val) ? '✅' : '❌') + ' Ada simbol');
    }

}
