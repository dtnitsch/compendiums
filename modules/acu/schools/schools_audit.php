<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__));   # Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("institutions_audit")) { back_redirect(); }

##################################################
#   Validation
##################################################
$id = get_page_id();
if(empty($id)) {
    warning_message("An error occured while trying to edit this record:  Missing Requred ID");
    safe_redirect('/acu/schools/');
}

##################################################
#   DB Queries
##################################################
$info = db_fetch("select * from public.institutions where id='". $id ."'",'Getting School');

##################################################
#   Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/schools/");

add_css('pagination.css');
add_js('sortlist.new.js');

##################################################
#   Content
##################################################
?>
    <h2 class='schools'>Audit School: <?php echo $info["title"]; ?></h2>
    
    <div class='content_container'>

    <?= school_navigation($id,"audit") ?>

    <?php echo dump_messages(); ?>

    <form id="form_filters" method="" action="" onsubmit="return false;">
    <fieldset class='filters clearfix' id='filters'>
        <div class='inputs float_left'>
            <label for='column_name'><b>Column</b></label><br>
            <input type='text' name='filters[column_name]' id='column_name'>
        </div>

        <div class='inputs float_left'>
            <label>&nbsp;</label><br>
            <button onclick='filter_results()' class='filter'>Filter Results</button>
        </div>
    </fieldset>
    </form>

    <span class='show_pagination'></span>
    <table id='asl_sort' cellpadding='0' cellspacing='0' class='asl_sort'>
        <thead id='asl_sort_head'>
        <tr>
            <th data-col="column_name">Column Name</th>
            <th data-col="full_name">Full Name</th>
            <th data-col="old_value">Old Value</th>
            <th data-col="new_value">New Value</th>
            <th data-col="created">Date Created</th>
        </tr>
        </thead>
        <tbody style='display: none;'>
        <tr onclick='window.location="/acu/schools/edit/?id={{id}}"'>
            <td>{{column_name}}</td>
            <td>{{full_name}}</td>
            <td>{{old_value}}</td>
            <td>{{new_value}}</td>
            <td>{{created}}</td>
        </tr>
        </tbody>
    </table>
    <span class='show_pagination'></span>
    
    </div>

<?php
    site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
?>


<?php
##################################################
#   Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">

    asl_sort = sortlist().remote;
    asl_sort.init('/ajax.php',{
        id:'asl_sort'
        ,data: 'apid=98250612f3259f00dfe692b99cf13389&id=<?php echo $id; ?>'
        ,filters: 'filters'
        ,type: "pagination"
    });

    function filter_results() {
        asl_sort.sort(asl_sort,true);
    }
    // True is needed to show it's a custom call
    asl_sort.sort(asl_sort,true);
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
?>