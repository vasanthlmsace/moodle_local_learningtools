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

 define(['jquery', 'core/fragment', 'core/modal_factory', 'core/modal_events',
    'core/notification', 'core/ajax', 'core/loadingicon'],
 function($, Fragment, ModalFactory, ModalEvents, Notification, Ajax, Loadingicon) {

    /**
     * Controls Time management tool action.
     * @param {object} params
     */
    var LearningToolTimeManagement = function(params) {
        var self = this;
        self.params = params;
        var timeManagementInfo = document.querySelector(".ltooltimemanagement-info #ltooltimemanagement-action");
        if (self.bodyArea.classList.contains('timemanagment-modal')) {
            self.bodyArea.classList.remove('timemanagment-modal');
        }
        if (timeManagementInfo) {
             // Hover color.
             var timeManagementhovercolor = timeManagementInfo.getAttribute("data-hovercolor");
             var timeManagementfontcolor = timeManagementInfo.getAttribute("data-fontcolor");
             if (timeManagementhovercolor && timeManagementfontcolor) {
                timeManagementInfo.addEventListener("mouseover", function() {
                     document.querySelector('#ltooltimemanagement-info p').style.background = timeManagementhovercolor;
                     document.querySelector('#ltooltimemanagement-info p').style.color = timeManagementfontcolor;
                 });
                 timeManagementInfo.addEventListener('click', function() {
                    if (!self.bodyArea.classList.contains('timemanagment-modal')) {
                        self.bodyArea.classList.add('timemanagment-modal');
                    }
                    self.displayTimeManagementBox(params);
                });
             }
        }
        var userSelectorBlock = document.querySelectorAll(self.userSelector)[0];
        if (userSelectorBlock) {
            userSelectorBlock.addEventListener("change", self.changeUserHandler.bind(this, self));
        }
    };

    LearningToolTimeManagement.prototype.bodyArea = document.querySelectorAll("body")[0];

    LearningToolTimeManagement.prototype.userSelector = '.time-management-header #user-select';

    LearningToolTimeManagement.prototype.printSelector = '.time-management-header .print-block';

    LearningToolTimeManagement.prototype.iconBlock = ".time-management-header .right-block .loading-icon";

    LearningToolTimeManagement.prototype.printHandler = function(themeurl, event) {
        var mywindow = window.open('', 'PRINT', 'height=400,width=600');
        mywindow.document.write('<html><head><title>' + document.title + '</title>');
        mywindow.document.write('<link rel="stylesheet" type="text/css" href="' + themeurl + '">');
        mywindow.document.write('</head><body class="timemanagment-modal">');
        mywindow.document.write('<div class="modal-content' + event.target.className + '">');
        mywindow.document.write(document.querySelectorAll(".modal-content")[0].innerHTML);
        mywindow.document.write('</div></body></html>');
        mywindow.document.close();
        mywindow.focus();
        setTimeout(function() {
            mywindow.print();
        }, 1000);
        return true;
    };

    LearningToolTimeManagement.prototype.changeUserHandler = function(self, event) {
        var userid = event.target.value;
        self.params.relateduser = userid;
        Ajax.call([{
            methodname: 'ltool_timemanagement_change_viewdates',
            args: {data: self.params, type: 'header'},
            done: function(response) {
                $(".timemanagment-modal .modal-header").find(".time-management-header").replaceWith(response);
                document.querySelector(".time-management-header #user-select").addEventListener("change",
                    self.changeUserHandler.bind(this, self));
                var printSelectorBlock = document.querySelectorAll(self.printSelector)[0];
                if (printSelectorBlock) {
                    printSelectorBlock.addEventListener("click", self.printHandler.bind(this, self.params.themeurl));
                }
            }
        }]);

        var promises = Ajax.call([{
            methodname: 'ltool_timemanagement_change_viewdates',
            args: {data: self.params, type: 'body'},
            done: function(response) {
                $(".timemanagment-modal .modal-body").find(".viewcourse-date-block").replaceWith(response);
            }
        }]);
        Loadingicon.addIconToContainerRemoveOnCompletion(self.iconBlock, promises);
    };

    LearningToolTimeManagement.prototype.displayTimeManagementBox = function(params) {
        var self = this;
        ModalFactory.create({
            title: self.getTimemanagementHeaderAction(params),
            type: null,
            body: self.getTimemanagementBodyAction(params),
            large: true
        }).then(function(modal) {
            modal.show();
            setTimeout(function() {
                var userSelectorBlock = document.querySelectorAll(self.userSelector)[0];
                if (userSelectorBlock) {
                    userSelectorBlock.addEventListener("change", self.changeUserHandler.bind(this, self));
                }
                var printSelectorBlock = document.querySelectorAll(self.printSelector)[0];
                if (printSelectorBlock) {
                    printSelectorBlock.addEventListener("click", self.printHandler.bind(this, self.params.themeurl));
                }
            }, 5000);
            modal.getRoot().on(ModalEvents.hidden, function() {
                if (self.bodyArea.classList.contains('timemanagment-modal')) {
                    self.bodyArea.classList.remove('timemanagment-modal');
                }
                modal.destroy();
            });
            modal.getRoot().on(ModalEvents.save, function(e) {
                e.preventDefault();
            });
            return modal;
        }).fail(Notification.exception);
    };

    LearningToolTimeManagement.prototype.getTimemanagementBodyAction = function(params) {
        return Fragment.loadFragment('ltool_timemanagement', 'get_timemanagement_bodyform', params.contextid, params);
    };

    LearningToolTimeManagement.prototype.getTimemanagementHeaderAction = function(params) {
        return Fragment.loadFragment('ltool_timemanagement', 'get_timemanagement_headerform', params.contextid, params);
    };

    return {
        init: function(params) {
            return new LearningToolTimeManagement(params);
        }
    };
 });