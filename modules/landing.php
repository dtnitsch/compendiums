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
<p>
	<strong>Total Lists</strong>: <?php echo $list_count['cnt']; ?>
	<br><strong>Total Collections</strong>: <?php echo $collection_count['cnt']; ?>
</p>


<div style="float: left; width: 32%; border: 1px solid #ccc; background: #fff; padding: 1em;">
	Last 10 lists created:
	<ul style="">
<?php
	$output = "";
	while($row = db_fetch_row($top_10_lists)) {
		$output .= "<li><a href='/list/". $row['key'] ."/'>". $row['title'] ."</a></li>";
	}
	echo $output;
?>
	</ul>
</div>
<div style="float: left; width: 32%; border: 1px solid #ccc; background: #fff; padding: 1em; margin-left: 3px;">
	Last 10 collections created:
	<ul style="">
<?php
	$output = "";
	while($row = db_fetch_row($top_10_collections)) {
		$output .= "<li><a href='/collection/". $row['key'] ."/'>". $row['title'] ."</a></li>";
	}
	echo $output;
?>
	</ul>
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
