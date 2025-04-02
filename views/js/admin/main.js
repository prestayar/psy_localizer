$(document).ready(function(){
    initdatePickers();

    $(document).bind("ajaxComplete", function(event, xhr, settings){
        if (settings.url.indexOf("specific-prices") > 0) {
            initdatePickers();
        }
    });

    $(document).bind("ajaxSend", function(event, xhr, settings){
        if (settings.url.indexOf("specific-prices") > 0 && settings.url.indexOf("update") > 0 ) {
            if (typeof settings.data !== 'undefined') {
                const urlSearchParams = new URLSearchParams(settings.data);
                const params = Object.fromEntries(urlSearchParams.entries());

                $.each(params, function(index, value) {
                    if (index ==='form[modal][sp_to]' || index === 'form[modal][sp_from]' ) {
                        params[index] = getConvertDateValue(value);
                    }

                    if (index ==='form[modal][sp_from_quantity]') {
                        params[index] = persianNumberToEnglish(value);
                    }
                });

                settings.data = $.param(params);
            }
        }

        if (settings.url.indexOf("catalog") > 0 &&
            settings.url.indexOf("products") > 0 &&
            settings.url.indexOf("virtual/save") < 0
        ) {
            if (typeof settings.data !== 'undefined') {
                const params = getQueryParams(settings.data);
                $.each(params, function(index, value) {
                    if (index.indexOf("[attribute") > 0) {
                        params[index] = persianNumberToEnglish(value);
                    }
                });
                settings.data = $.param(params);
            }
        }
    });

    $('.datepicker input[type="text"]').on('dp.show',function(){
        $(this).datetimepicker('destroy');
    });
});

function initdatePickers() {
    if (typeof Localizer_JalaliDate !== 'undefined' && Localizer_JalaliDate == '1') {
        const $datePickers = $('.datepicker input[type="text"]');
        $.each($datePickers, (i, picker) => {
            var instance = $(picker).data('DateTimePicker');
            if (instance) {
                $(picker).datetimepicker('destroy');
            }
            //$(picker).data("DateTimePicker").destroy();
            //console.log($(picker).attr('id'));

            var $initialValue = false,
                $format = $(picker).data('format'),
                $timePicker = {
                    enabled: false
                };

            var $valueDefualt = $(picker).attr('value');
            if ($valueDefualt && $valueDefualt !== '0000-00-00') {
                $initialValue = true;
            }

            if ($format && $format === 'YYYY-MM-DD H:m:s') {
                $timePicker = {
                    enabled: true,
                    meridiem: {
                        enabled: true
                    }
                };
            }

            $(picker).persianDatepicker({
                initialValue: $initialValue,
                autoClose: true,
                initialValueType: 'gregorian',
                observer: true,
                format: $(picker).data('format') ? $(picker).data('format') : 'YYYY-MM-DD', // YYYY-MM-DD hh:mm
                timePicker: $timePicker,
                onSelect: function(unix){
                    $(picker).parents(".column-filters").find(".grid-search-button").prop("disabled", !1);
                }
            });
        });

        $datePickers.closest("form").submit(function() {
            $datePickers.each(function(){
                var value = $(this).val();
                if (value !== '' && value !== '0000-00-00 00:00:00' && value !== '0000-00-00') {
                    var date = new convertDate(persianNumberToEnglish(value));
                    $(this).val(date.getDate());
                }
            });
        });

        $("#form_step2_specific_price_save").click(function() {
            $.each($datePickers, (i, picker) => {
                getDateInput($(picker));
            });

            var $quantity = $("#form_step2_specific_price_sp_from_quantity");
            $quantity.val(persianNumberToEnglish($quantity.val()));
        });
    }
}
function getDateInput($element) {
    var value = getConvertDateValue($element.val());
    $element.val(value);
    return value;
}
function getConvertDateValue(value) {
    if (value && value !== '0000-00-00 00:00:00' && value !== '0000-00-00') {
        var date = new convertDate(persianNumberToEnglish(value));
        value = date.getDate();
    }
    return value;
}
function persianNumberToEnglish(value) {
    if (!value) {
        return;
    }
    var arabicNumbers = ["١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩", "٠"],
        persianNumbers = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰"];

    for (var i = 0, numbersLen = arabicNumbers.length; i < numbersLen; i++) {
        value = value.replace(new RegExp(arabicNumbers[i], "g"), persianNumbers[i]);
    }

    var persianNumbers = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰", "٫"],
        englishNumbers = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "."];

    for (var i = 0, numbersLen = persianNumbers.length; i < numbersLen; i++) {
        value = value.replace(new RegExp(persianNumbers[i], "g"), englishNumbers[i]);
    }

    return value;
}

const getQueryParams = (query) => {
    let params = {};
    query = decodeURI(query);

    new URLSearchParams(query).forEach((value, key) => {
        let decodedKey = key;//decodeURIComponent(key);
        // console.log(value);
        // let decodedValue = decodeURIComponent(value);
        // This key is part of an array
        if (decodedKey.endsWith("[]")) {
            decodedKey = decodedKey.replace("[]", "");
            params[decodedKey] || (params[decodedKey] = []);
            params[decodedKey].push(value);
            // Just a regular parameter
        } else {
            params[decodedKey] = value;
        }
    });

    return params;
};