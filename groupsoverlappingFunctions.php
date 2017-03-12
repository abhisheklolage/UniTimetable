<?php
//include js
function utt_group_overlaps_scripts(){
    //include groupScripts
    wp_enqueue_script( 'groupOverlapStrings',  plugins_url('js/groupsoverlappingScripts.js', __FILE__) );
    //localize groupScripts
    wp_localize_script( 'groupOverlapStrings', 'groupOverlapStrings', array(
        'successEdit' => __( 'Group successfully overlapped!', 'UniTimetable' ),
        'failAdd' => __( 'Group overlapping failed!', 'UniTimetable' ),
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
            <select multiple="multiple" id="ogroups" class="dirty">
                <?php
                //fill select with groups
                $groupsTable=$wpdb->prefix."utt_groups";
                $groups = $wpdb->get_results("SELECT * FROM $groupsTable;");
                echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
                //translate classroom type
                foreach($groups as $group){
                    echo "<option value='$group->groupName'>$group->groupName</option>";
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

//ajax response insert-update group
add_action('wp_ajax_utt_insert_update_groups_overlaps','utt_insert_update_groups_overlaps');
function utt_insert_update_groups_overlaps(){
    global $wpdb;
    $groupsTMPTable=$wpdb->prefix."utt_tmp";
    //data to be inserted/updated
    //$groupOverlaps[]=$_GET['groupOverlaps'];
    foreach ($_GET['group_overlaps'] as $selectedOption){
        $safeSql = $wpdb->prepare("INSERT INTO $groupsTMPTable (groupName) VALUES (%s)",$selectedOption);
        $success = $wpdb->query($safeSql);
    }
    echo $success;
    die();
}

//ajax response view groups overlaps
add_action('wp_ajax_utt_view_groups_overlaps','utt_view_groups_overlaps');
function utt_view_groups_overlaps(){
    global $wpdb;
    $overlapTable=$wpdb->prefix."utt_overlap";
    $groupsOverlaps = $wpdb->get_results("SELECT * FROM $overlapTable;");
    ?>
        <!-- show table of groups -->
        <table class="widefat bold-th">
            <thead>
                <tr>
                    <th><?php _e("Group","UniTimetable"); ?></th>
                    <th><?php _e("Can be overlapped with","UniTimetable"); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e("Group","UniTimetable"); ?></th>
                    <th><?php _e("Can be overlapped with","UniTimetable"); ?></th>
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
            echo "<tr id='$group->groupOne' $addClass><td>$group->groupOne $type</td><td>$group->groupTwo</td></tr>";
        }
    ?>
            </tbody>
        </table>
    <?php
    die();
}
?>