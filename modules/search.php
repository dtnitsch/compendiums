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
	select
		id
	    ,title
		,modified
		,key
		,type
	    ,sum(weight) as weight
	from (
		select id,title,modified,key,'list' as type,10 as weight from public.list where title ilike '". $text ."'
			union
		select id,title,modified,key,'list' as type,9 as weight from public.list where title ilike '%". $text ."%'
			union
		select id,title,modified,key,'collection' as type,10 as weight from public.collection where title ilike '". $text ."'
			union
		select id,title,modified,key,'collection' as type,9 as weight from public.collection where title ilike '%". $text ."%'
			union

		select
			list.id
			,list.title
			,list.modified
			,list.key
			,'list' as type
			,7 as weight
		from public.list
		join public.list_asset_map on 
			list_asset_map.list_id = list.id
			and asset_id in (select id from public.asset where title ilike '". $text ."')

		union 


		select
			list.id
			,list.title
			,list.modified
			,list.key
			,'list' as type
			,6 as weight
		from public.list
		join public.list_asset_map on 
			list_asset_map.list_id = list.id
			and asset_id in (select id from public.asset where title ilike '%". $text ."%')
	) as q
	group by
		id
		,title
		,modified
		,key
		,type
	order by
	    weight desc
		,modified desc
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

<table class="tbl" cellspacing="0" cellpadding="0">
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
