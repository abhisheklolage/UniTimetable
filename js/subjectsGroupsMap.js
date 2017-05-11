//used to decline delete and edit when form is being completed
var isDirty = 0;
function deleteSubjectGroup(subjectGroupID) {
   if (isDirty==1) {
      alert(teacherStrings.deleteForbidden);
      return false;
   }
   //ajax data
   var data = {
      action: 'utt_delete_subject_group',
      subjectGroupID: subjectGroupID
   };
   //confirm deletion
   if (confirm(subjectsGroupsMapStrings.deleteRecord)) {
      //ajax call
      jQuery.get('admin-ajax.php' , data, function(data){
         //success
         if (data == 1) {
            jQuery('#'+subjectGroupID).remove();
            jQuery('#messages').html("<div id='message' class='updated'>"+subjectsGroupsMapStrings.mappingDeleted+"</div>");
         //fail
         }else{
            jQuery('#messages').html("<div id='message' class='error'>"+subjectsGroupsMapStrings.mappingNotDeleted+"</div>");
         }
         //ajax data
        data = {
          action: 'utt_view_subjects_groups',
        };
        //ajax call, reload table with data from database
        jQuery.get('admin-ajax.php' , data, function(data){
          jQuery('#subjectsGroupsResults').html(data);
        });
      });
   }
   return false;
}
jQuery(function ($) {
    //submit form
    $('#insert-updateSubjectsGroups').click(function(){
        //data
        var subjects = $('#msubjects').val();
        var groups = $('#mgroups').val();
        //ajax data
        var data = {
            action: 'utt_insert_update_subjects_groups',
            subjects: subjects,
            groups: groups
        };
        //ajax call
        $.get('admin-ajax.php' , data, function(data){
            success = data;
            //success
            if (success == 1) {
                //insert
               $('#messages').html("<div id='message' class='updated'>"+subjectsGroupsMapStrings.successAdd+"</div>");
               isDirty = 0;
            //fail
            }else{
                //insert
                $('#messages').html("<div id='message' class='error'>"+subjectGroupsMapStrings.failAdd+"</div>");
            }
            //ajax data
            data = {
               action: 'utt_view_subjects_groups'
            };
            //ajax call, reload table with data from database
            $.get('admin-ajax.php' , data, function(data){
               $('#subjectsGroupsResults').html(data);
            });
        });
        return false;
    })
    //form is dirty
    $('.dirty').change(function(){
        isDirty = 1;
    })

});
