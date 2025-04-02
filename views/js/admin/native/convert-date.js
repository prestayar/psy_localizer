/*
 * Prestayar.com
 *
 * @author Hashem Afkhami
 * @version 0.1
 * License: MIT
 *
 * */
Number.prototype.pad = function(size) {
    var s = String(this);
    while (s.length < (size || 2)) {s = "0" + s;}
    return s;
};

var convertDate = function( date ){
    this.set(date);
};
convertDate.prototype = {
    constructor: convertDate,

    set: function(date) {
        var positionBSlash  = date.indexOf("/");

        this.format   = this.getFormatDate(date);
        this.arrayDate = this.parseThisDate(date);
        this.inputDate = this.convertDate(date);

        this.year 	 = this.inputDate.getFullYear();
        this.month 	 = this.inputDate.getMonth()+1;
        this.day 	 = this.inputDate.getDate();
        this.hours 	 = this.inputDate.getHours();
        this.minutes = this.inputDate.getMinutes();
        this.seconds = this.inputDate.getSeconds();

        //this.jalali  = persianDate(this.inputDate);
        //this.yearJalali  = this.inputDate.year();
        //this.monthJalali  = this.inputDate.month();
        //this.dayJalali  = this.inputDate.date();

        return true;
    },
    checkDate: function(date) {
        var year = this.parseThisDate(date)[0];
        return year < 2100 && year > 1900;
    },
    parseThisDate: function ( date ) {
        var dateParts;
        switch(this.format)
        {
            case '_FORMAT_DATE_BACKSLASH_':
                dateParts = date.split("/");
                return [parseInt(dateParts[0]), parseInt(dateParts[1]),parseInt(dateParts[2])];

            case '_FORMAT_DATE_BACKSLASH_INVERSE':
                dateParts = date.split("/");
                return [parseInt(dateParts[2]), parseInt(dateParts[1]), parseInt(dateParts[0])];

            case '_FORMAT_DATE_DASH_':
                dateParts = date.split(" ");
                var dataDateParts = dateParts[0].split("-");
                return [parseInt(dataDateParts[0]), parseInt(dataDateParts[1]),parseInt(dataDateParts[2])];

            case '_FORMAT_DATETIME_DASH_':
                dateParts = date.split(" ");
                var dataDateParts = dateParts[0].split("-");
                var dataTimeParts = dateParts[1].split(":");
                return [parseInt(dataDateParts[0]), parseInt(dataDateParts[1]), parseInt(dataDateParts[2]),parseInt(dataTimeParts[0]), parseInt(dataTimeParts[1]), parseInt(dataTimeParts[2]), 0];

            default:
        }
    },
    convertDate: function(date) {
        if( this.checkDate(date) ) // true => gregorian and false => jalali
            return new Date(date);
        else
            return new persianDate(this.parseThisDate(date)).toLocale('en').toDate();
    },

    getFormatDate: function ( date ) {
        var dateParts;

        dateParts = date.split("/");

        if( 1 in dateParts ) {

            var positionBSlash  = date.indexOf("/");
            if ( positionBSlash == 2 )
                return '_FORMAT_DATE_BACKSLASH_INVERSE'; // '10/07/2017
            else
                return '_FORMAT_DATE_BACKSLASH_'; // '2017/07/10
        }
        else {
            dateParts = date.split(" ");
            if (1 in dateParts)
                return '_FORMAT_DATETIME_DASH_'; // '2017-07-10 15:00:00'
            else
                return '_FORMAT_DATE_DASH_'; // '2017-07-10
        }
    },
    getFormat: function () {
        switch(this.format)
        {
            case '_FORMAT_DATE_BACKSLASH_':
                return 'YYYY/MM/DD';

            case '_FORMAT_DATE_BACKSLASH_INVERSE':
                return 'DD/MM/YYYY';

            case '_FORMAT_DATE_DASH_':
                return 'YYYY-MM-DD';

            case '_FORMAT_DATETIME_DASH_':
                return 'YYYY-MM-DD HH:mm:ss';

            default:
                return 'YYYY-MM-DD';
        }
    },
    getJalali: function() {
        return new persianDate(this.inputDate).toLocale('en').format(this.getFormat());
    },
    getDate: function() {
        switch(this.format)
        {
            case '_FORMAT_DATE_BACKSLASH_':
                return this.year.pad(2) + "/" + this.month.pad(2) + "/" + this.day.pad(2);

            case '_FORMAT_DATE_BACKSLASH_INVERSE':
                return this.day.pad(2) + "/" + this.month.pad(2) + "/" + this.year.pad(2);

            case '_FORMAT_DATE_DASH_':
                return this.year.pad(2) + "-" + this.month.pad(2) + "-" + this.day.pad(2);

            case '_FORMAT_DATETIME_DASH_':
                return this.year.pad(2) + "-" + this.month.pad(2) + "-" + this.day.pad(2) + " " + this.hours.pad(2) + ":" + this.minutes.pad(2) + ":" + this.seconds.pad(2);

            default:
        }

    },

    getYear: function() {return this.year;},
    getMonth: function() {return this.month;},
    getDay: function() {return this.day;},
    getHours: function() {return this.hours;},
    getMinutes: function() {return this.minutes;},
    getSeconds: function() {return this.seconds;},
    //getYearJalali: function() {return this.yearJalali;},
    //getMonthJalali: function() {return this.monthJalali;},
    //getDayJalali: function() {return this.dayJalali;}
};