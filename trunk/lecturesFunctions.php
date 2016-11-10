<?php
//include js
function utt_lecture_scripts(){
    //include lecture scripts
    wp_enqueue_script( 'lectureScripts',  plugins_url('js/lectureScripts.js', __FILE__) );
    //localize lecture scripts
    wp_localize_script( 'lectureScripts', 'lectureStrings', array(
        'deleteForbidden' => __( 'Delete is forbidden while completing the form!', 'UniTimetable' ),
        'deleteLecture' => __( 'Delete all Lectures for this Group?', 'UniTimetable' ),
        'lectureDeleted' => __( 'Lecture deleted successfully!', 'UniTimetable' ),
        'lecturesDeleted' => __( 'Lectures deleted successfully!', 'UniTimetable' ),
        'editForbidden' => __( 'Edit is forbidden while completing the form!', 'UniTimetable' ),
        'editLecture' => __( 'Edit Lecture', 'UniTimetable' ),
        'cancel' => __( 'Cancel', 'UniTimetable' ),
        'periodVal' => __( 'Please choose a Period.', 'UniTimetable' ),
        'subjectVal' => __( 'Please choose a Subject.', 'UniTimetable' ),
        'groupVal' => __( 'Please choose a Group.', 'UniTimetable' ),
        'teacherVal' => __( 'Please choose a Teacher.', 'UniTimetable' ),
        'classroomVal' => __( 'Please choose a Classroom.', 'UniTimetable' ),
        'dateVal' => __( 'Invalid date.', 'UniTimetable' ),
        'startTimeVal' => __( 'Invalid start time.', 'UniTimetable' ),
        'endTimeVal' => __( 'Invalid end time.', 'UniTimetable' ),
        'timeVal' => __( 'Start time cannot be after end time.', 'UniTimetable' ),
        'insertLecture' => __( 'Insert Lecture', 'UniTimetable' ),
        'reset' => __( 'Reset', 'UniTimetable' ),
        'lang' => __( 'en', 'UniTimetable' ),
        'failAdd' => __( 'Failed to insert Lectures. Check if the Teacher, Classroom or Group is already used.', 'UniTimetable' ),
        'successAdd' => __( 'Lectures added successfully!', 'UniTimetable' ),
        'failEdit' => __( 'Failed to update Lecture. Check if the Teacher, Classroom or Group is already used.', 'UniTimetable' ),
        'successEdit' => __( 'Lecture edited successfully!', 'UniTimetable' ),
    ));
    //include scripts and styles
    wp_enqueue_script( 'moment',  plugins_url('js/moment.min.js', __FILE__) );
    wp_enqueue_script( 'fullcalendar',  plugins_url('js/fullcalendar.js', __FILE__) );
    if(get_locale()=="el"){
        wp_enqueue_script( 'fullcalendargreek',  plugins_url('js/el.js', __FILE__) );
    }
    wp_enqueue_style( 'fullcalendarcss',  plugins_url('css/fullcalendar.css', __FILE__) );
    wp_enqueue_style( 'jqueryui_style', plugins_url('css/jquery-ui.css', __FILE__) );
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-widget');
    wp_enqueue_script('jquery-ui-spinner');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style( 'smoothnesscss',  plugins_url('css/smoothness-jquery-ui.css', __FILE__) );
    wp_enqueue_script('jquerymousewheel', plugins_url('js/jquery.mousewheel.js', __FILE__));
    wp_enqueue_script('globalize', plugins_url('js/globalize.js', __FILE__));
    wp_enqueue_script('globalizede', plugins_url('js/globalize.culture.de-DE.js', __FILE__));
    wp_enqueue_script('qtipjs', plugins_url('js/jquery.qtip.min.js', __FILE__));
    wp_enqueue_style( 'qtipcss',  plugins_url('css/jquery.qtip.min.css', __FILE__) );
    
}

function utt_create_lectures_page(){
    ?>
    <div class="wrap">
    <h2 id="lectureTitle"><?php _e("Insert Lecture","UniTimetable"); ?></h2>
    <div id="dialog-confirm" title="<?php _e("Delete all Lectures for this Group?","UniTimetable"); ?>">
        <p></p>
    </div>
    <form action="" name="lectureForm" method="post">
        <input type="hidden" name="lectureID" id="lectureID" value=0 />
        <div class="element firstInRow">
        <?php _e("Period:","UniTimetable"); ?><br/>
        <select name="period" id="period" class="dirty">
            <?php
            //fill select with periods
            global $wpdb;
            $periodsTable=$wpdb->prefix."utt_periods";
            $periods = $wpdb->get_results( "SELECT * FROM $periodsTable ORDER BY year DESC");
            echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
            foreach($periods as $period){
                if($period->semester == "W"){
                    $semester = __("W","UniTimetable");
                }else{
                    $semester = __("S","UniTimetable");
                }
                echo "<option value='$period->periodID'>$period->year $semester</option>";
            }
            ?>
        </select>
        </div>
        <div class="element">
            <?php _e("Curriculum Semester:","UniTimetable"); ?><br/>
            <select name="semester" id="semester" class="dirty" onchange="loadSubjects(0);">
                <option value="0"><?php _e("- select -","UniTimetable"); ?></option>
                <?php
                //fill select with semester numbers
                for( $i=1 ; $i<11 ; $i++ ){
                    echo "<option value='$i'>$i</option>";
                }
                ?>
            </select>
        </div>
        <div class="element firstInRow">
        <?php _e("Subject:","UniTimetable"); ?><br/>
        <div id="subjects">
            <!-- place subjects into select when period and semester selected -->
        <select name="subject" id="subject" class="dirty">
            <option value='0'><?php _e("- select -","UniTimetable"); ?></option>
        </select>
        </div>
        </div>
        <div class="element">
            <?php _e("Group:","UniTimetable"); ?><br/>
            <div id="groups">
                <!-- place groups when subject selected -->
                <select name="group" id="group" class="dirty">
                    <option value="0"><?php _e("- select -","UniTimetable"); ?></option>
                </select>
            </div>
        </div>
        <div class="element firstInRow">
            <?php _e("Teacher:","UniTimetable"); ?><br/>
            <select name="teacher" id="teacher" class="dirty">
                <?php
                $teachersTable=$wpdb->prefix."utt_teachers";
                $teachers = $wpdb->get_results( "SELECT * FROM $teachersTable ORDER BY surname, name");
                echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
                foreach($teachers as $teacher){
                    echo "<option value='$teacher->teacherID'>$teacher->surname $teacher->name</option>";
                }
                ?>
            </select>
        </div>
        <div class="element">
            <?php _e("Classroom:","UniTimetable"); ?><br/>
            <select name="classroom" id="classroom" class="dirty">
                <?php
                //fill select with classrooms
                $classroomsTable=$wpdb->prefix."utt_classrooms";
                $classrooms = $wpdb->get_results( "SELECT * FROM $classroomsTable ORDER BY name");
                echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
                //translate classroom type
                foreach($classrooms as $classroom){
                    if($classroom->type == "Lecture"){
                        $classroomType = __("Lecture","UniTimetable");
                    }else{
                        $classroomType = __("Laboratory","UniTimetable");
                    }
                    echo "<option value='$classroom->classroomID'>$classroom->name $classroomType</option>";
                }
                ?>
            </select>
        </div>
        <div class="element firstInRow datetimeElements">
            <?php _e("Date:","UniTimetable"); ?>
            <br/>
            <input type="text" name="date" id="date" class="dirty" size="14"/>
        </div>
        <div class="element datetimeElements">
            <?php _e("Start time:","UniTimetable"); ?><br/>
            <input name="time" id="time" class="dirty" value="8:00" size="10"/>
        </div>
        <div class="element datetimeElements">
            <?php _e("End time:","UniTimetable"); ?><br/>
            <input name="endTime" id="endTime" class="dirty" value="10:00" size="10"/>
        </div>
        <div class="element weekDiv">
            <?php _e("Number of weeks:","UniTimetable"); ?><br/>
            <select name="weeks" id="weeks" class="dirty">
                <?php
                for( $i=1 ; $i<26 ; $i++ ){
                    echo "<option value='$i'>$i</option>";
                }
                ?>
            </select>
        </div>
            <div id="secondaryButtonContainer">
                <input type="submit" value="<?php _e("Submit","UniTimetable"); ?>" id="insert-updateLecture" class="button-primary"/>
                <a href='#' class='button-secondary' id="clearLectureForm"><?php _e("Reset","UniTimetable"); ?></a>
            </div>
    </form>
    <div id="messages"></div>
    <div id="filters">
        <span id="filter1">
            <?php _e("View per:","UniTimetable"); ?>&nbsp;
            <select name="filterSelect1" id="filterSelect1">
                <option value="semester" selected="selected"><?php _e("Semester","UniTimetable"); ?></option>
                <option value="teacher"><?php _e("Teacher","UniTimetable"); ?></option>
                <option value="classroom"><?php _e("Classroom","UniTimetable"); ?></option>
            </select>
        </span>
        <span id="filter2">
            <select name="filterSelect2" id="filterSelect2" onchange='filterFunction();'>
                <option value="0"><?php _e("- select -","UniTimetable"); ?></option>
                <?php for($i=1;$i<11;$i++){
                    if($i==1){
                        $selected = "selected='selected'";
                    }else{
                        $selected = "";
                    }
                    echo "<option value='$i' $selected>$i</option>";
                } ?>
            </select>
        </span>
        <img id="loadingImg" src="<?php echo plugins_url('icons/spinner.gif', __FILE__); ?>"/>
    </div>
    <div id="calendar"></div>
    </div>
    <?php
}
//load groups combo-box when period and subject selected
add_action('wp_ajax_utt_load_groups','utt_load_groups');
function utt_load_groups(){
        $period = $_GET['period'];
        $subject = $_GET['subject'];
        if(isset($_GET['selected'])){
            $selected = $_GET['selected'];
        }
        global $wpdb;
        $groupsTable = $wpdb->prefix."utt_groups";
        $safeSql = $wpdb->prepare("SELECT * FROM $groupsTable WHERE periodID=%d AND subjectID=%d ORDER BY groupName;",$period,$subject);
        $groups = $wpdb->get_results($safeSql);
        echo "<select name='group' id='group' class='dirty'>";
        echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
        foreach($groups as $group){
            //choose group selected when edit
            if($selected==$group->groupID){
                $select = "selected='selected'";
            }else{
                $select = "";
            }
            echo "<option value='$group->groupID' $select>$group->groupName</option>";
        }
        echo "</select>";
        die();
}
//load subjects combo-box when period and semester selected
add_action('wp_ajax_utt_load_subjects','utt_load_subjects');
function utt_load_subjects(){
    $semester = $_GET['semester'];
    $selected = $_GET['selected'];
    global $wpdb;
    $subjectsTable = $wpdb->prefix."utt_subjects";
    $safeSql = $wpdb->prepare("SELECT * FROM $subjectsTable WHERE semester=%d ORDER BY title;",$semester);
    $subjects = $wpdb->get_results($safeSql);
    echo "<select name='subject' id='subject' class='dirty' onchange='loadGroups(0,0,0)'>";
    echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
    foreach($subjects as $subject){
        //choose selected subjects when edit
        if($selected==$subject->subjectID){
            $select = "selected='selected'";
        }else{
            $select = "";
        }
        //translate subject type
        if($subject->type == "T"){
            $subjectType = __("T","UniTimetable");
        }else if($subject->type == "L"){
            $subjectType = __("L","UniTimetable");
        }else{
            $subjectType = __("PE","UniTimetable");
        }
        echo "<option value='$subject->subjectID' $select>$subject->title $subjectType</option>";
    }
    echo "</select>";
    die();
}

//ajax response insert-update lecture
add_action('wp_ajax_utt_insert_update_lecture','utt_insert_update_lecture');
function utt_insert_update_lecture(){
    global $wpdb;
    //data to be inserted/updated
    $lectureID=$_GET['lectureID'];
    $group=$_GET['group'];
    $teacher=$_GET['teacher'];
    $classroom=$_GET['classroom'];
    $date=$_GET['date'];
    $time=$_GET['time'];
    $endTime=$_GET['endTime'];
    $weeks=$_GET['weeks'];
    $lecturesTable=$wpdb->prefix."utt_lectures";
    $eventsTable=$wpdb->prefix."utt_events";
    //is insert
    if($lectureID==0){
        //transaction in order to cancel inserts if something goes wrong
        $wpdb->query('START TRANSACTION');
        //if conflict with a teacher, classroom or group, exists becomes 1
        $exists = 0;
        //insert records depending on weeks number
        for ($j=0;$j<=$weeks-1;$j++){
            $d = new DateTime($date);
            //adds record to selected week, next loop adds to next week etc...
            $d->modify('+'.$j.' weeks');
            $usedDate = $d->format('y-m-d');
               
            $datetime = $usedDate." ".$time;
            $endDatetime = $usedDate." ".$endTime;
            //check if there is conflict
            $busyTeacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $lecturesTable WHERE teacherID=%d AND %s<end AND %s>start;",$teacher,$datetime,$endDatetime));
            $busyClassroom1 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $lecturesTable WHERE classroomID=%d AND %s<end AND %s>start;",$classroom,$datetime,$endDatetime));
            $busyClassroom2 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $eventsTable WHERE classroomID=%d AND %s<eventEnd AND %s>eventStart;",$classroom,$datetime,$endDatetime));
            $busyGroup = $wpdb->get_row($wpdb->prepare("SELECT * FROM $lecturesTable WHERE groupID=%d AND %s<end AND %s>start;",$group,$datetime,$endDatetime));
            //if there is conflict, exists becomes 1
            if($busyTeacher!="" || $busyGroup!="" || $busyClassroom1!="" || $busyClassroom2!=""){
                $exists = 1;
                break;
            }else{
                $safeSql = $wpdb->prepare("INSERT INTO $lecturesTable (groupID, classroomID, teacherID, start, end) VALUES( %d, %d, %d, %s, %s)",$group,$classroom,$teacher,$datetime,$endDatetime);
                $wpdb->query($safeSql);
            }
        }
        //if exists is 0 then commit transaction
        if($exists==0){
            $wpdb->query('COMMIT');
            echo 1;
        //if exists is 1 rollback
        }else{
            $wpdb->query('ROLLBACK');
            echo 0;
        }
    //update
    }else{
        $datetime = $date . " " . $time;
        $endDatetime = $date . " " . $endTime;
        $busyTeacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $lecturesTable WHERE teacherID=%d AND %s<end AND %s>start AND lectureID<>%d;",$teacher,$datetime,$endDatetime,$lectureID));
        $busyClassroom1 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $lecturesTable WHERE classroomID=%d AND %s<end AND %s>start AND lectureID<>%d;",$classroom,$datetime,$endDatetime,$lectureID));
        $busyClassroom2 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $eventsTable WHERE classroomID=%d AND %s<eventEnd AND %s>eventStart;",$classroom,$datetime,$endDatetime));
        $busyGroup = $wpdb->get_row($wpdb->prepare("SELECT * FROM $lecturesTable WHERE groupID=%d AND %s<end AND %s>start AND lectureID<>%d;",$group,$datetime,$endDatetime,$lectureID));
        //if any of the selects returns 1, fail
        if($busyTeacher!="" || $busyGroup!="" || $busyClassroom1!="" || $busyClassroom2!=""){
            echo 0;
        }else{
            $safeSql = $wpdb->prepare("UPDATE $lecturesTable SET groupID=%d, classroomID=%d, teacherID=%d, start=%s, end=%s WHERE lectureID=%d;",$group,$classroom,$teacher,$datetime,$endDatetime,$lectureID);
            $wpdb->query($safeSql);
            echo 1;
        }
    }
    die();
}

//json response view lectures etc...
add_action('wp_ajax_utt_json_calendar','utt_json_calendar');
function utt_json_calendar(){
    global $wpdb;
    //filters value
    $viewType = $_POST['viewType'];
    $viewFilter = $_POST['viewFilter'];
    $lecturesView = $wpdb->prefix."utt_lectures_view";
    $start = $_POST['start'];
    $end = $_POST['end'];
    $lecturesTable = $wpdb->prefix."utt_lectures";
    switch ($viewType){
        //add to sql depending on filters
        case "semester":
            $safeSql = $wpdb->prepare("SELECT * FROM $lecturesView WHERE DATE(start) BETWEEN %s AND %s AND semester=%d;",$start,$end,$viewFilter);
            break;
        case "teacher":
            $safeSql = $wpdb->prepare("SELECT * FROM $lecturesView WHERE DATE(start) BETWEEN %s AND %s AND teacherID=%d;",$start,$end,$viewFilter);
            break;
        case "classroom":
            $safeSql = $wpdb->prepare("SELECT * FROM $lecturesView WHERE DATE(start) BETWEEN %s AND %s AND classroomID=%d;",$start,$end,$viewFilter);
            break;
    }
    //start and end of week viewed
    
    $lectures = $wpdb->get_results($safeSql);
    //array witch will be converted to json
    $jsonResponse = array();
    require('calendarColors.php');
    foreach($lectures as $lecture){
        if($viewType=="teacher"){
            //if filtered by teacher, load colors from calendarColors.php
            switch($lecture->subjectType){
                case "L":
                    $color = $colors[$lecture->semester-1][0];
                    break;
                case "T":
                    $color = $colors[$lecture->semester-1][1];
                    break;
                case "PE":
                    $color = $colors[$lecture->semester-1][2];
                    break;
            }
        }else{
            //load colors from database
            $color = "#".$lecture->color;
        }
        //translate subject type
        if($lecture->subjectType == "T"){
            $subjectType = __("T","UniTimetable");
        }else if($lecture->subjectType == "L"){
            $subjectType = __("L","UniTimetable");
        }else{
            $subjectType = __("PE","UniTimetable");
        }
        //array with a lecture
        $result = array();
        $result['title'] = $lecture->subjectTitle." ".$subjectType.", ".$lecture->groupName.", ".$lecture->teacherSurname." ".$lecture->teacherName.", ".$lecture->classroomName;
        $result['start'] = $lecture->start;
        $result['end'] = $lecture->end;
        $result['periodID'] = $lecture->periodID;
        $result['semester'] = $lecture->semester;
        $result['subjectID'] = $lecture->subjectID;
        $result['groupID'] = $lecture->groupID;
        $result['teacherID'] = $lecture->teacherID;
        $result['classroomID'] = $lecture->classroomID;
        $result['lectureID'] = $lecture->lectureID;
        $result['start2'] = $lecture->start;
        $result['end2'] = $lecture->end;
        $result['color'] = $color;
        $result['textColor'] = "black";
        $result['descr'] = "";
        //add lecture to jsonResponse array
        array_push($jsonResponse,$result);
    }
    $holidaysTable = $wpdb->prefix."utt_holidays";
    $safeSql = $wpdb->prepare("SELECT * FROM $holidaysTable WHERE holidayDate BETWEEN %s AND %s;",$start,$end);
    $holidays = $wpdb->get_results($safeSql);
    foreach($holidays as $holiday){
        //array with a holiday
        $result = array();
        $result['title'] = $holiday->holidayName;
        $result['allDay'] = true;
        $result['start'] = $holiday->holidayDate;
        $result['color'] = "red";
        $result['textColor'] = "black";
        $result['descr'] = "";
        $result['buttons'] = false;
        //add holiday to jsonResponse array
        array_push($jsonResponse,$result);
    }
    $eventsTable = $wpdb->prefix."utt_events";
    $classroomsTable = $wpdb->prefix."utt_classrooms";
    //if filtered by classroom, show events for selected classroom
    if($viewType=="classroom"){
        $safeSql = $wpdb->prepare("SELECT * FROM $eventsTable,$classroomsTable WHERE $eventsTable.classroomID=$classroomsTable.classroomID AND DATE(eventStart) BETWEEN %s AND %s AND $eventsTable.classroomID=%d;",$start,$end,$viewFilter);
    }else{
        $safeSql = $wpdb->prepare("SELECT * FROM $eventsTable,$classroomsTable WHERE $eventsTable.classroomID=$classroomsTable.classroomID AND DATE(eventStart) BETWEEN %s AND %s;",$start,$end);
    }
    $events = $wpdb->get_results($safeSql);
    foreach($events as $event){
        //translate event type
        switch($event->eventType){
            case "Thesis":
                $eventType = __("Thesis","UniTimetable");
                break;
            case "Speech":
                $eventType = __("Speech","UniTimetable");
                break;
            case "Presentation":
                $eventType = __("Presentation","UniTimetable");
                break;
            case "Students Team":
                $eventType = __("Students Team","UniTimetable");
                break;
            case "Graduation":
                $eventType = __("Graduation","UniTimetable");
                break;
        }
        //array with an event
        $result = array();
        $result['title'] = $eventType.", ".$event->eventTitle.", ".$event->name;
        $result['start'] = $event->eventStart;
        $result['end'] = $event->eventEnd;
        $result['color'] = "black";
        $result['textColor'] = "white";
        $result['descr'] = ", ".$event->eventDescr;
        $result['buttons'] = false;
        //add event to jsonResponse array
        array_push($jsonResponse,$result);
    }
    //convert jsonResponse array to json
    echo json_encode($jsonResponse);
    die();
}

//ajax response delete group
add_action('wp_ajax_utt_delete_lecture', 'utt_delete_lecture');
function utt_delete_lecture(){
    global $wpdb;
    $deleteAll = $_GET['delete_all'];
    $lectureID = $_GET['lecture_id'];
    $lecturesTable=$wpdb->prefix."utt_lectures";
    $safeSql = $wpdb->prepare("SELECT * FROM $lecturesTable WHERE lectureID=%d",$lectureID);
    $lecture = $wpdb->get_row($safeSql);
    //if delete all is 1, delete all lectures for this group
    if($deleteAll==1){
        $safeSql = $wpdb->prepare("DELETE FROM $lecturesTable WHERE groupID=%d ;",$lecture->groupID);
        $wpdb->query($safeSql);
    //else delete only this lecture
    }else{
        $safeSql = $wpdb->prepare("DELETE FROM `$lecturesTable` WHERE lectureID=%d;",$lectureID);
        $wpdb->query($safeSql);
    }
    die();
}

//ajax response load filter
add_action('wp_ajax_utt_load_filter', 'utt_load_filter');
function utt_load_filter(){
    global $wpdb;
    $viewType = $_GET['viewType'];
    echo "<select name='filterSelect2' id='filterSelect2' onchange='filterFunction();'>";
    echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
    //load second filter depending on the first one
    switch($viewType){
        case "semester":
            for($i=1;$i<11;$i++){
                echo "<option value='$i'>$i</option>";
            }
            break;
        case "teacher":
            $teachersTable = $wpdb->prefix."utt_teachers";
            $teachers = $wpdb->get_results("SELECT * FROM $teachersTable ORDER BY surname, name;");
            foreach($teachers as $teacher){
                echo "<option value='$teacher->teacherID'>$teacher->surname $teacher->name </option>";
            }
            break;
        case "classroom":
            $classroomsTable = $wpdb->prefix."utt_classrooms";
            $classrooms = $wpdb->get_results("SELECT * FROM $classroomsTable ORDER BY name;");
            foreach($classrooms as $classroom){
                echo "<option value='$classroom->classroomID'>$classroom->name </option>";
            }
            break;
    }
    echo "</select>";
    die();
}

?>