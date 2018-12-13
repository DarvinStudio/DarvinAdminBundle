const YandexTranslator = (() => {
    const KEY = $('body').data('trans-api-key');

    return {
        translate: (text, from, to, successCallback) => {
            if (null === text || '' === text || !KEY) {
                successCallback(text);

                return;
            }

            let lang = [from, to].join('-');

            $.ajax({
                url: 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' + KEY + '&text=' + text + '&lang=' + lang
            }).done((response) => {
                if (200 !== response.code) {
                    console.log(response);

                    return;
                }

                successCallback(response.text[0]);
            });
        }
    };
})();
