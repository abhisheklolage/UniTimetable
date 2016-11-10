//used to decline delete and edit when form is being completed
var isDirty = 0;
//delete period function
function deletePeriod(periodID){
   //if form is being completed it does not let you delete
   if (isDirty==1) {
      alert(periodStrings.deleteForbidden);
      return false;
   }
   //ajax data
   var data = {
      action: 'utt_delete_period',
      period_id: periodID
   }
   //confirm deletion
   if (confirm(periodStrings.deletePeriod)) {
      //ajax call
      jQuery.get('admin-ajax.php' , data, function(data){
         //success
         if (data == 1) {
            jQuery('#'+periodID).remove();
            jQuery('#messages').html("<div id='message' class='updated'>"+periodStrings.periodDeleted+"</div>");
         //fail
         }else{
            jQuery('#messages').html("<div id='message' class='error'>"+periodStrings.periodNotDeleted+"</div>");
         }
         
      });
   }
   return false;
}
//edit function
function editPeriod(periodID,semester,year) {
   //if form is being completed it does not let you edit
   if (isDirty==1) {
      alert(periodStrings.editForbidden)
      return false;
   }
   //fill form
   document.getElementById('semester').value=semester;
   document.getElementById('year').value=year;
   document.getElementById('periodid').value=periodID;
   document.getElementById('periodTitle').innerHTML=periodStrings.editPeriod;
   document.getElementById('clearPeriodForm').innerHTML=periodStrings.cancel;
   jQuery('#message').remove();
   isDirty = 1;
   return false;
}

jQuery(function ($) {
    //submit form
    $('#insert-updatePeriod').click(function(){
      //data
        var periodID = $('#periodid').val();
        var year = $('#year').val();
        var semester = $('#semester').val();
        var success = 0;
        //validation
        if (year < 1900 || year > 3000) {
            alert(periodStrings.yearVal);
            return false;
        }
        if (semester==0) {
            alert (periodStrings.semesterVal);
            return false;
        }
        //ajax data
        var data = {
            action: 'utt_insert_update_period',
            period_id: periodID,
            year: year,
            semester: semester
        };
        //ajax call
        $.get('admin-ajax.php' , data, function(data){
            success = data;
            //success
            if (success == 1) {
               //insert
               if (periodID == 0) {
                  $('#messages').html("<div id='message' class='updated'>"+periodStrings.successAdd+"</div>");
               //edit
               }else{
                  $('#messages').html("<div id='message' class='updated'>"+periodStrings.successEdit+"</div>"); 
               }
               //clear form
               $('#periodid').val(0);
               $('#year').val(new Date().getFullYear());
               $('#semester').val("0");
               $('#periodTitle').html(periodStrings.insertPeriod);
               $('#clearPeriodForm').html(periodStrings.reset);
               isDirty = 0;
            //fail
            }else{
               //insert
               if (periodID == 0) {
                  $('#messages').html("<div id='message' class='error'>"+periodStrings.failAdd+"</div>");
               //delete
               }else{
                  $('#messages').html("<div id='message' class='error'>"+periodStrings.failEdit+"</div>");
               }
            }
            //ajax data
            data = {
               action: 'utt_view_periods'
            };
            //ajax call
            $.get('admin-ajax.php' , data, function(data){
               //load new data
               $('#periodsResults').html(data);
            });
        });
        return false;
    })
    //clear form
    $('#clearPeriodForm').click(function(){
        $('#periodTitle').html(periodStrings.insertPeriod);
        $('#year').val(new Date().getFullYear());
        $('#semester').val("0");
        $('#periodid').val(0);
        $('#clearPeriodForm').html(periodStrings.reset);
        $('#message').remove();
        isDirty = 0;
        return false;
    })
    //form is dirty
    $('.dirty').change(function(){
      isDirty = 1;
    })
    
});