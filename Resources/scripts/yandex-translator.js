var YandexTranslator = (function () {
    var key = $('body').data('trans-api-key');

    return {
        translate: function (text, from, to, successCallback) {
            if (!key) {
                successCallback(text);

                return;
            }

            var lang = [from, to].join('-');

            $.ajax({
                url: 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' + key + '&text=' + text + '&lang=' + lang
            }).done(function (response) {
                if (200 !== response.code) {
                    console.log(response);

                    return;
                }

                successCallback(response.text[0]);
            });
        }
    };
})();
