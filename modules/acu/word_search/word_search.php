<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
// Check mulitple security modules in one DB call
$security_check_list = ['word_search_list','word_search_add','word_search_edit','word_search_delete'];
$security_list = has_access(implode(",",$security_check_list)); 
if(empty($security_list['word_search_list'])) { back_redirect(); }

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

##################################################
#   Content
##################################################
?>
<div class='clearfix'>
	<h2 class='word_search'>Word Search List</h2>

  <div class='right float_right buttons'>
<?php
      #<button onclick='export_csv("asl_sort","visible")' class='export'>Export Visible</button>
      #<button onclick='export_csv("asl_sort","all")' class='export'>Export All</button>
?>
		<button onclick='window.location.href="/acu/word_search/add/"' class='add'>Add New Questions</button>
	</div>
</div>
<div class='content_container'>
<?php echo dump_messages(); ?>
	<fieldset class='filters' id='filters'>
	<form id="form_filters" method="" action="" onsubmit="return false;">

		<div class='inputs float_left'>
			<label for='word'><b>Word</b></label><br>
			<input type='text' name='filters[word]' id='word'>
		</div>

		<div class='inputs float_left'>
			<label for='answer'><b>Answer</b></label><br>
			<input type='text' name='filters[answer]' id='answer'>
		</div>

		
		 <div class='inputs float_left'>
			<label for='world'><b>World</b></label><br>
<?php
	echo build_db_select(get_worlds(),"wsm.world_id", "world");
?>
		</div>
		<div class='inputs float_left'>
			<label for='generation'><b>Generation</b></label><br>
<?php
	echo build_db_select(get_generations(),"wsm.generation_id", "generation");
?>
		</div>
		<div class='inputs float_left'>
			<label for='themes'><b>Themes</b></label><br>
<?php
	echo build_db_select(get_themes(),"wsm.theme_id", "theme");
?>
		</div>
		<div class='inputs float_left'>
			<label for='sd.modified'><b>Date Modified</b></label><br>
			<input type='text' name='filters[sd.modified]' id='sd.modified'>
		</div>
		
		<div class='inputs float_left'>
			<label>&nbsp;</label><br>
    		<button onclick='filter_results()' class='filter'>Filter Results</button>
	    </div>
	</form>

	<form id="export_csv" method="post" action="/export/csv/" style='display: none;'>
		<label>&nbsp;</label><br>
		<input type="submit" value="Export CSV">
		<input type="hidden" name="query_csv" id="query_csv" value="">
	</form>
	   
	</fieldset>

	<span class='show_pagination'></span>
	<table id='asl_sort' cellpadding="0" cellspacing="0" class='asl_sort'>
		<thead>
		<tr>
			<th data-col="word">Words</th>
			<th data-col="modified" style='min-width: 150px;'>Date Modified</th>
			<th class='options' style='width: 1%;'><img src='/images/options.png' /></th>
		</tr>
		</thead>
		<tbody style='display: none;'>
		<tr onclick='window.location="/acu/word_search/edit/?id={{id}}"'>
			<td>{{word}}</td>
			<td>{{modified}}</td>
			<td rel='nolink' class='options'>
				<a href='/acu/word_search/delete/?id={{id}}' title="Delete: {{word_search}}" class='delete'></a>
			</td>
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
    asl_sort = sortlist().remote;
    asl_sort.init('/ajax.php',{
        id:'asl_sort'
        ,data: 'apid=b22468e2a337e163e70d3bfc9be562d8'
        ,filters: 'form_filters'
        ,type: "pagination"
        ,column: "word"
        ,direction: "asc"
		,callback: function(data) {
			$id('query_csv').value = data.query;
			show($id('export_csv'));
		}
    });

    function filter_results() {
        asl_sort.sort(asl_sort,true);
    }
    // True is needed to show it's a custom call
    asl_sort.sort(asl_sort,true);

	// Onfocus
    $id("word").focus();

</script>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################

function build_db_select($res,$name,$disp_name) {
	$output = '<select name="filters['. $name .']" id="'. $name .'">';
	$output .= '<option value="">-Select '. ucfirst($disp_name) .'-</option>';
	while($row = db_fetch_row($res)) {
		$output .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';
	}
	$output .= '</select>';
	return $output;
}

// get worlds
function get_worlds() {

	$q = "select id,(title || ' (' || alias || ')') as title from public.worlds where active and title != ''";
	$result = db_query($q,"Getting Worlds");

	if(db_is_error($result)) {
		return false;
	}

	return $result;
}

// get themes
function get_themes() {

	$q = "select id,(title || ' (' || alias || ')') as title from public.themes where active";
	$result = db_query($q,"Getting Themes");

	if(db_is_error($result)) {
		return false;
	}

	return $result;
}

// get generations
function get_generations() {

	$q = "select id,title from public.generations where active";
	$result = db_query($q,"Getting Generations");

	if(db_is_error($result)) {
		return false;
	}

	return $result;
}
##################################################
#   EOF
##################################################