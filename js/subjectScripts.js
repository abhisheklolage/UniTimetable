//used to decline delete and edit when form is being completed
var isDirty = 0;
//delete function
function deleteSubject(subjectID){
   //if form is being completed it does not let you delete
   if (isDirty==1) {
      alert(subjectStrings.deleteForbidden)
      return false;
   }
   //ajax data
   var data = {
      action: 'utt_delete_subject',
      subject_id: subjectID
   }
   //delete confirmation
   if (confirm(subjectStrings.deleteQuestion)) {
      //ajax call
      jQuery.get('admin-ajax.php' , data, function(data){
         //success
         if (data == 1) {
            //remove deleted
            jQuery('#'+subjectID).remove();
            jQuery('#messages').html("<div id='message' class='updated'>"+subjectStrings.subjectDeleted+"</div>");
         //failed
         }else{
            jQuery('#messages').html("<div id='message' class='error'>"+subjectStrings.subjectNotDeleted+"</div>");
         }
         
      });
   }
   return false;
}
//edit function
function editSubject(subjectID,title,type,semester,color) {
   //if form is being completed it does not let you delete
   if (isDirty==1) {
      alert(subjectStrings.editForbidden)
      return false;
   }
   //fill form
   document.getElementById('subjectid').value=subjectID;
   document.getElementById('subjectname').value=title;
   document.getElementById('subjecttype').value=type;
   document.getElementById('semester').value=semester;
   document.getElementById('color').value=color;
   jQuery('#color').css("background-color","#"+color);
   document.getElementById('subjectTitle').innerHTML=subjectStrings.editSubject;
   document.getElementById('clearSubjectForm').innerHTML=subjectStrings.cancel;
   jQuery('#message').remove();
   isDirty = 1;
   return false;
}
//show all or filtered subjects
function viewSubjects(){
    var semester = document.getElementById('semesterFilter').value;
    //ajax data
    var data = {
        action: 'utt_view_subjects',
        semester: semester
    }
    //ajax call
    jQuery.get('admin-ajax.php', data, function(data){
        jQuery('#subjectsResults').html(data);
    })
    return false;
}

jQuery(function ($) {
    //submit form
    $('#insert-updateSubject').click(function(){
         //data
         var subjectID = $('#subjectid').val();
         var subjectName = $('#subjectname').val();
         var subjectType = $('#subjecttype').val();
         var semester = $('#semester').val();
         var color = $('#color').val();
         var regexSubjectName = /^[α-ωΑ-ΩA-Za-zΆ-Ώά-ώ0-9\s-_\/.&]{3,64}$/;
         var regexColor = /^[0-9A-F]{6}$/;
         var success = 0;
         //validation
         if (!regexSubjectName.test(subjectName)) {
            alert(subjectStrings.nameVal);
            return false;
         }
         if (subjectType == 0) {
            alert(subjectStrings.typeVal);
            return false;
         }
         if (semester == 0) {
            alert(subjectStrings.semesterVal);
            return false;
         }
         if (!regexColor.test(color)) {
            alert(subjectStrings.colorVal);
            return false;
         }
         //ajax data
         var data = {
            action: 'utt_insert_update_subject',
            subject_id: subjectID,
            subject_name: subjectName,
            subject_type: subjectType,
            semester: semester,
            color: color
         };
         //ajax call
         $.get('admin-ajax.php' , data, function(data){
            success = data;
            //success
            if (success == 1) {
               //insert
               if (subjectID == 0) {
                  $('#messages').html("<div id='message' class='updated'>"+subjectStrings.successAdd+"</div>");
               //edit
               }else{
                  $('#messages').html("<div id='message' class='updated'>"+subjectStrings.successEdit+"</div>"); 
               }
               //clear form
               $('#subjectid').val(0);
               $('#subjecttype').val(0);
               $('#subjectTitle').html(subjectStrings.insertSubject);
               $('#clearSubjectForm').html(subjectStrings.reset);
               isDirty = 0;
            //fail
            }else{
               //insert
               if (subjectID == 0) {
                  $('#messages').html("<div id='message' class='error'>"+subjectStrings.failAdd+"</div>");
               //edit
               }else{
                  $('#messages').html("<div id='message' class='error'>"+subjectStrings.failEdit+"</div>");
               }
            }
            //ajax data
            data = {
               action: 'utt_view_subjects',
               semester: $('#semesterFilter').val()
            };
            //ajax call
            $.get('admin-ajax.php' , data, function(data){
               //show registered subjects
               $('#subjectsResults').html(data);
            });
         });
         return false;
    })
    //clear form
    $('#clearSubjectForm').click(function(){
        $('#subjectTitle').html(subjectStrings.insertSubject);
        $('#subjectid').val(0);
        $('#subjectname').val("");
        $('#subjecttype').val(0);
        $('#semester').val(0);
        $('#color').val("FFFFFF");
        $('#color').css("background-color","white");
        $('#clearSubjectForm').html(subjectStrings.reset);
        $('#message').remove();
        isDirty = 0;
        return false;
    })
    //form is dirty
    $('.dirty').change(function(){
        isDirty = 1;
    })
    
});