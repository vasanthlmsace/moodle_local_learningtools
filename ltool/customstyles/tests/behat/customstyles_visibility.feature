@local @local_learningtools @ltool @ltool_customstyles

Feature: Check the CSS ltool workflow.
  In order to check CSS
   ltool features workflow.
  Background: Create users to check the visbility.
    Given the following "users" exist:
      | username | firstname | lastname | email              |
      | student1 | Student   | User 1   | student1@test.com  |
      | teacher1 | Teacher   | User 1   | teacher1@test.com  |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 0        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student        |
      | teacher1 | C1 | editingteacher |

  @javascript
  Scenario: Check the teacher able to custom styles in course.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And I set the navbar hide
    And "#ltoolcustomstyles-info" "css_element" should exist
    And I click on "#ltoolcustomstyles-info" "css_element"
    Then I should see "Custom styles for Course 1"
    And I set the field "parsecustomstyles" to "#page-header{background:black;}"
    And I press "Save changes"
    Then I check header color "rgb(0, 0, 0)"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And I set the navbar hide
    And "#ltoolcustomstyles-info" "css_element" should exist
    And I click on "#ltoolcustomstyles-info" "css_element"
    And I set the field "parsecustomstyles" to "#page-header{background:green;}"
    And I press "Save changes"
    Then I check header color "rgb(0, 128, 0)"

  @javascript
  Scenario: Check the student view course style.
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And "#ltoolcustomstyles-info" "css_element" should not exist
    Then I check header color "rgb(0, 128, 0)"
