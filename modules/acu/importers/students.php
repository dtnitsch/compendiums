<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 
if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access('importers_students')) { back_redirect(); }

post_queue($module_name,'modules/acu/importers/post_files/');

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
  <h2 class='users'>Schools List</h2>

  <div class='content_container'>

    <div id="messages">
      <?php echo dump_messages(); ?>
    </div>

    <form name="editPlan" action="" method="post" enctype="multipart/form-data">


registered email address  child first name  child gender (boy or girl)  grade (k, 1-8, other) school id

      <p>To ensure a successful import, please make sure the file meets the following criteria:</p>
      <ul>
        <li>Upload a CSV (Comma Separated Values) file</li>
        <li>Each row should contain only five columns, the "Email Address", "Students First Name", "Gender (boy or girl)", "Grade (k, 1-8, other)", "School ID"</li>
        <li>Alphabetic characters should be within quotes.</li>
      </ul>

      <div style='margin-bottom: 20px;'>
        Example file to download: <a href='/assets/example_students.csv' title='Example File'>Example CSV File</a>
      </div>

      <fieldset>
        <label>File</label>
        <input type="file" name="file" id="file">
      </fieldset>

      <p><input type="submit" name="publish" class="button" value="Import" /></p>
    </form>
  </div>
</div>

<?php

##################################################
#   Javascript Functions
##################################################
/*
ob_start();
?>
<script type="text/javascript">

</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }
*/
##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################