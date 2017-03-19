//used to decline delete and edit when form is being completed
var isDirty = 0;
function deleteOverlap(g1ID, g2ID) {
   if (isDirty==1) {
      alert(teacherStrings.deleteForbidden);
      return false;
   }
   //ajax data
   var data = {
      action: 'utt_delete_overlap',
      g1_ID: g1ID,
      g2_ID: g2ID
   };
   //confirm deletion
   if (confirm(groupOverlapStrings.deleteRecord)) {
      //ajax call
      jQuery.get('admin-ajax.php' , data, function(data){
         //success
         if (data == 1) {
            jQuery('#'+g1ID, '#'+g2ID).remove();
            jQuery('#messages').html("<div id='message' class='updated'>"+groupOverlapStrings.overlapDeleted+"</div>");
         //fail
         }else{
            jQuery('#messages').html("<div id='message' class='error'>"+groupOverlapStrings.overlapNotDeleted+"</div>");
         }
         //ajax data
        data = {
          action: 'utt_view_groups_overlaps',
        };
        //ajax call, reload table with data from database
        jQuery.get('admin-ajax.php' , data, function(data){
          jQuery('#groupsOverlapResults').html(data);
        });
      });
   }
   return false;
}
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
