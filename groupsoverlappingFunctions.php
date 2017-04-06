<?php
//include js
function utt_group_overlaps_scripts(){
    //include groupScripts
    wp_enqueue_script( 'groupOverlapStrings',  plugins_url('js/groupsoverlappingScripts.js', __FILE__) );
    //localize groupScripts
    wp_localize_script( 'groupOverlapStrings', 'groupOverlapStrings', array(
        'successEdit' => __( 'Group successfully overlapped!', 'UniTimetable' ),
        'failAdd' => __( 'Group overlapping failed!', 'UniTimetable' ),
        'overlapDeleted' => __( 'Group overlap deleted!', 'UniTimetable' ),
        'overlapNotDeleted' => __( 'Group overlap NOT deleted!', 'UniTimetable' ),
        'deleteRecord' => __( 'Are you sure that you want to delete this record?', 'UniTimetable' ),
    ));
}
//groups page
function utt_create_groups_overlaps_page(){
    global $wpdb;
    //group form
    ?>
    <div class="wrap">
        <h2 id="groupOverlapTitle"> <?php _e("Insert Group Overlapping","UniTimetable"); ?> </h2>
        <form action="" name="groupForm" method="post">
            <div class="element">
            <?php _e("Select Groups for Overlap","UniTimetable"); ?><br/>
            <select multiple="multiple" id="ogroups" class="dirty" size=20 style='height: 50%;'>
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
            <div id="secondaryButtonContainer">
                <input type="submit" value="<?php _e("Submit","UniTimetable"); ?>" id="insert-updateGroupOverlaps" class="button-primary"/>
            </div>
        </form>
        <!-- place to view messages -->
        <div id="messages">

        </div>
        <!-- filters to filter shown groups -->
        <span id="filter1">
    </span>

    <!-- place to view inserted groups -->
    <div id="groupsOverlapResults">
        <?php utt_view_groups_overlaps(); ?>
    </div>
    <?php
}
//ajax response delete overlap
add_action('wp_ajax_utt_delete_overlap', 'utt_delete_overlap');
function utt_delete_overlap(){
    global $wpdb;
    $overlapTable=$wpdb->prefix."utt_overlap";
    $safeSql = $wpdb->prepare("DELETE FROM $overlapTable WHERE groupOne = %d AND groupTwo = %d;", $_GET['g1_ID'], $_GET['g2_ID']);
    $success = $wpdb->query($safeSql);
    //if success is 1, delete succeeded
    echo $success;
    die();
}

//ajax response insert-update group
add_action('wp_ajax_utt_insert_update_groups_overlaps','utt_insert_update_groups_overlaps');
function utt_insert_update_groups_overlaps(){
    global $wpdb;
    $groupsTMPTable=$wpdb->prefix."utt_tmp";
    $groupOverlapTable=$wpdb->prefix."utt_overlap";
    //data to be inserted/updated
    //$groupOverlaps[]=$_GET['groupOverlaps'];
    foreach ($_GET['group_overlaps'] as $selectedOption){
        $safeSql = $wpdb->prepare("INSERT INTO $groupsTMPTable (grID) VALUES (%d)",$selectedOption);
        $success = $wpdb->query($safeSql);
    }
    // inserting overlaps for current selection only
    $allPairs = $wpdb->get_results("SELECT A.grID as valOne, B.grID as valTwo FROM $groupsTMPTable AS A, $groupsTMPTable AS B WHERE A.grID != B.grID");
    foreach($allPairs as $pair){
        $safeSql = $wpdb->prepare("INSERT INTO $groupOverlapTable (groupOne, groupTwo) VALUES ($pair->valOne, $pair->valTwo);");
        $success = $wpdb->query($safeSql);
    }
    // truncate the GroupTMPTable
    $safeSql = $wpdb->prepare("TRUNCATE TABLE $groupsTMPTable;");
    $success = $wpdb->query($safeSql);
    echo $success;
    die();
}

//ajax response view groups overlaps
add_action('wp_ajax_utt_view_groups_overlaps','utt_view_groups_overlaps');
function utt_view_groups_overlaps(){
    global $wpdb;
    $overlapTable=$wpdb->prefix."utt_overlap";
    $groupsTable=$wpdb->prefix."utt_groups";
    $groupsOverlaps = $wpdb->get_results("SELECT * FROM $overlapTable;");
    ?>
        <!-- show table of groups -->
        <table class="widefat bold-th">
            <thead>
                <tr>
                    <th><?php _e("Group","UniTimetable"); ?></th>
                    <th><?php _e("Can be overlapped with","UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e("Group","UniTimetable"); ?></th>
                    <th><?php _e("Can be overlapped with","UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </tfoot>
            <tbody>
    <?php
        //show grey and white records in order to be more recognizable
        $bgcolor = 1;
        foreach($groupsOverlaps as $group){
            if($bgcolor == 1){
                $addClass = "class='grey'";
                $bgcolor = 2;
            }else{
                $addClass = "class='white'";
                $bgcolor = 1;
            }
            //a record
            $g1name=$wpdb->get_var("SELECT groupName FROM $groupsTable WHERE $group->groupOne = groupID;");
            $g2name=$wpdb->get_var("SELECT groupName FROM $groupsTable WHERE $group->groupTwo = groupID;");
            //echo "<tr id='$group->groupOne' $addClass><td>$group->groupOne $type</td><td>$group->groupTwo</td></tr>";
            echo "<tr id='$group->groupOne' $addClass><td>$g1name $type</td><td>$g2name</td><td><a href='#' onclick='deleteOverlap($group->groupOne, $group->groupTwo);' class='deleteOverlap'><img id='edit-delete-icon' src='".plugins_url('icons/delete_icon.png', __FILE__)."'/> ".__("Delete","UniTimetable")."</a></td></tr>";
        }
    ?>
            </tbody>
        </table>
    <?php
    die();
}
?>
