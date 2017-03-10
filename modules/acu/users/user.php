<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
// Check mulitple security modules in one DB call
$security_check_list = ['admin_users_list','admin_users_add','admin_users_edit','admin_users_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['admin_users_list'])) { back_redirect(); }

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################

##################################################
#   Pre-Content
##################################################
add_css('pagination.css');
add_js('sortlist.new.js');

// If this page is loading from the school edit section, set this value
$info['institution_id'] = 0;
if(strpos($_SERVER['SCRIPT_URI'],'/acu/schools/edit/') !== false && !empty($_GET['id'])) {
  $info['institution_id'] = (int)$_GET['id'];
}

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
  <h2 class='users'>User List</h2>
    
    <?php
    $add_button = "";
    if(!empty($security_list['admin_users_add'])) {
        $add_button = "<button onclick='window.location.href=\"/acu/users/add/\"' class='add'>Add New User</button>";
    }

    $edit_onclick = "";
    if(!empty($security_list['admin_users_edit'])) {
        $edit_onclick = " onclick='window.location=\"/acu/users/edit/?id={{id}}\"'";
    }

    $delete_link = "";
    if(!empty($security_list['admin_users_delete'])) {
        $delete_link = '<a href="/acu/users/delete/?id={{id}}" title="Delete: {{firstname}} {{lastname}}" class="delete"></a>';
    }
?>

    <div class='right float_right buttons'>
<?php
      #<button onclick='export_csv("asl_sort_users","visible")' class='export'>Export Visible</button>
      #<button onclick='export_csv("asl_sort_users","all")' class='export'>Export All</button>
      echo $add_button;
?>
    </div>
</div>
  <div class='content_container'>
  <?php echo dump_messages(); ?>
    <fieldset class='filters'>
  <form id='form_filters_users' method='' action='' onsubmit='return false;'>

      <div class='clearfix'>

        <div class='inputs float_left'>
          <label for='firstname'><b>First Name</b></label><br>
          <input type='text' name='filters[firstname]' id='firstname' onfocus>
        </div>

        <div class='inputs float_left'>
          <label for='lastname'><b>Last Name</b></label><br>
          <input type='text' name='filters[lastname]' id='lastname'>
        </div>

        <div class='inputs float_left'>
          <label for='email'><b>Email Address</b></label><br>
          <input type='text' name='filters[email]' id='email'>
        </div>

        <div class='inputs float_left'>
          <label for='institution'><b>Institution</b></label><br>
          <input type='text' name='filters[institution]' id='institution'>
        </div>

		<div class='inputs float_left'>
			<label for='registration_type'><b>Registration Type</b></label><br>
			<select name="filters[registration_type]" id="registration_type">
				<option value=""></option>
				<option value="0">Default</option>
				<option value="1">Teacher</option>
				<option value="2">Parent</option>
				<option value="3">Club</option>
				<option value="4">After School</option>
				<option value="5">Home-School</option>
				<option value="6">Kids in Need Foundation</option>
				<option value="7">Tutor</option>

			</select>
		</div>



        <input type='hidden' name='filters[institution_ids]' id='filters_institution_ids' value='<?php echo $info['institution_id']; ?>'>


        <div class='inputs float_left'>
          <label>&nbsp;</label><br>
          <button onclick='filter_results_users()' class='filter'>Filter Results</button>
        </div>

        <div class='inputs float_right hide'>
          <label>&nbsp;</label><br>
          <button onclick='show_hide("advanced_filters")' class='more'>Show More</button>
        </div>

      </div>

      <div id='advanced_filters' class='mtb' style='display:none;'>
        <b>Columns</b><br>
        <div class='inputs float_left'>
          <label for='filters_columns_firstname'>
            <input type='checkbox' name='filters[columns][su.firstname]' id='filters_columns_firstname' value='1' checked> First Name
          </label>

          <label for='filters_columns_lastname'>
            <input type='checkbox' name='filters[columns][su.lastname]' id='filters_columns_lastname' value='1' checked> Last Name
          </label>

          <label for='filters_columns_email'>
            <input type='checkbox' name="filters[columns][su.email]" id="filters_columns_email" value='1' checked> Email
          </label>

          <label for='filters_columns_heard_about'>
            <input type='checkbox' name='filters[columns][su.marketing_source]' id='filters_columns_heard_about' value='1' checked> Heard About
          </label>

          <label for='filters_columns_institution'>
            <input type='checkbox' name='filters[columns][institution]' id='filters_columns_institution' value='1' checked> Institution
          </label>

          <label for='filters_columns_phone'>
            <input type='checkbox' name='filters[columns][phone]' id='filters_columns_phone' value='1' checked> Phone
          </label>

          <label for='filters_columns_is_superadmin'>
            <input type='checkbox' name="filters[columns][is_superadmin]" id="filters_columns_is_superadmin" value='1'> Is Superadmin
          </label>

          <label for='filters_columns_registration_type'>
            <input type='checkbox' name='filters[columns][registration_type]' id='filters_columns_registration_type' value='1' checked> Registration Type
          </label>

          <label for="filters_columns_modified">
            <input type='checkbox' name='filters[columns][modified]' id='filters_columns_modified' value='1' checked> Date Modified
          </label>

          <label for='filters_columns_created'>
            <input type='checkbox' name='filters[columns][created]' id='filters_columns_created' value='1' checked> Date Created
          </label>

          <button onclick='filter_results_users()' class='filter small'>Filter Columns</button>
        </div>
      </div>

   
    </form>
       <form id="export_csv" method="post" action="/export/csv/" style='display: none;'>
            <label>&nbsp;</label><br>
            <input type="submit" value="Export CSV">
            <input type="hidden" name="query_csv" id="query_csv" value="">
        </form>
    </fieldset>   

  <span class='show_pagination'></span>
      <table id='asl_sort_users' cellpadding='0' cellspacing='0' class='asl_sort'>
        <thead id='asl_sort_users_head'>
          <tr>
            <th id='th_id' data-col='su.id'>User ID</th>
            <th id='th_firstname' data-col='su.firstname'>First Name</th>
            <th id='th_lastname' data-col='su.lastname'>Last Name</th>
            <th id='th_email' data-col='su.email'>Email</th>
            <th id='th_marketing_source' data-col='marketing_source'>Heard About</th>
            <th id='th_institution' data-col='institution'>Institution</th>
            <th id='th_iphone' data-col='phone'>Phone</th>
            <th id='th_is_superadmin' data-col='su.is_superadmin' class='hide'>Is Superadmin</th>
            <th id='th_registration_type' data-col='prt.title'>Registration Type</th>
            <th id='th_modified' data-col='su.modified'>Date Modified</th>
            <th id='th_created' data-col='su.created'>Date Created</th>
            <th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
          </tr>
        </thead>
        <tbody style='display: none;'>
          <tr<?= $edit_onclick ?>>
            <td>{{id}}</td>
            <td>{{firstname}}</td>
            <td>{{lastname}}</td>
            <td>{{email}}</td>
            <td>{{marketing_source}}</td>
            <td>{{institution}}</td>
            <td>{{phone}}</td>
            <td class='hide'>{{is_superadmin}}</td>
            <td>{{registration_type}}</td>
            <td>{{modified}}</td>
            <td>{{created}}</td>
            <td rel='nolink' class='options'><?= $delete_link ?></td>
          </tr>
        </tbody>
    </table>
<span class='show_pagination'></span>
</div>


<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">

    asl_sort_users = sortlist().remote;
    asl_sort_users.init('/ajax.php',{
        id:'asl_sort_users'
        ,data: 'apid=02a54624a9ea0058b4ab1b96265afd84'
        ,filters: 'form_filters_users'
        ,type: "pagination"
        ,column: "su.created"
        ,direction: "desc"
        ,pre_hook: show_hide_columns_users
        ,callback: function(data) {
            $id('query_csv').value = data.query;
            show($id('export_csv'));
        }
    });


    function filter_results_users() {
        asl_sort_users.sort(asl_sort_users,true);
    }
    // True is needed to show it's a custom call
    asl_sort_users.sort(asl_sort_users,true);

    function show_hide_columns_users(x) {
        // return false;
        var checkboxes = $queryAll('input[name^="filters[columns]"]');
        var i = checkboxes.length;
        var id;

        while(i--) {
            id = checkboxes[i].name.replace('filters[columns][','');
            id = id.replace(']','');
            checkboxes[i].checked;
            // ths_length = ths.length;

            if(checkboxes[i].checked) {
                asl_sort_users.template = asl_sort_users.template.replace('<td class="hide">{{'+ id +'}}</td>','<td>{{'+ id +'}}</td>');
                removeclass('th_'+id,'hide');

            } else {
                asl_sort_users.template = asl_sort_users.template.replace('<td>{{'+ id +'}}</td>','<td class="hide">{{'+ id +'}}</td>');
                addclass('th_'+id,'hide');
            }

        }
    }
    // Onfocus
    $id("firstname").focus();

</script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################