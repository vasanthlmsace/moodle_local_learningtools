// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Time Management ltool define js.
 * @module   ltool_timemanagement
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/fragment', 'core/notification', 'core/templates', 'core/ajax', 'ltool_timemanagement/datepicker'],
    function($, Fragment, notification, Templates, Ajax) {

    var ManageDates = function(params) {
        var self = this;
        self.params = params;
        var courseDueDateTypeInfo = document.querySelectorAll(self.courseDueDateTypeSelector)[0];
        if (courseDueDateTypeInfo) {
            courseDueDateTypeInfo.addEventListener("change", self.courseDueDateTypeHandler.bind(this));
        }

        if (self.bodyArea.classList.contains('timemanagement-managedates')) {
            self.bodyArea.classList.remove('timemanagement-managedates');
        }

        var modStartDateTypeInfo = document.querySelectorAll(self.modStartDateTypeSelector);
        if (modStartDateTypeInfo) {
            modStartDateTypeInfo.forEach(item => {
                item.addEventListener("change", self.modStartDateTypeHandler.bind(this));
            });
        }

        var modDueDateTypeInfo = document.querySelectorAll(self.modDueDateTypeSelector);
        if (modDueDateTypeInfo) {
            modDueDateTypeInfo.forEach(item => {
                item.addEventListener("change", self.modDueDateTypeHandler.bind(this));
            });
        }

        var courseDueDateBlockInfo = document.querySelectorAll(self.courseDueDateBlockSelector);
        if (courseDueDateBlockInfo) {
            courseDueDateBlockInfo.forEach(item => {
                item.addEventListener("change", self.courseDueDatecalculate.bind(this));
            });
        }

        var modStartDateDuration = document.querySelectorAll(self.modStartDateDurationSelector);
        if (modStartDateDuration) {
            modStartDateDuration.forEach(item => {
                item.addEventListener("change", self.modStartDatecalculate.bind(this));
            });
        }

        var modDueDateDuration = document.querySelectorAll(self.modDueDateDurationSelector);
        if (modDueDateDuration) {
            modDueDateDuration.forEach(item => {
                item.addEventListener("change", self.modDueDatecalculate.bind(this));
            });
        }

        var datePickerInfo = document.querySelectorAll(self.datepickerSelector);
        if (datePickerInfo) {
            $("input.datepicker").datepicker2({
                autoclose: true,
                format: "yyyy/mm/dd"
            }).change(function(event) {
                if (event.target.id == 'enrolluserdate') {
                    self.setPageDate();
                    self.changeCourseDueDate();
                    self.changeModStartDate();
                    self.changeModDueDate();
                }
            });
        }
    };

    ManageDates.prototype.bodyArea = document.querySelectorAll("body")[0];

    ManageDates.prototype.courseDueDateTypeSelector = ".course-detail-block #course-due-date-info";

    ManageDates.prototype.courseDueDateBlockSelector = ".course-detail-block .course-duedate-block .courseduedate";

    ManageDates.prototype.modStartDateTypeSelector = ".course-detail-block .module-startdate";

    ManageDates.prototype.modStartDateDurationSelector = ".course-detail-block .duration-date-selector .startdate-duration";

    ManageDates.prototype.modDueDateDurationSelector = ".course-detail-block .duration-date-selector .duedate-duration";

    ManageDates.prototype.modDueDateTypeSelector = ".course-detail-block .module-duedate";

    ManageDates.prototype.enrollUserDateSelector = ".course-detail-block #enrolluserdate";

    ManageDates.prototype.datepickerSelector = ".course-detail-block .datepicker";

    ManageDates.prototype.thisPage = ".timemanagement-managedates .course-detail-block";

    ManageDates.prototype.changeCourseDueDate = function() {
        var self = this;
        var courseduedateblock = document.querySelectorAll(".course-duedate-block")[0];
        if (courseduedateblock) {
            var type = courseduedateblock.previousElementSibling.value;
            if (type == 'after') {
                var durationSelector = courseduedateblock.querySelector(".duration-date-selector");
                self.courseManageDueDate(durationSelector);
            }
        }
    };

    ManageDates.prototype.courseManageDueDate = function(durationSelector) {
        var self = this;
        var durationDigits = durationSelector.querySelector('input[name=course-duedate-digits]').value;
        var durationType = durationSelector.querySelector('select[name=course-duedate-duration]').value;
        if (durationDigits && durationType) {
            self.calculateuserdate('after', durationSelector, durationDigits + durationType);
        }
    };

    ManageDates.prototype.courseDueDatecalculate = function(event) {
        var self = this;
        var dateType = event.target.closest(".course-duedate-block").previousElementSibling;
        if (dateType.value == 'after') {
            let durationSelector = event.target.closest(".duration-date-selector");
            self.courseManageDueDate(durationSelector);
        }
    };

    ManageDates.prototype.changeModStartDate = function() {
        var self = this;
        var modstartdateblock = document.querySelectorAll(".mod-startdates-block");
        if (modstartdateblock) {
            modstartdateblock.forEach(element => {
                var type = element.previousElementSibling.value;
                if (type == 'after') {
                    var durationSelector = element.querySelector(".duration-date-selector");
                    self.modManageStartDate(durationSelector);
                } else if (type == "upon") {
                    var customSelector = element.querySelector(".custom-date-selector");
                    var calcuateSelector = customSelector.nextElementSibling;
                    calcuateSelector.querySelector("span").innerHTML = '';
                    calcuateSelector.querySelector("span").innerHTML = self.getPageDate();
                }
            });
        }
    };

    ManageDates.prototype.changeModDueDate = function() {
        var self = this;
        var modduedateblock = document.querySelectorAll(".mod-duedates-block");
        if (modduedateblock) {
            modduedateblock.forEach(element => {
                var type = element.previousElementSibling.value;
                if (type == 'after') {
                    var durationSelector = element.querySelector(".duration-date-selector");
                    self.modManageDueDate(durationSelector);
                }
            });
        }
    };

    ManageDates.prototype.modManageStartDate = function(durationSelector) {
        var self = this;
        var durationDigits = durationSelector.querySelector('#mod-startdate-digits').value;
        var durationType = durationSelector.querySelector('#mod-startdate-duration').value;
        if (durationDigits && durationType) {
            self.calculateuserdate('after', durationSelector, durationDigits + durationType);
        }
    };

    ManageDates.prototype.modManageDueDate = function(durationSelector) {
        var self = this;
        var durationDigits = durationSelector.querySelector('#mod-duedate-digits').value;
        var durationType = durationSelector.querySelector('#mod-duedate-duration').value;
        if (durationDigits && durationType) {
            self.calculateuserdate('after', durationSelector, durationDigits + durationType);
        }
    };

    ManageDates.prototype.modStartDatecalculate = function(event) {
        var self = this;
        var dateType = event.target.closest(".mod-startdates-block").previousElementSibling;
        if (dateType.value == 'after') {
            let durationSelector = event.target.closest(".duration-date-selector");
            self.modManageStartDate(durationSelector);
        }
    };

    ManageDates.prototype.modDueDatecalculate = function(event) {
        var self = this;
        var dateType = event.target.closest(".mod-duedates-block").previousElementSibling;
        if (dateType.value == 'after') {
            let durationSelector = event.target.closest(".duration-date-selector");
            self.modManageDueDate(durationSelector);
        }
    };


    ManageDates.prototype.setPageDate = function() {
        var self = this;
        var enrolluserdate = document.querySelectorAll(self.enrollUserDateSelector)[0];
        var modifyDate = enrolluserdate.value;
        enrolluserdate.setAttribute("data-value", modifyDate);
    };

    ManageDates.prototype.getPageDate = function() {
        var self = this;
        var enrolluserdate = document.querySelectorAll(self.enrollUserDateSelector)[0];
        return enrolluserdate.getAttribute("data-value");
    };

    ManageDates.prototype.getManagedatesContent = function(params) {
        return Fragment.loadFragment('ltool_timemanagement', 'reload_managedates_content', params.contextid, params);
    };

    ManageDates.prototype.modDueDateTypeHandler = function(event) {
        var self = this;
        let type = event.target.value;
        var customSelector = event.target.nextElementSibling.querySelector(".custom-date-selector");
        var durationSelector = event.target.nextElementSibling.querySelector(".duration-date-selector");
        var calcuateSelector = customSelector.nextElementSibling;
        if (type == 'custom') {
            if (customSelector.classList.contains("d-none")) {
                customSelector.classList.remove("d-none");
            }
            if (!durationSelector.classList.contains("d-none")) {
                durationSelector.classList.add("d-none");
            }
            if (!calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.add("d-none");
            }
        } else if (type == 'after') {
            self.modManageDueDate(durationSelector);
            if (!customSelector.classList.contains("d-none")) {
                customSelector.classList.add("d-none");
            }
            if (durationSelector.classList.contains("d-none")) {
                durationSelector.classList.remove("d-none");
            }
            if (calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.remove("d-none");
            }
        } else {
            if (!durationSelector.classList.contains("d-none")) {
                durationSelector.classList.add("d-none");
            }
            if (!customSelector.classList.contains("d-none")) {
                customSelector.classList.add("d-none");
            }
            if (!calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.add("d-none");
            }
        }
    };

    ManageDates.prototype.modStartDateTypeHandler = function(event) {
        var self = this;
        let type = event.target.value;
        var customSelector = event.target.nextElementSibling.querySelector(".custom-date-selector");
        var durationSelector = event.target.nextElementSibling.querySelector(".duration-date-selector");
        var calcuateSelector = customSelector.nextElementSibling;
        calcuateSelector.querySelector("span").innerHTML = '';
        if (type == 'custom') {
            if (customSelector.classList.contains("d-none")) {
                customSelector.classList.remove("d-none");
            }
            if (!durationSelector.classList.contains("d-none")) {
                durationSelector.classList.add("d-none");
            }
            if (!calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.add("d-none");
            }
        } else if (type == 'after') {
            self.modManageStartDate(durationSelector);
            if (!customSelector.classList.contains("d-none")) {
                customSelector.classList.add("d-none");
            }
            if (durationSelector.classList.contains("d-none")) {
                durationSelector.classList.remove("d-none");
            }
            if (calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.remove("d-none");
            }
        } else if (type == 'upon') {
            if (!customSelector.classList.contains("d-none")) {
                customSelector.classList.add("d-none");
            }
            if (!durationSelector.classList.contains("d-none")) {
                durationSelector.classList.add("d-none");
            }
            if (calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.remove("d-none");
            }
            calcuateSelector.querySelector("span").innerHTML = '';
            calcuateSelector.querySelector("span").innerHTML = self.getPageDate();
        } else {
            if (!customSelector.classList.contains("d-none")) {
                customSelector.classList.add("d-none");
            }
            if (!durationSelector.classList.contains("d-none")) {
                durationSelector.classList.add("d-none");
            }
            if (!calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.add("d-none");
            }
        }
    };

    ManageDates.prototype.courseDueDateTypeHandler = function(event) {
        var self = this;
        let type = event.target.value;
        var customSelector = event.target.nextElementSibling.querySelector(".custom-date-selector");
        var durationSelector = event.target.nextElementSibling.querySelector(".duration-date-selector");
        var calcuateSelector = durationSelector.nextElementSibling;
        if (type == 'custom') {
            if (customSelector.classList.contains("d-none")) {
                customSelector.classList.remove("d-none");
            }
            if (!durationSelector.classList.contains("d-none")) {
                durationSelector.classList.add("d-none");
            }
            if (!calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.add("d-none");
            }
        } else if (type == 'after') {
            self.courseManageDueDate(durationSelector);
            if (durationSelector.classList.contains("d-none")) {
                durationSelector.classList.remove("d-none");
            }
            if (!customSelector.classList.contains("d-none")) {
                customSelector.classList.add("d-none");
            }
            if (calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.remove("d-none");
            }
        } else {
            if (!durationSelector.classList.contains("d-none")) {
                durationSelector.classList.add("d-none");
            }
            if (!customSelector.classList.contains("d-none")) {
                customSelector.classList.add("d-none");
            }
            if (!calcuateSelector.classList.contains("d-none")) {
                calcuateSelector.classList.add("d-none");
            }
        }
    };

    ManageDates.prototype.calculateuserdate = function(type, currentselector, time = '',) {
        var self = this;
        var data = {};
        var date = self.getPageDate();
        data.pagedate = date;
        data.type = type;
        data.time = time;
        data.courseid = self.params.courseid;
        Ajax.call([{
            methodname: 'ltool_timemanagement_update_managedates',
            args: {data: data},
            done: function(response) {
                if (response) {
                    var info = currentselector.parentElement.querySelector("#calculate-date span");
                    info.innerHTML = '';
                    info.innerHTML = response;
                }
            }
        }]);
    };

    return {
        init: function(params) {
            return new ManageDates(params);
        }
    };
});