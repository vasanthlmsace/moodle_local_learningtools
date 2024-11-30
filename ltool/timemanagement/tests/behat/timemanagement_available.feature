@local @local_learningtools @ltool @ltool_timemanagement @javascript

Feature: Check the timemanagement ltool workflow.
  In order to check timemanagement
   ltool features workflow.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
        | fullname | shortname | format | coursedisplay | numsections | enablecompletion | showcompletionconditions|
        | Course 1 | C1        | topics | 0             | 2           |    1             |        1                |
        | Course 2 | C2        | topics | 0             | 2           |    0             |        0                |
        | Course 3 | C3        | topics | 0             | 2           |    1             |        1                |
    And the following "course enrolments" exist:
      | user     | course | role           | timestart  |
      | teacher1 | C1     | editingteacher | ## today ##|
      | student1 | C1     | student        | ## today ##|
      | teacher1 | C2     | editingteacher | ## today ##|
      | teacher1 | C3     | editingteacher | ## today ##|

  Scenario: Check the student able to timemanagement.
    Given I log in as "student1"
    And I click on FAB button
    And "#ltooltimemanagement-info" "css_element" should not exist
    And I am on "Course 1" course homepage
    Then I should see "Course 1" in the "#page-header .page-header-headings h1" "css_element"
    And I click on FAB button
    And "#ltooltimemanagement-info" "css_element" should exist
    And I click on "#ltooltimemanagement-info" "css_element"
    Then I should see "Time management for Course 1"
    Then I should see "Print"
    And ".viewcourse-date-block" "css_element" should exist

  Scenario: Check teacher able to active timemanagement.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And "#ltooltimemanagement-info" "css_element" should exist
    And I click on "#ltooltimemanagement-info" "css_element"
    And I wait "3" seconds
    Then I should see "Manage dates"
    And I should see "Send message"
    Then I am on "Course 2" course homepage
    And I click on FAB button
    And I click on "#ltooltimemanagement-info" "css_element"
    Then I should see "Turn on completion tracking to use Time Management."
    And I am on "Course 3" course homepage
    And I click on FAB button
    And I click on "#ltooltimemanagement-info" "css_element"
    Then I should see "Please enroll at least one user to use Time Management."
