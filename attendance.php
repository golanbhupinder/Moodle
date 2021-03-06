<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once('../../config.php');


//$semid = $_POST["sem_id"]; 
$PAGE->set_pagelayout('standard');
$table = new html_table;


$table->head = array(
    'COURSE ID', 'COURSE NAME','TERM','START TIME','END TIME','PRESENTS','ABSENTS','PERCENTAGE'
);
$table->headspan = array(1, 1, 1, 1,1,1,1,1);
$table->data = array();


global $USER, $SESSION;

//username
  $users = $DB->get_records_sql("
  SELECT 
	STUDENT_ID
      
FROM 
    Buser_mapping
WHERE 
      MOODLE_USER = ?", array($USER->username));

	  
foreach ($users as $user) {
		$usrname = $user->student_id;
		}


$report = $DB->get_records_sql("select FIRST_NAME,MIDDLE_NAME,LAST_NAME,GENDER,FATHER_NAME from Bstudent where STUDENT_ID=?",array($usrname));		

foreach ($report as $rpdata) {
$firstname= $rpdata->first_name;
$midname= $rpdata->middle_name;
$lastname= $rpdata->last_name;
$fathername= $rpdata->gender;
$gender= $rpdata->father_name;
}

$sql = "
SELECT  BE.COURSE_ID,
        BE.COURSE_NAME,
        BE.TERM,
        BR.START_DATE,
        BR.END_DATE,
        BA.PRESENTS,
        BA.ABSENTS,
        BA.PERCENTAGE
FROM
      BATTENDANCE BA,
      BCOURSE BE,
      BSEMESTER BR,
      BTIMETABLE BT
      
      
WHERE   
        BA.CLASS_NO=BT.CLASS_NO       
AND     BT.COURSE_ID=BE.COURSE_ID
AND     BR.TERM=BT.TERM
AND     BA.STUDENT_ID=?
";

$rs = $DB->get_recordset_sql($sql,array($usrname));


foreach ($rs as $backuprow) {

    // Cache the course context
    context_instance_preload($backuprow);


    // Create the row and add it to the table


    $cells = array(
        $backuprow->course_id,
		$backuprow->course_name,
		$backuprow->term,
        $backuprow->start_date,
        $backuprow->end_date,
        $backuprow->presents,
        $backuprow->absents,
        $backuprow->percentage
        	
    );
    $table->data[] = new html_table_row($cells);
}
$rs->close();

// Check if we have any results and if not add a no records notification

if (empty($table->data)) {
    $cell = new html_table_cell($OUTPUT->notification("No Attendance yet!"));
    $cell->colspan = 8;
    $table->data[] = new html_table_row(array($cell));
}

// Display the Attendance table
echo $OUTPUT->header();
echo '<a href="attendance.php" onClick="printpage();return false"><img src="print.jpg" align="right"></a>';
echo $OUTPUT->heading('<style type="text/css">
#st
{
color:red;
text-align:center;

} 
</style><div id="st">Attendance </div>
<script type="text/javascript">
function printpage()
  {
  var printContent = document.getElementById("t1");
         

 var windowUrl = "about:blank";
 var num;

var uniqueName = new Date();
var windowName = "Print" + uniqueName.getTime();var printWindow = window.open(num, windowName, "left=50000,top=50000,width=0,height=0");

printWindow.document.write("<div style=\"position:relative; left:20px; top:50px\"><br/><br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"logo.png\" align=\"center\"/><font size=\"25\" face=\"arial\" color=\"black\">Riphah International University</font><br/><br/><br/>"+"<hr/><font size=\"8\" face=\"arial\" color=\"black\">Attendance Report</font><br/><br/>"+
printContent.innerHTML+"</div><br/><br/>");

printWindow.document.close();

printWindow.focus();

printWindow.print();

printWindow.close();
  //window.print()
  }
</script>

');


echo '<div id="t1" >';
echo $OUTPUT->box_start();
echo html_writer::table($table);
echo $OUTPUT->box_end();
echo '</div>';
echo $OUTPUT->footer();