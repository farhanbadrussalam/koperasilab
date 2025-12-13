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
window.Parsley.options.successClass = 'is-valid';
window.Parsley.options.errorClass   = 'is-invalid';

// ganti warna saat error
// window.Parsley.on('field:validated', function (field) {
//     const $element = field.$element;
//     const $errorsList = $element.siblings('.parsley-errors-list');

//     if ($element.closest('.input-group').length) {
//         $element.closest('.input-group').append($errorsList);
//     }

//     if (field.isValid()) {
//         $element.removeClass('is-invalid').addClass('is-valid');
//         $errorsList.removeClass('invalid-feedback'); // Reset color
//     } else {
//         $element.removeClass('is-valid').addClass('is-invalid');
//         $errorsList.addClass('invalid-feedback'); // Change color to red
//     }
// });

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

function rules_password(type="create", rules = {}, val = '', id = '') {
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
                $(val).append(`<li id="rule-${key}-${id}"></li>`)
            }
        }
        rules.minLength && $(`#rule-minLength-${id}`).text('▪ Minimal 8 karakter');
        rules.lowerCase && $(`#rule-lowerCase-${id}`).text('▪ Ada huruf kecil');
        rules.upperCase && $(`#rule-upperCase-${id}`).text('▪ Ada huruf besar');
        rules.digit && $(`#rule-digit-${id}`).text('▪ Ada angka');
        rules.special && $(`#rule-special-${id}`).text('▪ Ada simbol');
    } else {
        let successCount = 0;
        const countRule = Object.values(rules).filter(value => value !== false).length;

        const checkRule = (condition, ruleName, text) => {
            const icon = val === '' ? '▪' : (condition ? '✅' : '❌');
            $(`#rule-${ruleName}-${id}`).text(`${icon} ${text}`);
            if (condition) {
                successCount++;
            }
        };

        if (rules.minLength) checkRule(val.length >= rules.minLength, 'minLength', `Minimal ${rules.minLength} karakter`);
        if (rules.lowerCase) checkRule(/[a-z]/.test(val), 'lowerCase', 'Ada huruf kecil');
        if (rules.upperCase) checkRule(/[A-Z]/.test(val), 'upperCase', 'Ada huruf besar');
        if (rules.digit) checkRule(/\d/.test(val), 'digit', 'Ada angka');
        if (rules.special) checkRule(/[^a-zA-Z0-9]/.test(val), 'special', 'Ada simbol');

        const percentage = (successCount / countRule) * 100;

        let backgroundColor = '';
        // Tambahkan kelas warna baru berdasarkan kondisi
        if (percentage === 100) {
            backgroundColor = 'bg-success';
        } else if (successCount === countRule - 1 && countRule > 1) {
            backgroundColor = 'bg-warning';
        } else {
            backgroundColor = 'bg-danger';
        }
        return { successCount, totalRules: countRule, percentage, backgroundColor }; // Mengembalikan object untuk penggunaan lebih lanjut
    }

}
