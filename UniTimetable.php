<?php
/*
Plugin Name: UniTimetable
Plugin URI: 
Description: A plugin to be used by an Educational Institute, in order to store information about the timetable of a department.
Version: 1.1
Author: Fotis Kokkoras, Antonis Roussos
Author URI: https://www.linkedin.com/pub/antonis-roussos/47/25b/9a5
License: GPLv2
*/

//register utt_activate function to run when user activates the plugin
register_activation_hook( __FILE__, 'utt_activate' );
//create tables and view for UniTimetable plugin to the Wordpress Database
function utt_activate(){
    //require upgrade.php so that we can use dbDelta function
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;
    //set table names
    $periodsTable=$wpdb->prefix."utt_periods";
    $subjectsTable=$wpdb->prefix."utt_subjects";
    $groupsTable=$wpdb->prefix."utt_groups";
    $teachersTable=$wpdb->prefix."utt_teachers";
    $classroomsTable=$wpdb->prefix."utt_classrooms";
    $lecturesTable=$wpdb->prefix."utt_lectures";
    $holidaysTable=$wpdb->prefix."utt_holidays";
    $eventsTable=$wpdb->prefix."utt_events";
    $lecturesView=$wpdb->prefix."utt_lectures_view";
    $charset_collate = $wpdb->get_charset_collate();
    
    //create utt tables
    $sql = "CREATE TABLE IF NOT EXISTS `$periodsTable` (
            periodID int UNSIGNED NOT NULL AUTO_INCREMENT,
            year year NOT NULL COMMENT 'year - this way we can keep history',
            semester varchar(45) NOT NULL COMMENT 'Summer, Winter',
            PRIMARY  KEY (periodID),
            UNIQUE KEY `unique_period` (year ASC, semester ASC))
            ENGINE = InnoDB
            $charset_collate;";
    dbDelta($sql);
    
    $sql="CREATE TABLE IF NOT EXISTS `$subjectsTable` (
            subjectID int UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(64) NOT NULL COMMENT 'Subject\' s official Name',
            type varchar(45) NOT NULL COMMENT 'Subject type ex. Theory, Lab, Practice Exercises etc.',
            semester tinyint UNSIGNED NOT NULL COMMENT 'semester where the subject belongs',
            is_enabled tinyint(1) NOT NULL DEFAULT 1 COMMENT 'if the subject is active - application will show only active subjects',
            color varchar(45) NOT NULL COMMENT 'color shown in the curriculum',
            PRIMARY KEY  (subjectID),
            UNIQUE KEY `unique_subject` (title ASC, type ASC))
            ENGINE = InnoDB
            $charset_collate;";
    dbDelta($sql);
    
    $sql="CREATE TABLE IF NOT EXISTS `$groupsTable` (
            groupID int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'unique - for use in the Lectures table',
            periodID int UNSIGNED NOT NULL COMMENT 'FKey from Periods',
            subjectID int UNSIGNED NOT NULL COMMENT 'FKey from Subjects',
            groupName varchar(30) NOT NULL COMMENT 'name of the group',
            PRIMARY KEY  (periodID, subjectID, groupName),
            KEY `fk_Groups_Periods_idx` (periodID ASC),
            KEY `fk_Groups_Subject1_idx` (subjectID ASC),
            UNIQUE KEY `groupID_UNIQUE` (groupID ASC),
            CONSTRAINT `fk_Groups_Periods`
            FOREIGN KEY (periodID)
            REFERENCES `$periodsTable` (periodID)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
            CONSTRAINT `fk_Groups_Subjects`
            FOREIGN KEY (subjectID)
            REFERENCES `$subjectsTable` (subjectID)
            ON DELETE RESTRICT
            ON UPDATE CASCADE)
            ENGINE = InnoDB
            $charset_collate;";
    dbDelta($sql);
    
    $sql="CREATE TABLE IF NOT EXISTS `$teachersTable` (
            teacherID smallint UNSIGNED NOT NULL AUTO_INCREMENT,
            surname varchar(35) NOT NULL COMMENT 'teacher\'s surname',
            name varchar(35) NULL COMMENT 'teacher\'s name',
            PRIMARY KEY  (teacherID),
            UNIQUE KEY `unique_teacher` (surname ASC, name ASC))
            ENGINE = InnoDB
            $charset_collate;";
    dbDelta($sql);
    
    $sql="CREATE TABLE IF NOT EXISTS `$classroomsTable` (
            classroomID smallint UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(35) NOT NULL COMMENT 'name of the classroom',
            type varchar(45) NOT NULL COMMENT 'Lecture classroom, Laboratory',
            is_available tinyint(1) NOT NULL DEFAULT 1 COMMENT 'if classroom is available',
            PRIMARY KEY  (classroomID),
            UNIQUE KEY `unique_classroom` (name ASC))
            ENGINE = InnoDB
            $charset_collate;";
    dbDelta($sql);
    
    $sql="CREATE TABLE IF NOT EXISTS `$lecturesTable` (
            lectureID int UNSIGNED NOT NULL AUTO_INCREMENT,
            groupID int UNSIGNED NOT NULL COMMENT 'FKey from Groups',
            classroomID smallint UNSIGNED NOT NULL COMMENT 'FKey from Classrooms',
            teacherID smallint UNSIGNED NOT NULL COMMENT 'FKey from Teachers',
            start datetime NOT NULL COMMENT 'date-time when the event starts',
            end datetime NOT NULL COMMENT 'date-time when the event ends',
            KEY `fk_Lecture_Classrooms1_idx` (classroomID ASC),
            KEY `fk_Lecture_Teachers1_idx` (teacherID ASC),
            PRIMARY KEY  (lectureID),
            CONSTRAINT `fk_Lectures_Classrooms`
            FOREIGN KEY (classroomID)
            REFERENCES `$classroomsTable` (classroomID)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
            CONSTRAINT `fk_Lectures_Teachers`
            FOREIGN KEY (teacherID)
            REFERENCES `$teachersTable` (teacherID)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
            CONSTRAINT `fk_Lectures_Groups1`
            FOREIGN KEY (groupID)
            REFERENCES `$groupsTable` (groupID)
            ON DELETE RESTRICT
            ON UPDATE CASCADE)
            ENGINE = InnoDB
            $charset_collate;";

    dbDelta($sql);
    
    $sql="CREATE TABLE IF NOT EXISTS `$holidaysTable` (
            holidayDate date NOT NULL COMMENT 'Date of the holiday',
            holidayName varchar(45) NOT NULL COMMENT 'name of the Holiday',
            PRIMARY KEY  (holidayDate))
            ENGINE = InnoDB
            $charset_collate;";
    dbDelta($sql);
    
    $sql="CREATE TABLE IF NOT EXISTS `$eventsTable` (
            eventID int UNSIGNED NOT NULL AUTO_INCREMENT,
            eventType varchar(45) NOT NULL COMMENT 'type of the event',
            eventTitle varchar(45) NOT NULL COMMENT 'title of the event',
            eventDescr varchar(255) NULL COMMENT 'description of the event',
            classroomID smallint UNSIGNED NOT NULL COMMENT 'Fkey from classrooms',
            eventStart datetime NOT NULL COMMENT 'Date-time when the event starts',
            eventEnd datetime NOT NULL COMMENT 'date-time when the event ends',
            PRIMARY KEY  (eventID),
            KEY `fk_wp_utt_events_wp_utt_classrooms1_idx` (classroomID ASC),
            CONSTRAINT `fk_wp_utt_events_wp_utt_classrooms1`
            FOREIGN KEY (classroomID)
            REFERENCES `$classroomsTable` (classroomID)
            ON DELETE RESTRICT
            ON UPDATE CASCADE)
            ENGINE = InnoDB
            $charset_collate;";
    dbDelta($sql);
    
    //create view
    $wpdb->query("CREATE  OR REPLACE VIEW $lecturesView AS
            SELECT
                periodID,
                lectureID,
                semester,
                $lecturesTable.groupID,
                $lecturesTable.classroomID,
                $lecturesTable.teacherID,
                start,
                end,
                groupName,
                $subjectsTable.subjectID,
                $subjectsTable.title AS subjectTitle,
                $subjectsTable.type AS subjectType,
                color,
                $classroomsTable.name AS classroomName,
                $classroomsTable.type AS classroomType,
                surname as teacherSurname,
                $teachersTable.name as teacherName
            FROM
                $lecturesTable,
                $groupsTable,
                $subjectsTable,
                $classroomsTable,
                $teachersTable
            WHERE
                $lecturesTable.groupID = $groupsTable.groupID
                    AND $groupsTable.subjectID = $subjectsTable.subjectID
                    AND $lecturesTable.classroomID = $classroomsTable.classroomID
                    AND $lecturesTable.teacherID = $teachersTable.teacherID;");

}

//register utt_deactivate to run when plugin is deactivated
register_deactivation_hook( __FILE__, 'utt_deactivate' );
//do nothing when plugin deactivates
function utt_deactivate(){
    
}

//register utt_uninstall to run when plugin is uninstalled/deleted
register_uninstall_hook( __FILE__, 'utt_uninstall' );
//delete tables and view on deletion of plugin
function utt_uninstall(){
    global $wpdb;
    //set table names
    $periodsTable=$wpdb->prefix."utt_periods";
    $subjectsTable=$wpdb->prefix."utt_subjects";
    $groupsTable=$wpdb->prefix."utt_groups";
    $teachersTable=$wpdb->prefix."utt_teachers";
    $classroomsTable=$wpdb->prefix."utt_classrooms";
    $lecturesTable=$wpdb->prefix."utt_lectures";
    $holidaysTable=$wpdb->prefix."utt_holidays";
    $eventsTable=$wpdb->prefix."utt_events";
    $lecturesView=$wpdb->prefix."utt_lectures_view";
    //drop view
    $sql = "DROP VIEW IF EXISTS `$lecturesView` ;";
    $wpdb->query($sql);
    //drop tables
    $sql = "DROP TABLE IF EXISTS `$eventsTable` ;";
    $wpdb->query($sql);
    
    $sql = "DROP TABLE IF EXISTS `$lecturesTable` ;";
    $wpdb->query($sql);
        
    $sql="DROP TABLE IF EXISTS `$groupsTable` ;";
    $wpdb->query($sql);

    $sql="DROP TABLE IF EXISTS `$periodsTable` ;";
    $wpdb->query($sql);
        
    $sql="DROP TABLE IF EXISTS `$subjectsTable` ;";
    $wpdb->query($sql);
        
    $sql="DROP TABLE IF EXISTS `$classroomsTable` ;";
    $wpdb->query($sql);

    $sql="DROP TABLE IF EXISTS `$teachersTable` ;";
    $wpdb->query($sql);
    
    $sql="DROP TABLE IF EXISTS `$holidaysTable` ;";
    $wpdb->query($sql);
}

//register utt_load languages on init hook
add_action('init','utt_load_languages');
//load translation files
function utt_load_languages(){
    load_plugin_textdomain('UniTimetable', false, 'UniTimetable/languages');
}
//register utt_UniTimetableMenu_create
add_action('admin_menu','utt_UniTimetableMenu_create');
//Create Menu-Submenus
function utt_UniTimetableMenu_create(){
    //load utt_style.css on every plugin page
    wp_enqueue_style( 'utt_style',  plugins_url('css/utt_style.css', __FILE__) );
    //add main page of plugin
    add_menu_page('UniTimeTable','UniTimeTable','manage_options',__FILE__,'utt_UniTimetable_page' );
    
    //add submenu pages to UniTimetable menu
    $teachersPage = add_submenu_page( __FILE__, __("Insert Teacher","UniTimetable"), __("Teachers","UniTimetable"), 'manage_options',__FILE__.'_teachers', 'utt_create_teachers_page' );
    add_action('load-'.$teachersPage, 'utt_teacher_scripts');
    
    $periodsPage = add_submenu_page( __FILE__, __("Insert Period","UniTimetable"), __("Periods","UniTimetable"), 'manage_options',__FILE__.'_periods', 'utt_create_periods_page' );
    add_action('load-'.$periodsPage, 'utt_period_scripts');
    
    $subjectsPage = add_submenu_page( __FILE__, __("Insert Subject","UniTimetable"), __("Subjects","UniTimetable"), 'manage_options',__FILE__.'_subjects', 'utt_create_subjects_page' );
    add_action('load-'.$subjectsPage, 'utt_subject_scripts');
    
    $classroomsPage = add_submenu_page( __FILE__, __("Insert Classroom","UniTimetable"), __("Classrooms","UniTimetable"), 'manage_options',__FILE__.'_classrooms', 'utt_create_classrooms_page' );
    add_action('load-'.$classroomsPage, 'utt_classroom_scripts');
    
    $groupsPage = add_submenu_page( __FILE__, __("Insert Group","UniTimetable"), __("Groups","UniTimetable"), 'manage_options',__FILE__.'_groups', 'utt_create_groups_page' );
    add_action('load-'.$groupsPage, 'utt_group_scripts');
    
    $holidaysPage = add_submenu_page( __FILE__, __("Insert Holiday","UniTimetable"), __("Holidays","UniTimetable"), 'manage_options',__FILE__.'_holidays', 'utt_create_holidays_page' );
    add_action('load-'.$holidaysPage, 'utt_holiday_scripts');
    
    $eventsPage = add_submenu_page( __FILE__, __("Insert Event","UniTimetable"), __("Events","UniTimetable"), 'manage_options',__FILE__.'_events', 'utt_create_events_page' );
    add_action('load-'.$eventsPage, 'utt_event_scripts');
    
    $lecturesPage = add_submenu_page( __FILE__, __("Insert Lecture","UniTimetable"), __("Lectures","UniTimetable"), 'manage_options',__FILE__.'_lectures', 'utt_create_lectures_page' );
    add_action('load-'.$lecturesPage, 'utt_lecture_scripts');
}

//load main utt page
function utt_UniTimetable_page(){
    global $wpdb;
    //set table names
    $periodsTable=$wpdb->prefix."utt_periods";
    $subjectsTable=$wpdb->prefix."utt_subjects";
    $groupsTable=$wpdb->prefix."utt_groups";
    $teachersTable=$wpdb->prefix."utt_teachers";
    $classroomsTable=$wpdb->prefix."utt_classrooms";
    $lecturesTable=$wpdb->prefix."utt_lectures";
    $holidaysTable=$wpdb->prefix."utt_holidays";
    $eventsTable=$wpdb->prefix."utt_events";
    ?>
    <div class="wrap">
        <h2><?php _e("Main Page of UniTimetable Plugin","UniTimetable"); ?></h2>
        <h3><?php _e("About","UniTimetable"); ?></h3>
        <p>
            <?php _e("<strong>UniTimetable</strong> is a WordPress plugin for presenting timetables of an educational institute. It includes teachers, classrooms, subjects (modules) and student groups, which are all combined to define lectures. The lectures can be scheduled at some time point during a semester. Out of schedule events and holidays are also supported. After providing the plugin with data, shortcodes provided (see below) generate beautiful calendars with all or selected part of the entered data. <strong>UniTimetable</strong> was designed by <a href='https://www.researchgate.net/profile/Fotis_Kokkoras'>Fotis Kokkoras</a> and <a href='https://www.linkedin.com/pub/antonis-roussos/47/25b/9a5'>Antonis Roussos</a> and implemented by <a href='https://www.linkedin.com/pub/antonis-roussos/47/25b/9a5'>Antonis Roussos</a> for the fulfillment of his BSc Thesis in the <a href'http://www.cs.teilar.gr/CS/Home.jsp'>Department of Computer Science and Engineering (TEI of Thessaly, Greece)</a>.","UniTimetable"); ?>
        </p>
        <h3><?php _e("How to use the Shortcodes","UniTimetable"); ?></h3>
            <?php _e("<p>The general purpose shortcode is <strong>[utt_calendar]</strong> and should be better placed in a page (or post) with substantial width. The resulting calendar includes two filter combo-boxes for selecting individual calendars for any of the semester, classroom, and teacher.</p><p>In case that a fixed calendar is required, parameters can be added to precisely define the content to be displayed. More specifically:</p><ul class='bullets'><li>[utt_calendar classroom = &lt;comma separated classroomID list&gt;]   (examples: [utt_calendar classroom=1], [utt_calendar classroom=1,2,3])</li><li>[utt_calendar teacher = &lt;comma separated teacherID list&gt;]</li><li>[utt_calendar semester = &lt;comma separated semester list&gt;]</li></ul><p>Note that the filtered shortcodes generate a calendar without the usual filter combo-boxes.</p>","UniTimetable"); ?>
        <h3><?php
        //show database records
        _e("Database Records","UniTimetable"); ?></h3>
        <?php $teachers = $wpdb->get_row("SELECT count(*) as counter FROM $teachersTable;") ?>
        <?php _e("Teachers:","UniTimetable"); echo " ".$teachers->counter." "; _e("Records","UniTimetable"); ?><br/>
        <?php $periods = $wpdb->get_row("SELECT count(*) as counter FROM $periodsTable;") ?>
        <?php _e("Periods:","UniTimetable"); echo " ".$periods->counter." "; _e("Records","UniTimetable"); ?><br/>
        <?php $subjects = $wpdb->get_row("SELECT count(*) as counter FROM $subjectsTable;") ?>
        <?php _e("Subjects:","UniTimetable"); echo " ".$subjects->counter." "; _e("Records","UniTimetable"); ?><br/>
        <?php $classrooms = $wpdb->get_row("SELECT count(*) as counter FROM $classroomsTable;") ?>
        <?php _e("Classrooms:","UniTimetable"); echo " ".$classrooms->counter." "; _e("Records","UniTimetable"); ?><br/>
        <?php $groups = $wpdb->get_row("SELECT count(*) as counter FROM $groupsTable;") ?>
        <?php _e("Groups:","UniTimetable"); echo " ".$groups->counter." "; _e("Records","UniTimetable"); ?><br/>
        <?php $holidays = $wpdb->get_row("SELECT count(*) as counter FROM $holidaysTable;") ?>
        <?php _e("Holidays:","UniTimetable"); echo " ".$holidays->counter." "; _e("Records","UniTimetable"); ?><br/>
        <?php $events = $wpdb->get_row("SELECT count(*) as counter FROM $eventsTable;") ?>
        <?php _e("Events:","UniTimetable"); echo " ".$events->counter." "; _e("Records","UniTimetable"); ?><br/>
        <?php $lectures = $wpdb->get_row("SELECT count(*) as counter FROM $lecturesTable;") ?>
        <?php _e("Lectures:","UniTimetable"); echo " ".$lectures->counter." "; _e("Records","UniTimetable"); ?>
    </div>
    <?php
}
//require external php files
require('teachersFunctions.php');
require('periodsFunctions.php');
require('subjectsFunctions.php');
require('classroomsFunctions.php');
require('groupsFunctions.php');
require('holidaysFunctions.php');
require('lecturesFunctions.php');
require('eventsFunctions.php');
require('calendarShortcode.php');
?>