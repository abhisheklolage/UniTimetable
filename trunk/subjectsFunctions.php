<?php
//include js
function utt_subject_scripts(){
    //include subject scripts
    wp_enqueue_script( 'subjectScripts',  plugins_url('js/subjectScripts.js', __FILE__) );
    //localize subject scripts
    wp_localize_script( 'subjectScripts', 'subjectStrings', array(
        'deleteForbidden' => __( 'Delete is forbidden while completing the form!', 'UniTimetable' ),
        'deleteQuestion' => __( 'Are you sure that you want to delete this Lecture?', 'UniTimetable' ),
        'subjectDeleted' => __( 'Subject deleted successfully!', 'UniTimetable' ),
        'subjectNotDeleted' => __( 'Failed to delete Subject. Check if Subject is connected with Groups.', 'UniTimetable' ),
        'editForbidden' => __( 'Edit is forbidden while completing the form!', 'UniTimetable' ),
        'editSubject' => __( 'Edit Subject', 'UniTimetable' ),
        'cancel' => __( 'Cancel', 'UniTimetable' ),
        'nameVal' => __( 'Title field is required. Please avoid using special characters.', 'UniTimetable' ),
        'typeVal' => __( 'Please choose a Subject type.', 'UniTimetable' ),
        'semesterVal' => __( 'Please choose a semester.', 'UniTimetable' ),
        'colorVal' => __( 'Wrong color code.', 'UniTimetable' ),
        'insertSubject' => __( 'Insert Subject', 'UniTimetable' ),
        'reset' => __( 'Reset', 'UniTimetable' ),
        'failAdd' => __( 'Failed to add Subject. Check if Subject already exists.', 'UniTimetable' ),
        'successAdd' => __( 'Subject successfully added!', 'UniTimetable' ),
        'failEdit' => __( 'Failed to edit Subject. Check if Subject already exists.', 'UniTimetable' ),
        'successEdit' => __( 'Subject successfully edited!', 'UniTimetable' ),
    ));
    //include js color
    wp_enqueue_script( 'jscolor',  plugins_url('js/jscolor.js', __FILE__) );
}
//subjects page
function utt_create_subjects_page(){
    //subject form
    ?>
    <div class="wrap">
        <h2 id="subjectTitle"> <?php _e("Insert Subject","UniTimetable"); ?> </h2>
        <form action="" name="subjectForm" method="post">
            <input type="hidden" name="subjectid" id="subjectid" value=0 />
            <div class="element">
            <?php _e("Title:","UniTimetable"); ?><br/>
            <input type="text" name="subjectname" id="subjectname" class="dirty" size="40" placeholder="<?php _e("Required","UniTimetable"); ?>"/>
            </div>
            <div class="element2 firstInRow last">
            <?php _e("Type:","UniTimetable"); ?><br/>
            <select name="subjecttype" class="dirty" id="subjecttype">
                <option value="0"><?php _e("- select -","UniTimetable"); ?></option>
                <option value="T"><?php _e("Theory","UniTimetable"); ?></option>
                <option value="L"><?php _e("Lab","UniTimetable"); ?></option>
                <option value="PE"><?php _e("Practice Exercises","UniTimetable"); ?></option>
            </select>
            </div>
            <div class="element2">
            <?php _e("Semester:","UniTimetable"); ?><br/>
            <select name="semester" id="semester" class="dirty">
                <option value="0"><?php _e("- select -","UniTimetable"); ?></option>
                <?php
                for($i=1;$i<11;$i++){
                    echo "<option value=$i>$i</option>";
                }
                ?>
            </select>
            </div>
            <div class="element2">
            <?php _e("Color:","UniTimetable"); ?><br/>
            <input type="text" id="color" class="color dirty" size="10" name="color"/>
            </div>
            <div id="secondaryButtonContainer">
                <input type="submit" value="<?php _e("Submit","UniTimetable"); ?>" id="insert-updateSubject" class="button-primary"/>
                <a href='#' class='button-secondary' id="clearSubjectForm"><?php _e("Reset","UniTimetable"); ?></a>
            </div>
        </form>
    <!-- place to view messages -->
    <div id="messages"></div>
    <?php _e("Select Semester:","UniTimetable"); ?>
    <!-- semester filter -->
    <select name="semesterFilter" id="semesterFilter" onchange="viewSubjects();">
        <option value='0'><?php _e("All","UniTimetable"); ?></option>
        <?php
        
        for($i=1;$i<11;$i++){
            echo "<option value='$i'>$i</option>";
        }
        ?>
    </select>
    <!-- place to view subjects table -->
    <div id="subjectsResults">
        <?php utt_view_subjects(); ?>
    </div>
    <?php
}

//ajax response insert-update subject
add_action('wp_ajax_utt_insert_update_subject','utt_insert_update_subject');
function utt_insert_update_subject(){
    global $wpdb;
    //data
    $subjectID=$_GET['subject_id'];
    $subjectName=$_GET['subject_name'];
    $subjectType=$_GET['subject_type'];
    $semester=$_GET['semester'];
    $color=$_GET['color'];
    $subjectsTable=$wpdb->prefix."utt_subjects";
    //insert
    if($subjectID==0){
        $safeSql = $wpdb->prepare("INSERT INTO $subjectsTable (title, type, semester, color) VALUES (%s,%s,%s,%s)",$subjectName,$subjectType,$semester,$color);
        $success = $wpdb->query($safeSql);
        if($success == 1){
            //success
            echo 1;
        }else{
            //fail
            echo 0;
        }
    //edit
    }else{
        $safeSql = $wpdb->prepare("UPDATE $subjectsTable SET title=%s, type=%s, semester=%s, color=%s WHERE subjectID=%d ",$subjectName,$subjectType,$semester,$color,$subjectID);
        $success = $wpdb->query($safeSql);
        if($success == 1){
            //success
            echo 1;
        }else{
            //fail
            echo 0;
        }
    }
    die();
}

//ajax response delete subject
add_action('wp_ajax_utt_delete_subject', 'utt_delete_subject');
function utt_delete_subject(){
    global $wpdb;
    $subjectsTable=$wpdb->prefix."utt_subjects";
    $safeSql = $wpdb->prepare("DELETE FROM $subjectsTable WHERE subjectID=%d",$_GET['subject_id']);
    $success = $wpdb->query($safeSql);
    //if success is 1, delete succeeded
    echo $success;
    die();
}

//ajax response view subjects
add_action('wp_ajax_utt_view_subjects','utt_view_subjects');
function utt_view_subjects(){
    global $wpdb;
    $subjectsTable = $wpdb->prefix."utt_subjects";
    if(isset($_GET['semester'])){
        $semester = $_GET['semester'];
    }else{
        $semester = 0;   
    }
    //if not selected semester, view all subjects
    if($semester==0 || $semester==""){
        $subjects = $wpdb->get_results("SELECT * FROM $subjectsTable ORDER BY title, type");
    //view filtered subjects
    }else{
        $safeSql = $wpdb->prepare("SELECT * FROM $subjectsTable WHERE semester=%d ORDER BY title, type",$semester);
        $subjects = $wpdb->get_results($safeSql);
    }
    //show registered subjects
    ?>
        <!-- table with subjects viewed -->
        <table class="widefat bold-th">
            <thead>
                <tr>
                    <th><?php _e("Subject","UniTimetable"); ?></th>
                    <th><?php _e("Type","UniTimetable"); ?></th>
                    <th><?php _e("Semester","UniTimetable"); ?></th>
                    <th><?php _e("Color","UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e("Subject","UniTimetable"); ?></th>
                    <th><?php _e("Type","UniTimetable"); ?></th>
                    <th><?php _e("Semester","UniTimetable"); ?></th>
                    <th><?php _e("Color","UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </tfoot>
            <tbody>
    <?php
        //show grey and white records in order to be more recognizable
        $bgcolor = 1;
        foreach($subjects as $subject){
            if($bgcolor == 1){
                $addClass = "class='grey'";
                $bgcolor = 2;
            }else{
                $addClass = "class='white'";
                $bgcolor = 1;
            }
            //translate subject type
            if($subject->type == "T"){
                $type = __("T","UniTimetable");
            }else if($subject->type == "L"){
                $type = __("L","UniTimetable");
            }else{
                $type = __("PE","UniTimetable");
            }
            //a record
            echo "<tr id='$subject->subjectID' $addClass><td>$subject->title</td><td>$type</td><td>$subject->semester</td><td><span style='background-color:#$subject->color'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> $subject->color</td>
                <td><a href='#' onclick='deleteSubject($subject->subjectID);' class='deleteSubject'><img id='edit-delete-icon' src='".plugins_url('icons/delete_icon.png', __FILE__)."'/> ".__("Delete","UniTimetable")."</a>&nbsp;
                <a href='#' onclick=\"editSubject($subject->subjectID, '$subject->title', '$subject->type', $subject->semester, '$subject->color');\" class='editSubject'><img id='edit-delete-icon' src='".plugins_url('icons/edit_icon.png', __FILE__)."'/> ".__("Edit","UniTimetable")."</a></td></tr>";
        }
    ?>
            </tbody>
        </table>
    <?php
    die();
}
?>