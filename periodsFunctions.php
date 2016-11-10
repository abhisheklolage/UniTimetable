<?php
//include js
function utt_period_scripts(){
    //load periodScripts.js file
    wp_enqueue_script( 'periodScripts',  plugins_url('js/periodScripts.js', __FILE__) );
    //localize period strings
    wp_localize_script( 'periodScripts', 'periodStrings', array(
        'deleteForbidden' => __( 'Delete is forbidden while completing the form!', 'UniTimetable' ),
        'deletePeriod' => __( 'Are you sure that you want to delete this period?', 'UniTimetable' ),
        'periodDeleted' => __( 'Period deleted successfully!', 'UniTimetable' ),
        'periodNotDeleted' => __( 'Failed to delete Period. Check if Period is connected with Groups.', 'UniTimetable' ),
        'editForbidden' => __( 'Edit is forbidden while completing the form!', 'UniTimetable' ),
        'editPeriod' => __( 'Edit Period', 'UniTimetable' ),
        'cancel' => __( 'Cancel', 'UniTimetable' ),
        'yearVal' => __( 'Year field is out of limits.', 'UniTimetable' ),
        'semesterVal' => __( 'Please choose a semester.', 'UniTimetable' ),
        'insertPeriod' => __( 'Insert Period', 'UniTimetable' ),
        'reset' => __( 'Reset', 'UniTimetable' ),
        'failAdd' => __( 'Failed to add Period. Check if Period already exists.', 'UniTimetable' ),
        'successAdd' => __( 'Period successfully added!', 'UniTimetable' ),
        'failEdit' => __( 'Failed to edit Period. Check if Period already exists.', 'UniTimetable' ),
        'successEdit' => __( 'Period successfully edited!', 'UniTimetable' ),
    ));
}
//periods page
function utt_create_periods_page(){
    //period form
    ?>
    <div class="wrap">
        <h2 id="periodTitle"> <?php _e("Insert Period","UniTimetable"); ?> </h2>
        <form action="" name="periodForm" method="post">
            <input type="hidden" name="periodid" id="periodid" value=0 />
            <?php _e("Year:","UniTimetable"); ?><br/>
            <input type="number" name="year" id="year" class="dirty" value="<?php echo date("Y"); ?>"/>
            <br/>
            <?php _e("Semester:","UniTimetable"); ?><br/>
            <select name="semester" class="dirty" id="semester">
                <option value=0><?php _e("- select -","UniTimetable"); ?></option>
                <option value="W"><?php _e("Winter","UniTimetable"); ?></option>
                <option value="S"><?php _e("Spring","UniTimetable"); ?></option>
            </select>
            <br/>
            <div id="secondaryButtonContainer">
                <input type="submit" value="<?php _e("Submit","UniTimetable"); ?>" id="insert-updatePeriod" class="button-primary"/>
                <a href='#' class='button-secondary' id="clearPeriodForm"><?php _e("Reset","UniTimetable"); ?></a>
            </div>
        </form>
    <!-- place to show messages -->
    <div id="messages"></div>
    <!-- place to show results table -->
    <div id="periodsResults">
        <?php utt_view_periods(); ?>
    </div>
    </div>
    <?php
}
//show registered periods
add_action('wp_ajax_utt_view_periods', 'utt_view_periods');
function utt_view_periods(){
    global $wpdb;
    $periodsTable=$wpdb->prefix."utt_periods";
    $periods = $wpdb->get_results( "SELECT * FROM $periodsTable ORDER BY year DESC");
    ?>
    <!-- show table with periods -->
    <table class="widefat bold-th">
            <thead>
                <tr>
                    <th><?php _e("Year","UniTimetable"); ?></th>
                    <th><?php _e("Semester","UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e("Year","UniTimetable"); ?></th>
                    <th><?php _e("Semester","UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </tfoot>
            <tbody>
    <?php
        //show grey and white records in order to be more recognizable
        $bgcolor = 1;
        foreach($periods as $period){
            if($bgcolor == 1){
                $addClass = "class='grey'";
                $bgcolor = 2;
            }else{
                $addClass = "class='white'";
                $bgcolor = 1;
            }
            if($period->semester == "S"){
                $semester = __("S","UniTimetable");
            }else{
                $semester = __("W","UniTimetable");
            }
            //a record
            echo "<tr id='$period->periodID' $addClass><td>$period->year</td><td>$semester</td>
            <td><a href='#' onclick='deletePeriod($period->periodID);' class='deletePeriod'><img id='edit-delete-icon' src='".plugins_url('icons/delete_icon.png', __FILE__)."'/> ". __("Delete","UniTimetable") ."</a>&nbsp;
            <a href='#' onclick=\"editPeriod($period->periodID,'$period->semester',$period->year);\" class='editPeriod'><img id='edit-delete-icon' src='".plugins_url('icons/edit_icon.png', __FILE__)."'/> ". __("Edit","UniTimetable") ."</a></td></tr>";
        }
    ?>
            </tbody>
        </table><?php
        die();
}

//ajax response insert-update period
add_action('wp_ajax_utt_insert_update_period','utt_insert_update_period');
function utt_insert_update_period(){
    global $wpdb;
    //data
    $year=$_GET['year'];
    $semester=$_GET['semester'];
    $periodid=$_GET['period_id'];
    $periodsTable=$wpdb->prefix."utt_periods";
    //is insert
    if($periodid==0){
        $safeSql = $wpdb->prepare("INSERT INTO $periodsTable (year, semester) VALUES (%s,%s)",$year,$semester);
        $success = $wpdb->query($safeSql);
        if($success == 1){
            //success
            echo 1;
        }else{
            //fail
            echo 0;
        }
    //is edit
    }else{
        $safeSql = $wpdb->prepare("UPDATE $periodsTable SET year=%s, semester=%s WHERE periodID=%d ",$year,$semester,$periodid);
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

//ajax response delete period
add_action('wp_ajax_utt_delete_period', 'utt_delete_period');
function utt_delete_period(){
    global $wpdb;
    $periodsTable=$wpdb->prefix."utt_periods";
    $safeSql = $wpdb->prepare("DELETE FROM $periodsTable WHERE periodID=%d",$_GET['period_id']);
    $success = $wpdb->query($safeSql);
    //if success is 1 then delete succeeded
    echo $success;
    die();
}
?>