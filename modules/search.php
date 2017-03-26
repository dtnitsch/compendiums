<?php
##################################################
#   Document Setup and Security
##################################################
// if(!logged_in()) { safe_redirect("/login/"); }
// if(!has_access("view_dashboard")) { safe_redirect("/"); }

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################
$text = str_replace(["'",'"',";"],"",trim($_GET['search']));
$text = db_prep_sql($text);
$text = str_replace(" ","%",$text);

$q = "
	select title,modified,key,'list' as type from public.list where title ilike '%". $text ."%'
	union
	select title,modified,key,'collection' as type from public.collection where title ilike '%". $text ."%'
	-- union
	-- select title,modified,key,'compendium' as type from public.collection where title ilike '%". $text ."%'
	order by
		modified desc
";
$res = db_query($q,"Doing Search");



##################################################
#   Pre-Content
##################################################

##################################################
#   Content
##################################################
?>

<h2 class='home'>Search Results</h2>

<table class="list_table">
<?php
	$output = '';
	while($row = db_fetch_row($res)) {
		$output .= "
			<tr onclick='window.location.href=\"/". $row['type'] ."/". $row['key'] ."/\"'>
				<td>". $row['type'] ."</td>
				<td>". $row['title'] ."</td>
				<td>". $row['modified'] ."</td>
			</tr>
		";
	}
	if($output == "") {
		$output = "<em>No search results found for</em>: <strong>". $text ."</strong>";
	}
	echo $output;
?>
</table>
</ol>

<?php
##################################################
#   Javascript Functions
##################################################

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################
