@local @local_learningtools @ltool @ltool_timemanagement @javascript
Feature: Check the timemanagement visibile workflow.
  In order to check timemanagement
   ltool features workflow.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | teacher2 | Teacher   | 2        | teacher2@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections | enablecompletion | showcompletionconditions|
      | Course 1 | C1        | topics | 0             | 2           |    1             |        1                |
      | Course 2 | C2        | topics | 0             | 2           |    0             |        0                |
      | Course 3 | C3        | topics | 0             | 2           |    1             |        1                |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course | idnumber    | section | completion |  completionview |
      | assign     | Test assignment name 1 | Test assignment1 description  | C1     | assign1     | 0       |   2        | 1 |
      | assign     | Test assign name       | Test assign description         | C1     | assign5       | 0       |   2        | 1 |
      | assign     | Test assignment name 2 | Test assignment2 description  | C1     | assign2     | 1       |   2        | 1 |
      | assign     | Test assignment name 3 | Test assignment description   | C1     | assign3     | 1       |   0        | 0 |
      | assign     | Test assignment name 4 | Test assignment description   | C1     | assign4     | 2       |   2        | 1 |
    And the following "course enrolments" exist:
      | user     | course | role           | timestart  |
      | teacher1 | C1     | editingteacher | ## today ##|
      | teacher2 | C1     | teacher        | ## today ##|
      | student1 | C1     | student        | ## today ##|
      | student2 | C1     | student        | ##5 days ago## |
      | teacher1 | C2     | editingteacher | ## today ##|
      | teacher1 | C3     | editingteacher | ## today ##|
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I set the field "Edit topic name" in the "li#section-1" "css_element" to "Demo section 1"
    And I set the field "Edit topic name" in the "li#section-2" "css_element" to "Demo section 2"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And "#ltooltimemanagement-info" "css_element" should exist
    And I click on "#ltooltimemanagement-info" "css_element"
    And I wait "3" seconds
    Then I should see "Manage dates"
    And I press "Manage dates"
    And ".course-detail-block" "css_element" should exist
    And I fill "select.module-startdate:nth-child(1)" with "upon"
    And I fill "select.module-duedate:nth-child(1)" with "after"
    And I fill ".course-detail-block tbody tr:nth-child(2) .mod-duedates-block input#mod-duedate-digits" with "1"
    Then I fill ".course-detail-block tbody tr:nth-child(2) select#mod-duedate-duration" with "months"
    And I fill ".course-detail-block tbody tr:nth-child(4) select.module-startdate" with "after"
    And I fill ".course-detail-block tbody tr:nth-child(4) .mod-startdates-block .duration-date-selector input" with "5"
    And I fill ".course-detail-block tbody tr:nth-child(4) .mod-startdates-block .duration-date-selector select" with "days"
    And I fill ".course-detail-block tbody tr:nth-child(4) select.module-duedate" with "after"
    And I fill ".course-detail-block tbody tr:nth-child(4) .mod-duedates-block input#mod-duedate-digits" with "10"
    Then I fill ".course-detail-block tbody tr:nth-child(4) .mod-duedates-block select#mod-duedate-duration" with "days"
    And I fill ".course-detail-block tbody tr:nth-child(5) select.module-startdate" with "after"
    And I fill ".course-detail-block tbody tr:nth-child(5) .mod-startdates-block .duration-date-selector input" with "7"
    Then I fill ".course-detail-block tbody tr:nth-child(5) .mod-startdates-block .duration-date-selector select" with "days"
    And I press "Save and generate calendar entries"
    And I should see "Save changes"
    Then I log out

  Scenario: Student able to view the manage dates.
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test assign name"
    And I am on "Course 1" course homepage
    And I follow "Test assignment name 1"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And I click on "#ltooltimemanagement-info" "css_element"
    And I wait "3" seconds
    Then I see "## today ##%B %d, %Y##" date in the ".viewcourse-date-block thead th:nth-child(1) p"
    And I should see "50% (2/4 activities)" in the ".viewcourse-date-block thead th:nth-child(3) p" "css_element"
    And I should see "-/-" in the ".viewcourse-date-block thead th:nth-child(4) p" "css_element"
    And I should see "Test assignment name 1" in the ".viewcourse-date-block tbody tr:nth-child(2) td:nth-child(2)" "css_element"
    And I see "##today ##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(2) td:nth-child(3)"
    And I see "##+1 months##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(2) td:nth-child(4)"
    Then ".viewcourse-date-block tbody tr:nth-child(2) td:nth-child(5) li.completed " "css_element" should exist
    And I see "##today##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(2) td:nth-child(6)"
    Then I should see "Demo section 1" in the ".viewcourse-date-block tbody tr:nth-child(4) td" "css_element"
    And I should see "Test assignment name 2" in the ".viewcourse-date-block tbody tr:nth-child(4) td:nth-child(2)" "css_element"
    And I see "##+5 days##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(4) td:nth-child(3)"
    And I see "##+10 days##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(4) td:nth-child(4)"
    And I see "##+7 days##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(5) td:nth-child(3)"
    And I should see "" in the ".viewcourse-date-block tbody tr:nth-child(5) td:nth-child(4)" "css_element"
    And I am on "Course 1" course homepage
    Then I log out

  Scenario: Student able to access the manage dates.
    Given I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test assignment name 4"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And I click on "#ltooltimemanagement-info" "css_element"
    And I wait "3" seconds
    Then I see "## -5days ##%B %d, %Y##" date in the ".viewcourse-date-block thead th:nth-child(1) p"
    And I should see "25% (1/4 activities)" in the ".viewcourse-date-block thead th:nth-child(3) p" "css_element"
    And I should see "-/-" in the ".viewcourse-date-block thead th:nth-child(4) p" "css_element"
    And I should see "Test assignment name 1" in the ".viewcourse-date-block tbody tr:nth-child(2) td:nth-child(2)" "css_element"
    And I see "##-5days ##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(2) td:nth-child(3)"
    Then I should see "Demo section 1" in the ".viewcourse-date-block tbody tr:nth-child(4) td" "css_element"
    And I should see "Test assignment name 2" in the ".viewcourse-date-block tbody tr:nth-child(4) td:nth-child(2)" "css_element"
    And I see "##today##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(4) td:nth-child(3)"
    And I see "##+5 days##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(4) td:nth-child(4)"
    And I see "##+2 days##%B %d, %Y##" date in the ".viewcourse-date-block tbody tr:nth-child(5) td:nth-child(3)"
    And I should see "" in the ".viewcourse-date-block tbody tr:nth-child(5) td:nth-child(4)" "css_element"
    And I am on "Course 1" course homepage
    Then I log out

  Scenario: user can see dates in the calendar.
    Given I log in as "student1"
    And I turn editing mode on
    # TODO MDL-57120 site "Tags" link not accessible without navigation block.
    And I follow "Calendar" in the user menu
    Then I should see "Test assignment name 1 should be started"
    And I click on "Test assignment name 1 should be started" "link"
    And I should see "Test assignment name 1 should be started"
    Then I see "##today##%A, %d %B##" date in the ".modal-body a.dimmed"
    And I am on "Course 1" course homepage
    And I log out
