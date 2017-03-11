<?php 
##################################################
#   Document Setup and Security
##################################################

##################################################
#   Validation
##################################################

##################################################
#   DB Queries
##################################################
$q = "select count(id) as cnt from public.list where active";
$list_count = db_fetch($q,"Getting List");

$q = "select count(id) as cnt from public.collection where active";
$collection_count = db_fetch($q,"Getting Collection");

$q = "select title,key from public.list where active order by id desc limit 10 ";
$top_10_lists = db_query($q,"Getting Top 10 Lists");

$q = "select title,key from public.collection where active order by id desc limit 10 ";
$top_10_collections = db_query($q,"Getting Top 10 Collections");

##################################################
#   Pre-Content
##################################################

##################################################
#   Content
##################################################
?>
<p>Total Lists: <?php echo $list_count['cnt']; ?></p>
<div style="float: left; width: 32%">
	Last 10 lists created:
	<br>
<?php
	$output = "";
	while($row = db_fetch_row($top_10_lists)) {
		$output .= "<br><a href='/list/". $row['key'] ."/'>". $row['title'] ."</a>";
	}
	echo $output;
?>
</div>
<div style="float: left; width: 32%">
	Last 10 collections created:
	<br>
<?php
	$output = "";
	while($row = db_fetch_row($top_10_collections)) {
		$output .= "<br><a href='/collection/". $row['key'] ."/'>". $row['title'] ."</a>";
	}
	echo $output;
?>
</div>

<div class="clear"></div>

<?php
##################################################
#   Javascript Functions
##################################################
// ob_start();
// ?><?php
// $js = trim(ob_get_clean());
// if(!empty($js)) { add_js_code($js); }

##################################################
#   Additional PHP Functions
##################################################

##################################################
#   EOF
##################################################
