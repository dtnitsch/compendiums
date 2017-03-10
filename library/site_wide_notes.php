<?php
########################################################################
#	Site Wide Notes
########################################################################
function site_wide_notes_ajax($path_id,$identifier) {
	$data = "z=". base64_encode(json_encode(array("path_id"=> $path_id, "identifier" => $identifier, "user_id" => $_SESSION['user']['id'])));

	$output = "
	<div stlye='border-top: 1px solid #ccc; height: 1px; width: 100%; margin: 10px 0;'>&nbsp;</div>

	<h3>Site Wide Notes</h3>
	<textarea name='site_wide_note_content' id='site_wide_note_content' class='site_wide_note_content'></textarea>
	<br><input type='button' value='Save Note' onclick='save_site_wide_note()'>

	<div id='site_wide_notes_output' class='site_wide_notes_output'>
		". site_wide_notes_list($path_id,$identifier) ."
	</div>
	";

	echo $output;	

	add_js_code(site_wide_notes_build_ajax($data));
}

function site_wide_notes_build_ajax($data) {
	ob_start();
?>
	<script type='text/javascript'>

	function save_site_wide_note() {
		var data = "<?php echo $data; ?>&apid=621ea1449472caca9ed301610dca5a84&note="+ $id("site_wide_note_content").value;
		ajax({
			url: "/ajax.php"
			,debug: true
			,data: data
			,type: "json"
			,success: function(data) {
				var info = JSON.parse(data.output)
				// if(typeof info.note != 'undefined' && info.note.length > 0) {
					output = '<div id="wide_wide_note_box" class="wide_wide_note_box">';
					output += '<span class="label">';
					output += '<?php echo $_SESSION['user']['firstname'] .' '. $_SESSION['user']['lastname']; ?>';
					output += '<br><a href="mailto:<?php echo $_SESSION['user']['email']; ?>"><?php echo $_SESSION['user']['email']; ?></a>';
					output += '<br>'+ info.datetime;
					output += '</span>';
					output += '<span class="content">';
					output += info.note;
					output += '</span>';
					output += '<div class="clear"></div>';
					output += '</div>';
					$id('site_wide_notes_output').innerHTML = output + $id('site_wide_notes_output').innerHTML;
				// }
			}
		});
	}

	function add_site_wide_note_to_list() {
		// console.log('fasdfads');
	}
	</script>
<?php
	return ob_get_clean();
}

function site_wide_notes_list($path_id,$identifier) {
	$q = "
		select
			site_wide_notes.id
			,site_wide_notes.content
			,site_wide_notes.created
			,system.users.id
			,system.users.firstname
			,system.users.lastname
			,system.users.email
		from public.site_wide_notes
		join system.users on system.users.id = site_wide_notes.user_id
		where 
			site_wide_notes.active
			and site_wide_notes.path_id = '". $path_id ."'
			and site_wide_notes.identifier = '". $identifier ."'
		order by site_wide_notes.created desc
	";
	$res = db_query($q,"Getting Site Wide Notes List");

	$output = '';
	while($row = db_fetch_row($res)) {
		$output .= '<div class="wide_wide_note_box">
			<span class="label">
				'. $row['firstname'] .' '. $row['lastname'] .'
				<br><a href="mailto:'. $row['email'] .'">'. $row['email'] .'</a>
				<br>'. date('m/d/Y g:i a',strtotime($row['created'])) .'
			</span>
			<span class="content">
				'. nl2br(base64_decode($row['content'])) .'
			</span>
			<div class="clear"></div>
		</div>';
	}

	return $output;
}
