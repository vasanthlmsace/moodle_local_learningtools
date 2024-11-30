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

 define(['jquery', 'core/modal_factory', 'core/str', 'core/fragment', 'core/modal_events', 'core/ajax', 'core/notification'],
 function($, ModalFactory, Str, Fragment, ModalEvents, Ajax, notification) {

    /**
     * Controls bookmarks tool action.
     * @param {object} params
     */
    function learningToolInviteAction(params) {
        showModalInvitetool(params);
    }

    /**
     * Display the modal to invite user emails.
     * @param {object} params
     */
    function showModalInvitetool(params) {
        var inviteinfo = document.querySelector(".ltoolinvite-info #ltoolinvite-action");
        if (inviteinfo) {
            inviteinfo.addEventListener("click", function() {
                // Strinviteusers.
                ModalFactory.create({
                    title: params.strinviteusers,
                    body: getInviteAction(params),
                    large: true
                }).then(function(modal) {
                    modal.show();
                    modal.getRoot().on(ModalEvents.bodyRendered, function() {
                        var inviteuserForm = document.querySelector("#invite-users-area form");
                        if (inviteuserForm) {
                            inviteuserForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                submitFormData(modal, params);
                            });
                        }
                        var saveAction = document.querySelector("#inviteusers-action #save-action");
                        if (saveAction) {
                            saveAction.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.target.setAttribute("disabled", true);
                                document.querySelectorAll('#invite-users-area #inviteuser-action')[0].click();
                            });
                        }
                        var cancelAction = document.querySelector("#inviteusers-action #cancel-action");
                        if (cancelAction) {
                            cancelAction.addEventListener('click', function(e) {
                                e.preventDefault();
                                modal.destroy();
                            });
                        }
                    });

                    var footer = getFooterContent(params);
                    modal.setFooter(footer);
                    return modal;
                }).catch(notification.exception);
            });
            // Hover color.
            var invitehovercolor = inviteinfo.getAttribute("data-hovercolor");
            var invitefontcolor = inviteinfo.getAttribute("data-fontcolor");
            if (invitehovercolor && invitefontcolor) {
                inviteinfo.addEventListener("mouseover", function() {
                    document.querySelector('#ltoolinvite-info p').style.background = invitehovercolor;
                    document.querySelector('#ltoolinvite-info p').style.color = invitefontcolor;
                });
            }
        }
    }

    /**
     * Submit the modal data form.
     * @param {object} modal object
     * @param {array} params  list of parameters
     * @return {void} ajax respoltoolsnse.
     */
    function submitFormData(modal, params) {
        var modalform = document.querySelectorAll('#invite-users-area form')[0];
        var formData = new URLSearchParams(new FormData(modalform)).toString();
        var jsonparams = JSON.stringify(params);
        Ajax.call([{
            methodname: 'ltool_invite_inviteusers',
            args: {params: jsonparams, formdata: formData},
            done: function(response) {
                if (response) {
                    modal.hide();
                    var successinfo = Str.get_string('successinviteusers', 'local_learningtools');
                    $.when(successinfo).done(function(localizedEditString) {
                        notification.addNotification({
                            message: localizedEditString,
                            type: "success"
                        });
                    });
                    var listurl = M.cfg.wwwroot + "/local/learningtools/ltool/invite/list.php?id=" + params.user +
                    "&courseid=" + params.course;
                    window.open(listurl, '_self');
                } else {
                    modal.destroy();
                }
            }
        }]);
    }

    /**
     * Get invite user emails form.
     * @param {object} params
     * @return {string} textarea html
     */
    function getInviteAction(params) {
        return Fragment.loadFragment('ltool_invite', 'get_inviteusers_form', params.contextid, params);
    }
    /**
     * Display the list of invite users footer.
     * @param {object} params
     * @returns {string} footer block.
     */
    function getFooterContent(params) {
        var content = '';
        content += "<div class='inviteusers-footer' id='inviteusers-footer'>";
        var listurl = M.cfg.wwwroot + "/local/learningtools/ltool/invite/list.php?id=" + params.user +
        "&courseid=" + params.course;
        if (params.showreports) {
            content += "<div id='list-action-url'><a href='" + listurl + "' target='_blank'>" + params.strinvitelist + "</a></div>";
        }
        content += "<div id='inviteusers-action' class='inviteusers-action'>";
        if (params.showinvitebox) {
            content += "<button id='save-action' type='button' class='btn btn-primary' data-action='save'>"
            + params.strinvitenow + "</button>";
        }
        content += "<button id='cancel-action' type='button' class='btn btn-primary' data-action='cancel'>"
        + params.strcancel + "</button>";
        content += "</div>";
        content += "</div>";
        return content;
    }

    return {
        init: function(params) {
            learningToolInviteAction(params);
        }
    };
 });