var synsBrithday = {
    init: function () {
        this.dateBirthdayInput = $("input[name=birthday]");
        this.setJalaliDateBrithday();
    },
    setJalaliDateBrithday: function () {
        var birthday = this.dateBirthdayInput.val();
        if (birthday){
            var setDateBrithdayNew = synsBrithday.getDateBrithday();
            this.dateBirthdayInput.val(setDateBrithdayNew.getJalali());
        }
    },
    setSubmitDateBrithday: function () {
        var birthday = this.dateBirthdayInput.val();
        if (birthday){
            var setDateBrithdayNew = synsBrithday.getDateBrithday();
            this.dateBirthdayInput.val(setDateBrithdayNew.getYear() + '-' + setDateBrithdayNew.getMonth() + '-' + setDateBrithdayNew.getDay());
        }
    },
    getDateBrithday: function () {
        return new convertDate (this.dateBirthdayInput.val());
    }
};

$(document).ready(function(){
    if ($('input[name=birthday]').length) {
        synsBrithday.init();
        $("button[type='submit']").closest("form").submit(function() {
            synsBrithday.setSubmitDateBrithday();
        });
    }
});
