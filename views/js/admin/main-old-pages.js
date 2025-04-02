$(document).ready(function(){
    $("#psy-panel .form-group").find('.control-label.col-lg-4').removeClass('col-lg-4').addClass('col-lg-3');
    $("#psy-panel .form-group").find('.col-lg-8:not(.psy-draggable)').removeClass('col-lg-8').addClass('col-lg-9');

    if (typeof Localizer_JalaliDate !== 'undefined' && Localizer_JalaliDate == '1') {

        var input = $('input.datepicker, input.datetimepicker, #calendar input.date-input,.datepicker input');
        window.formatPersian = false;

        input.closest("form").submit(function() {
            input.each(function(){
                var value = $(this).val();
                if (value !== '' && value !== '0000-00-00 00:00:00' && value !== '0000-00-00') {
                    var date = new convertDate(persianNumberToEnglish(value));
                    $(this).val(date.getDate());
                }
            });

            localFilterDateProcess();
        });
        input.each(function(){
            var value = $(this).val();
            if( value !== '' && value !== '0000-00-00 00:00:00' && value !== '0000-00-00' ){
                var date = new convertDate(persianNumberToEnglish(value));
                $(this).val(date.getJalali());
            }
        });

        localFilterDateProcess();

        $('.datepicker1,.datepicker2').each( function() {
            var date = new convertDate($(this).data('date'));
            $(this).data('date', date.getJalali());
        });
        $('#datepicker-from-info,#datepicker-to-info').each(function(){
            var date = new convertDate($(this).html());
            $(this).html(date.getJalali());
        });
    }
});

function localFilterDateProcess() {
    $("[id^='local_']").each(function(){
        var local_id = $(this).attr('id');
        var local_name = local_id.substr('local_'.length);
        var local_value = $(this).val();

        if (local_value) {
            convertValue = moment(local_value, 'DD/MM/YYYY').format('YYYY-MM-DD');
            convertValue = persianNumberToEnglish(convertValue);
            $("#" + local_name).val( convertValue );
        }
    });
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

    var persianNumbers = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰"],
        englishNumbers = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];

    for (var i = 0, numbersLen = persianNumbers.length; i < numbersLen; i++) {
        value = value.replace(new RegExp(persianNumbers[i], "g"), englishNumbers[i]);
    }

    return value;
}
