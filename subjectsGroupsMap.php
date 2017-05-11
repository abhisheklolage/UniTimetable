<?php
//include js
function utt_subjects_groups_map_scripts(){
    //include groupScripts
    wp_enqueue_script( 'subjectsGroupsMapStrings',  plugins_url('js/subjectsGroupsMap.js', __FILE__) );
    //localize groupScripts
    wp_localize_script( 'subjectsGroupsMapStrings', 'subjectsGroupsMapStrings', array(
        'successAdd' => __( 'Subjects Groups successfully added!', 'UniTimetable' ),
        'failAdd' => __( 'Subjects Groups mapping failed!', 'UniTimetable' ),
        'mappingDeleted' => __( 'Subject Group mapping deleted!', 'UniTimetable' ),
        'mappingNotDeleted' => __( 'Subject Group mapping NOT deleted!', 'UniTimetable' ),
        'deleteRecord' => __( 'Are you sure that you want to delete this record?', 'UniTimetable' ),
    ));
}
//subjects groups page
function utt_create_subjects_groups_map_page(){
    global $wpdb;
    //group form
    ?>
    <div class="wrap">
        <h2 id="subjectGroupsTitle"> <?php _e("Insert Subject-Group Mappings","UniTimetable"); ?> </h2>
        <form action="" name="groupForm" method="post">
            <div class="element firstInRow">
            <?php _e("Select Subjects","UniTimetable"); ?><br/>
            <select multiple="multiple" id="msubjects" class="dirty" size=20 style='height: 50%;'>
                <?php
                //fill select with groups
                $subjectsTable=$wpdb->prefix."utt_subjects";
                $subjects = $wpdb->get_results("SELECT * FROM $subjectsTable;");
                //translate classroom type
                foreach($subjects as $subject){
                    echo "<option value='$subject->subjectID'>$subject->title</option>";
                }
                ?>
            </select>
            </div>
            <div class="element">
            <?php _e("Select Groups","UniTimetable"); ?><br/>
            <select multiple="multiple" id="mgroups" class="dirty" size=20 style='height: 50%;'>
                <?php
                //fill select with groups
                $groupsTable=$wpdb->prefix."utt_groups";
                $groups = $wpdb->get_results("SELECT * FROM $groupsTable;");
                //translate classroom type
                foreach($groups as $group){
                    echo "<option value='$group->groupID'>$group->groupName</option>";
                }
                ?>
            </select>
            </div>
            <div class="element firstInRow">
            <div id="secondaryButtonContainer">
                <input type="submit" value="<?php _e("Submit","UniTimetable"); ?>" id="insert-updateSubjectsGroups" class="button-primary"/>
            </div>
        </form>
        <!-- place to view messages -->
        <div id="messages">

        </div>
        <!-- filters to filter shown groups -->
        <span id="filter1">
    </span>

    <!-- place to view inserted groups -->
    <div id="subjectsGroupsResults">
        <?php utt_view_subjects_groups(); ?>
    </div>
    <?php
}
//ajax response delete subject group
add_action('wp_ajax_utt_delete_subject_group', 'utt_delete_subject_group');
function utt_delete_subject_group(){
    global $wpdb;
    $subjectsGroupsMapTable=$wpdb->prefix."utt_subjects_groups";
    //$safeSql = $wpdb->prepare("DELETE FROM $subjectsGroupsMapTable WHERE subjectID = %d AND groupID = %d;", $_GET['subject_ID'], $_GET['group_ID']);
    $safeSql = $wpdb->prepare("DELETE FROM $subjectsGroupsMapTable WHERE subjectGroupID = %d;", $_GET['subjectGroupID']);
    $success = $wpdb->query($safeSql);
    //if success is 1, delete succeeded
    echo $success;
    die();
}

//ajax response insert-update group
add_action('wp_ajax_utt_insert_update_subjects_groups','utt_insert_update_subjects_groups');
function utt_insert_update_subjects_groups(){
    global $wpdb;
    $subjectsGroupsMapTable=$wpdb->prefix."utt_subjects_groups";
    //data to be inserted/updated
    //$groupOverlaps[]=$_GET['groupOverlaps'];
    $selSubjects=$_GET['subjects'];
    $selGroups=$_GET['groups'];
    //$safeSql = $wpdb->prepare("INSERT INTO $subjectsGroupsMapTable (subjectID, groupID) VALUES (%d, %d)", $selSubjects[0], $selGroups[0]);
    //$success = $wpdb->query($safeSql);
    foreach ($selSubjects as $selectedSubject){
        foreach ($selGroups as $selectedGroup){
            $safeSql = $wpdb->prepare("INSERT INTO $subjectsGroupsMapTable (subjectID, groupID) VALUES (%d, %d)", $selectedSubject, $selectedGroup);
            $success = $wpdb->query($safeSql);
        }
    }
    echo $success;
    die();
}

//ajax response view subjects groups
add_action('wp_ajax_utt_view_subjects_groups','utt_view_subjects_groups');
function utt_view_subjects_groups(){
    global $wpdb;
    $subjectsGroupsMapTable=$wpdb->prefix."utt_subjects_groups";
    $groupsTable=$wpdb->prefix."utt_groups";
    $subjectsTable=$wpdb->prefix."utt_subjects";
    $subjectsGroups = $wpdb->get_results("SELECT * FROM $subjectsGroupsMapTable;");
    ?>
        <!-- show table of groups -->
        <table class="widefat bold-th">
            <thead>
                <tr>
                    <th><?php _e("Subject","UniTimetable"); ?></th>
                    <th><?php _e("Group","UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e("Subject","UniTimetable"); ?></th>
                    <th><?php _e("Group","UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </tfoot>
            <tbody>
    <?php
        //show grey and white records in order to be more recognizable
        $bgcolor = 1;
        foreach($subjectsGroups as $subjectGroup){
            if($bgcolor == 1){
                $addClass = "class='grey'";
                $bgcolor = 2;
            }else{
                $addClass = "class='white'";
                $bgcolor = 1;
            }
            //a record
            //echo "<tr id='$group->groupOne' $addClass><td>$group->groupOne $type</td><td>$group->groupTwo</td></tr>";

            //$subject_id=$wpdb->get_var("SELECT subjectID FROM $subjectsGroupsMapTable WHERE $subjectGroup->subjectGroupID = $subjectGroup;");
            //$group_id=$wpdb->get_var("SELECT groupID FROM $subjectsGroupsMapTable WHERE $subjectGroup->subjectGroupID = $subjectGroup;");
            //echo $subject_id."-".$group_id;
            $subname=$wpdb->get_var("SELECT title FROM $subjectsTable WHERE $subjectGroup->subjectID = subjectID;");
            $grpname=$wpdb->get_var("SELECT groupName FROM $groupsTable WHERE $subjectGroup->groupID = groupID;");
            //echo $subject_name."-".$group_name;
            //echo $subjectGroup;
            //echo $subjectGroup->subjectGroupID;
            //echo $subjectGroup->subjectID;
            echo "<tr id='subjectGroup->subjectGroupID' $addClass><td>$subname</td><td>$grpname</td><td><a href='#' onclick='deleteSubjectGroup($subjectGroup->subjectGroupID);' class='deleteSubjectGroup'><img id='edit-delete-icon' src='".plugins_url('icons/delete_icon.png', __FILE__)."'/> ".__("Delete","UniTimetable")."</a></td></tr>";
        }
    ?>
            </tbody>
        </table>
    <?php
    die();
}
?>
