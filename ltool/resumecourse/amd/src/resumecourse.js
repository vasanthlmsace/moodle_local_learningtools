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
 * Invite ltool define js.
 * @module   ltool_invite
 * @category  Classes - autoloading
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define(['jquery', 'core/ajax', 'core/notification'],
 function($, Ajax, Notification) {

    /* global ltools */

    /**
     * Controls resume course tool action.
     * @param {object} params
     */
    function learningToolResumeCourseAction(params) {
        var resumecourseinfo = document.querySelector(".ltoolresumecourse-info #ltoolresumecourse-action");
        if (resumecourseinfo) {
            resumecourseinfo.addEventListener("click", function() {
                if (typeof params == 'object') {
                    params = JSON.stringify(params);
                }
                Ajax.call([{
                    methodname: 'ltool_resumecourse_lastaccess_activity',
                    args: {params: params},
                    done: function(response) {
                        if (response.url) {
                            window.open(response.url, '_self');
                        }
                        if (response.message) {
                            Notification.addNotification({
                                message: response.message,
                                type: "success"
                            });
                        }
                        if (ltools.disappertimenotify != 0) {
                            setTimeout(function() {
                                document.querySelector("span.notifications").innerHTML = "";
                            }, ltools.disappertimenotify);
                        }
                    }
                }]);
            });

            // Hover color.
            var resumecoursehovercolor = resumecourseinfo.getAttribute("data-hovercolor");
            var resumecoursefontcolor = resumecourseinfo.getAttribute("data-fontcolor");
            if (resumecoursehovercolor && resumecoursefontcolor) {
                resumecourseinfo.addEventListener("mouseover", function() {
                    document.querySelector('#ltoolresumecourse-info p').style.background = resumecoursehovercolor;
                    document.querySelector('#ltoolresumecourse-info p').style.color = resumecoursefontcolor;
                });
            }
        }
    }

    return {
        init: function(params) {
            learningToolResumeCourseAction(params);
        }
    };

 });