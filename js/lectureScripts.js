//used to decline delete and edit when form is being completed
var isDirty = 0;
//date validation
function isDate(date){
   if (date=="") {
      return false;
   }
   var regexDate = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/;
   if (date.match(regexDate) == null) {
      return false;
   }
   date = date.split("/");
   var d = date[0];
   var m = date[1];
   var y = date[2];
   
   if (m<1 || m>12) {
      return false;
   }
   if (y<1000 || y>3000) {
      return false;
   }
   if (d<1 || d>31) {
      return false;
   }
   if ((m==4 || m==6 || m==9 || m==11) && d==31) {
      return false;
   }
   if (m==2) {
      var isleap = (y % 4 == 0 && (y % 100 != 0 || y % 400 == 0));
      if (d>29 || (d==29 && !isleap)) {
         return false;
      }
   }
   return y+"-"+m+"-"+d;
}
//time validation
function isTime(time) {
   if (time == 0) {
      return false;
   }
   var regexTime = /^(\d{1,2}):(\d{2})$/;
   if (time.match(regexTime) == null) {
      return false;
   }
   time = time.split(":");
   var h = time[0];
   var m = time[1];
   if (h>23 || h<0) {
      return false;
   }
   return h+":"+m+":00";
}
//delete lecture function
function deleteLecture(lectureID){
   //if form is being completed it does not let you delete
   if (isDirty==1) {
      alert(lectureStrings.deleteForbidden)
      return false;
   }
   //ask user whether he wants to delete only this record or all for this group
   jQuery("#dialog-confirm p").html(lectureStrings.deleteLecture);
   jQuery( "#dialog-confirm" ).dialog({
      resizable: false,
      height:180,
      modal: true,
      buttons: {
         "This only" : function() {
            jQuery( this ).dialog( "close" );
            var data = {
               action: 'utt_delete_lecture',
               lecture_id: lectureID,
               delete_all: 0
            };
            jQuery.get('admin-ajax.php' , data, function(data){
               jQuery('#msg').html("<div id='message' class='updated'>"+lectureStrings.lectureDeleted+"</div>");
            });
            setTimeout(loadCalendar, 500);
            return false;
         },
         "All" : function() {
            jQuery( this ).dialog( "close" );
            var data = {
               action: 'utt_delete_lecture',
               lecture_id: lectureID,
               delete_all: 1
            };
            jQuery.get('admin-ajax.php' , data, function(data){
               jQuery('#msg').html("<div id='message' class='updated'>"+lectureStrings.lecturesDeleted+"</div>");
            });
            setTimeout(loadCalendar, 500);
            return false;
         },
         "Cancel" : function() {
            jQuery( this ).dialog( "close" );
            return false;
         }
      }
    });
}
//edit lecture function
function editLecture(lectureID, periodID, semester, subjectID, groupID, teacherID, classroomID, start, end){
   //if form is being completed it does not let you edit
   if (isDirty==1) {
      alert(lectureStrings.editForbidden);
      return false;
   }
   //complete form
   jQuery('#lectureTitle').html(lectureStrings.editLecture);
   jQuery('#lectureID').val(lectureID);
   jQuery('#period').val(periodID);
   jQuery('#semester').val(semester);
   loadSubjects(subjectID);
   loadGroups(groupID,periodID,subjectID);
   jQuery('#teacher').val(teacherID);
   jQuery('#classroom').val(classroomID);
   start = start.split(" ");
   var time = start[1];
   time = time.split(":");
   time = time[0]+":"+time[1];
   var date = start[0].split("-");
   var y = date[0];
   var m = date[1];
   var d = date[2];
   date = d + "/" + m + "/" + y;
   jQuery('#date').val(date);
   jQuery('#time').val(time);
   end = end.split(" ");
   var endTime = end[1];
   endTime = endTime.split(":");
   endTime = endTime[0]+":"+endTime[1];
   jQuery('#endTime').val(endTime);
   jQuery('.weekDiv').hide();
   jQuery('#clearLectureForm').html(lectureStrings.cancel);
}
//load groups combo-box depending on period and subject. Selected parameter is used for autofill on edit
function loadGroups(selected, period, subject) {
   if (period == 0) {
      period = jQuery('#period').val();
   }
   if (subject == 0) {
      subject = jQuery('#subject').val();
   }
   //ajax data
   var data = {
      action: 'utt_load_groups',
      period: period,
      subject: subject,
      selected: selected
   };
   //ajax call
   jQuery.get('admin-ajax.php' , data, function(data){
      //load groups combo-box
      jQuery('#groups').html(data);                                                
   });
}
//load subjects when semester selected
function loadSubjects(selected){
   semester = jQuery('#semester').val();
   //ajax data
   var data = {
      action: 'utt_load_subjects',
      semester: semester,
      selected: selected
   };
   //ajax call
   jQuery.get('admin-ajax.php', data, function(data){
      //load combo-box
      jQuery('#subjects').html(data);
   });
}
//runs when filterSelect2 is changed
function filterFunction() {
   var viewType = jQuery('#filterSelect1').val();
   var viewFilter = jQuery('#filterSelect2').val();
   //ajax call
   var events = {
      url: 'admin-ajax.php',
      type: 'POST',
      data: {
         action: 'utt_json_calendar',    // an option!
         viewType: jQuery('#filterSelect1').val(),  // an option!
         viewFilter: jQuery('#filterSelect2').val()
      }
   }
   //reload json data
   jQuery('#calendar').fullCalendar('removeEventSource', events);
   jQuery('#calendar').fullCalendar('addEventSource', events);
   loadCalendar();
}
//reloads json data
function loadCalendar() {
   jQuery('#calendar').fullCalendar( 'refetchEvents' );
}

jQuery(function ($) {
    //on page load
   $(document).ready(function() {
      var lang = lectureStrings.lang;
      // page is now ready, initialize the calendar...
      $('#calendar').fullCalendar({
         lang: lang,
         eventSources: [

            // your event source
            {
               url: 'admin-ajax.php',
               type: 'POST',
               data: {
                  action: 'utt_json_calendar',    // an option!
                  viewType: $('#filterSelect1').val(),  // an option!
                  viewFilter: $('#filterSelect2').val()
               },
               error: function() {
                   alert('there was an error while fetching events!');
               }
            },
   
            // any other sources...

         ],
         loading: function (bool) { 
            if (bool)
               //show spinner when loading
               $('#loadingImg').show(); 
             else 
               $('#loadingImg').hide(); 
         },
         timeFormat: 'H:mm' ,
         eventRender: function(event, element) {
            if (event.buttons == false) {
               
            }else{
               element.find('.fc-time').before($("<a href='javascript:;' onclick='deleteLecture("+event.lectureID+");' class='deleteLecture' title='Διαγραφή Διδασκαλίας'><div class='deleteLectureDiv'></div></a><a href='#' onclick='editLecture("+event.lectureID+","+event.periodID+","+event.semester+","+event.subjectID+","+event.groupID+","+event.teacherID+","+event.classroomID+",\""+event.start2+"\",\""+event.end2+"\");' class='editLecture' title='Επεξεργασία Διδασκαλίας'><div class='editLectureDiv'></div></a><br/>"));
            }
            element.qtip({
               show: 'hover',
               hide: { when: { event: 'unfocus' } },
               content: event.title + event.descr
            });
         }
      });
   });
   //when period is changed load groups (if semester and subject are selected)
   $('#period').change(function(){
      period = $('#period').val();
      subject = $('#subject').val();
      //ajax data
      var data = {
         action: 'utt_load_groups',
         period: period,
         subject: subject
      };
      //ajax call
      $.get('admin-ajax.php' , data, function(data){
         //groups combo-box reload
         $('#groups').html(data);                                                
      });
   })
   //load datepicker to date element
   $( "#date" ).datepicker({dateFormat: 'dd/mm/yy'});
    //timespinner options
   $.widget( "ui.timespinner", $.ui.spinner, {
      options: {
         // seconds
         step: 60 * 60 * 1000,
         // hours
         page: 60
      },
 
      _parse: function( value ) {
         if ( typeof value === "string" ) {
            // already a timestamp
            if ( Number( value ) == value ) {
               return Number( value );
            }
            return +Globalize.parseDate( value );
         }
         return value;
      },
 
      _format: function( value ) {
         return Globalize.format( new Date(value), "t" );
      }
   });
   Globalize.culture("de-DE");
   //load timespinner
   $("#time").timespinner();
   $("#endTime").timespinner();
   //submit form
   $('#insert-updateLecture').click(function(){
      //submited data
      var lectureID = $('#lectureID').val();
      var period = $('#period').val();
      var subject = $('#subject').val();
      var group = $('#group').val();
      var teacher = $('#teacher').val();
      var classroom = $('#classroom').val();
      var date = $('#date').val();
      var time = $('#time').val();
      var endTime = $('#endTime').val();
      var weeks = $('#weeks').val();
      var success = 0;
      //validation
      if (period == 0) {
         alert(lectureStrings.periodVal);
         return false;
      }
      if (subject == 0) {
         alert (lectureStrings.subjectVal);
         return false;
      }
      if (group == 0) {
         alert (lectureStrings.groupVal);
         return false;
      }
      if (teacher == 0) {
         alert (lectureStrings.teacherVal);
         return false;
      }
      if (classroom == 0) {
         alert (lectureStrings.classroomVal);
         return false;
      }
      date = isDate(date);
      if (!date){
         alert (lectureStrings.dateVal);
         return false;
      }
      time = isTime(time);
      if (!time) {
         alert (lectureStrings.startTimeVal);
         return false;
      }
      endTime = isTime(endTime);
      if (!endTime) {
         alert (lectureStrings.endTimeVal);
         return false;
      }
      if (time>=endTime) {
         alert (lectureStrings.timeVal);
         return false;
      }
      //ajax data
      var data = {
         action: 'utt_insert_update_lecture',
         lectureID: lectureID,
         group: group,
         teacher: teacher,
         classroom: classroom,
         date: date,
         time: time,
         endTime: endTime,
         weeks: weeks
      };
      //ajax call
      $.get('admin-ajax.php' , data, function(data){
         success = data;
         //success
         if (success == 1) {
               //insert
               if (lectureID == 0) {
                  $('#messages').html("<div id='message' class='updated'>"+lectureStrings.successAdd+"</div>");
               //edit
               }else{
                  $('#messages').html("<div id='message' class='updated'>"+lectureStrings.successEdit+"</div>"); 
               }
               //clear form
               $('#lectureTitle').html(lectureStrings.insertLecture);
               $('#group').val(0);
               $('#classroom').val(0);
               $('#weeks').val(1);
               jQuery('.weekDiv').show();
               $('#clearLectureForm').html(lectureStrings.reset);
               setTimeout(loadCalendar, 500);
               isDirty = 0;
            //fail
            }else{
               //insert
               if (lectureID == 0) {
                  $('#messages').html("<div id='message' class='error'>"+lectureStrings.failAdd+"</div>");
               //edit
               }else{
                  $('#messages').html("<div id='message' class='error'>"+lectureStrings.failEdit+"</div>");
               }
            }
      });
      return false;
   })
   //clear form
   $('#clearLectureForm').click(function(){
      $('#lectureTitle').html(lectureStrings.insertLecture);
      $('#lectureID').val(0);
      $('#period').val(0);
      $('#semester').val(0);
      $('#subject').val(0);
      $('#group').val(0);
      $('#teacher').val(0);
      $('#classroom').val(0);
      $('#date').val("");
      $('#time').val("08:00");
      $('#endTime').val("10:00");
      $('#weeks').val(1);
      $('#clearLectureForm').html(lectureStrings.reset);
      $('#message').remove();
      isDirty = 0;
      return false;
   })
   //load new filterSelect2
   $('#filterSelect1').change(function(){
      viewType = $('#filterSelect1').val();
      //ajax data
      var data = {
         action: 'utt_load_filter',
         viewType: viewType
      };
      //ajax call
      $.get('admin-ajax.php', data, function(data){
         $('#filter2').html(data);
      });
   });
   //form is dirty
   $('.dirty').change(function(){
      isDirty = 1;
   })
    
});