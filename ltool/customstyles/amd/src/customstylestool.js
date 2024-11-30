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
 * Custom styles ltool define js.
 * @module   ltool_customstyles
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define(['jquery', 'core/fragment', 'core/modal_factory', 'core/modal_events', 'core/notification'],
 function($, Fragment, ModalFactory, ModalEvents, notification) {

    /**
     * Controls Custom styles tool action.
     * @param {object} params
     */
    var LearningToolCustomStyles = function(params) {
        var self = this;
        var customstylesToolInfo = document.querySelector(".ltoolcustomstyles-info #ltoolcustomstyles-action");
        if (customstylesToolInfo) {
            // Hover color.
            var customstylestoolhovercolor = customstylesToolInfo.getAttribute("data-hovercolor");
            var customstylestoolfontcolor = customstylesToolInfo.getAttribute("data-fontcolor");
            if (customstylestoolhovercolor && customstylestoolfontcolor) {
                customstylesToolInfo.addEventListener("mouseover", function() {
                    document.querySelector('#ltoolcustomstyles-info p').style.background = customstylestoolhovercolor;
                    document.querySelector('#ltoolcustomstyles-info p').style.color = customstylestoolfontcolor;
                });
            }
            customstylesToolInfo.addEventListener('click', function() {
                self.displaycustomstylesbox(params);
            });
        }
    };

    LearningToolCustomStyles.prototype.displaycustomstylesbox = function(params) {
        var self = this;
        var ltoolcustomstylesbody = document.getElementsByTagName('body')[0];
        if (!ltoolcustomstylesbody.classList.contains('learningtool-customstyles')) {
            ltoolcustomstylesbody.classList.add('learningtool-customstyles');
        }
        ModalFactory.create({
            title: params.modalheader,
            type: ModalFactory.types.SAVE_CANCEL,
            body: self.getcustomstylesbox(params),
            large: true
        }).then(function(modal) {

            modal.show();
            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
            });
            modal.getRoot().on(ModalEvents.save, function(e) {
                e.preventDefault();
                $(e.target).find("button[data-action=save]").attr("disabled", true);
                modal.getRoot().find('form').submit();
            });
            modal.getRoot().on("submit", "form", e => {
                e.preventDefault();
                self.submitFormData(modal, params.contextid);
            });
            return modal;
        }).catch(notification.exception);
    };

    LearningToolCustomStyles.prototype.submitFormData = function(modal, contextId) {
        var modalform = document.querySelectorAll('#ltoolcustomstyles-editorbox form')[0];
        var formData = new URLSearchParams(new FormData(modalform)).toString();
        var args = {
            formdata: formData
        };
        Fragment.loadFragment('ltool_customstyles', 'save_course_customstyles', contextId, args);
        modal.hide();
        window.setTimeout(function() {
            window.location.reload();
        }, 500);
    };

    LearningToolCustomStyles.prototype.getcustomstylesbox = function(params) {
        return Fragment.loadFragment('ltool_customstyles', 'get_customstyles_editor', params.contextid, params);
    };

    return {
        init: function(params) {
            return new LearningToolCustomStyles(params);
        }
    };

 });