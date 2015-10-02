var YandexTranslator = (function () {
    return {
        translate: function (text, from, to, successCallback) {
            if ('undefined' === typeof YANDEX_TRANSLATE_API_KEY || '' === YANDEX_TRANSLATE_API_KEY) {
                console.log('Non empty "YANDEX_TRANSLATE_API_KEY" variable must be defined, skip translating.');

                successCallback(text);

                return;
            }

            var lang = from + '-' + to;
            var url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' + YANDEX_TRANSLATE_API_KEY + '&text=' + text + '&lang=' + lang;

            $.ajax({
                url: url
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
