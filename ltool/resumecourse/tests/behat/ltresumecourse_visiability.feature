@local @local_learningtools @ltool @ltool_resumecourse

Feature: Check the Resume course ltool workflow.
  Background: Create users to check the visbility.
    Given the following "users" exist:
      | username | firstname | lastname | email              |
      | student1 | Student   | User 1   | student1@test.com  |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 0        | 1                | 1                        |
      | Course 2 | C2        | 0        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student |
      | student1 | C2 | student |
    And the following "activities" exist:
      | activity   | name   | intro              | course | idnumber |
      | quiz       | Quiz 1 | Quiz 1 description | C1     | quiz1    |
      | quiz       | Quiz 2 | Quiz 2 description | C2     | quiz2    |

  @javascript
  Scenario: Check the resume course tool.
    When I log in as "student1"
    And I click on FAB button
    And "#ltoolresumecourse-info" "css_element" should be visible
    And I click on "#ltoolresumecourse-info" "css_element"
    Then I should see "You don't have any pages to resume"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I am on site homepage
    And I click on FAB button
    Then "#ltoolresumecourse-info" "css_element" should be visible
    And I click on "#ltoolresumecourse-info" "css_element"
    Then I should see "Quiz 1"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And I click on "#ltoolresumecourse-info" "css_element"
    Then I should see "Quiz 1"
