@local @local_learningtools @ltool @ltool_forceactivity

Feature: Check the Force activity ltool workflow.

  Background: Create users to check the visbility.
    Given the following "users" exist:
      | username | firstname | lastname | email              |
      | student1 | Student   | User 1   | student1@test.com  |
      | student2 | Student   | User 2   | student2@test.com  |
      | teacher1 | Teacher   | User 1   | teacher1@test.com  |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions | Enable completion tracking |
      | Course 1 | C1        | 0        | 1                | 1                        | yes                        |
    And the following "course enrolments" exist:
      | user | course | role           |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student        |
  @javascript
  Scenario: Test the force activity workflow
    Given I log in as "teacher1"
    And I am on site homepage
    And I click on FAB button
    Then "#ltoolforceactivity-info" "css_element" should not be visible
    When I am on "Course 1" course homepage with editing mode on
    #And I add a "Page" to section "1" and I fill the form with:
    And I add a page activity to course "Course 1" section "1"
    And I expand all fieldsets
    And I set the field "Name" to "Page 1"
    And I set the field "Description" to "Test"
    And I set the field "Page content" to "Test"
    And I set the field "Add requirements" to "1"
    And I set the field "View the activity" to "1"
    And I click on "Save and display" "button"
    #And "Student User 1" user has not completed "Page 1" activity
    And I am on "Course 1" course homepage
    And I click on FAB button
    Then "#ltoolforceactivity-info" "css_element" should be visible
    And I click on "#ltoolforceactivity-info" "css_element"
    And I should see "Force activity" in the ".modal-title" "css_element"
    And I set the following fields to these values:
      | Course activity | Page 1 |
      | Message | Test info message |
    And I press "Save changes"
    And I should see "Successfully added the force activity in the course"
    And I log out
    And I log in as "student1"
    And I am on site homepage
    And I click on FAB button
    Then "#ltoolforceactivity-info" "css_element" should not be visible
    And I am on "Course 1" course homepage
    Then I should see "Test info message"
    And I should see "Page 1"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And "Student User 1" user has completed "Page 1" activity
    Then I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should not see "Test info message"
    And I should see "Course 1"
    Then I log out
