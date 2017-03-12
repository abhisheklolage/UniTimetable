//used to decline delete and edit when form is being completed
var isDirty = 0;
jQuery(function ($) {
    //submit form
    $('#insert-updateGroupOverlaps').click(function(){
        //data
        var groupOverlaps = $('#ogroups').val();
        //ajax data
        var data = {
            action: 'utt_insert_update_groups_overlaps',
            group_overlaps: groupOverlaps
        };
        //ajax call
        $.get('admin-ajax.php' , data, function(data){
            success = data;
            //success
            if (success == 1) {
                //insert
               $('#messages').html("<div id='message' class='updated'>"+groupOverlapStrings.successEdit+"</div>");
               isDirty = 0;
            //fail
            }else{
                //insert
                $('#messages').html("<div id='message' class='error'>"+groupOverlapStrings.failAdd+"</div>");
            }
            //ajax data
            data = {
               action: 'utt_view_groups_overlaps',

            };
            //ajax call, reload table with data from database
            $.get('admin-ajax.php' , data, function(data){
               $('#groupsOverlapResults').html(data);
            });
        });
        return false;
    })
    //form is dirty
    $('.dirty').change(function(){
        isDirty = 1;
    })
    
});