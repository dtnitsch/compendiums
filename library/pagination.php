<?php
##################################################
#	Pagination
##################################################
/*
	# Include File
	include("/library/pagination.php");
	
	# Pagi Setup
	#   This is what sets up the pagination for this page.  3 values can be supplied, only 1 is required:
	#   (Required) Pagination Name.  This is the name that will show up in the session for the page
	#   (Optional)  Results on page.  This is the number of results to show on the page itself.  Default = 10
	#   (Optional)  Number of Display buttons.  This should be an odd number for symmetrical purposes. Default = 7 (3 on each side of current page)
	pagination_setup('front_scholarships',3,5);
	
	# The query.
	#   This is a normal database_query, just named a little differently.  It actually calls the database_query function inside, so you can send all normal values as it it was a database_query
	$results = pagination_query($q,"Getting scholarships");
	
	
	# Pagination Output
	#    Output of the actual pagination.  1 optional value can be supplied
	#    (Optional) "detailed".  If "detailed" is added in, it will show the "Results 141 - 148 of 148" part along with the normal pagination
	show_pagination();
*/

	# Set up the pagination
	function pagination_setup($name,$ipp='50',$nav_show = '7',$pretty_url=0) {
		
		add_css('pagination.css',100); 

		if(!isset($_SESSION[$name])) {
			$_SESSION[$name]['ipp'] = $ipp; 			# Items per page
			$_SESSION[$name]['cp'] = 1; 			# Current Page
			$_SESSION[$name]['num_pages_to_show'] = (($nav_show % 2) == 0 ? ($nav_show + 1) : $nav_show); 	# Pages to Show in Nav
			$_SESSION[$name]['pretty_urls'] = $pretty_url;
		}
		$GLOBALS['pagination']['name'] = $name;
		pagination_updates($name,$ipp,$nav_show,$pretty_url);
	}

	# check if the number of items per page changed
	function pagination_updates($pagi_name,$ipp,$nav_show,$pretty_url) {
		if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
			if(isset($_GET['cp']) && is_numeric($_GET['cp']) && $_GET['cp'] > 0) {
				$_SESSION[$pagi_name]['cp'] = $_GET['cp'];
			}
		} else {
			$current_path = strtolower($GLOBALS['path']['path']);
			$path_array = split("/",strtolower($current_path));
			$path_depth = count($path_array);

			if($path_array[$path_depth - 3] == 'page' && is_numeric($path_array[$path_depth - 2])) {
				$_SESSION[$pagi_name]['cp'] = $path_array[$path_depth - 2];
				unset($path_array[$path_depth - 1]);
				unset($path_array[$path_depth - 2]);
				$GLOBALS[$pagi_name]['clean_url'] = implode('/',$path_array) . '/';
			} else {
				$_SESSION[$pagi_name]['cp'] = 1;
				$GLOBALS[$pagi_name]['clean_url'] = implode('/',$path_array) . 'page/';
			}
			
			
		}
		if(isset($_GET['ipp']) && is_numeric($_GET['ipp']) && $_GET['ipp'] > 0) {
			$_SESSION[$pagi_name]['ipp'] = $_GET['ipp'];
		}
		if(isset($_SESSION[$pagi_name]['ipp']) && ($_SESSION[$pagi_name]['ipp'] != $ipp)) {
			$_SESSION[$pagi_name]['ipp'] = $ipp;
		}
		if(isset($_SESSION[$pagi_name]['num_pages_to_show']) && ($_SESSION[$pagi_name]['num_pages_to_show'] != $nav_show)) {
			$_SESSION[$pagi_name]['num_pages_to_show'] = $nav_show;
		}
	}
	
	function show_pagination($type="",$force_display=false) {
		_error_debug('Show Pagination', __FUNCTION__);

		$pagi_name = $GLOBALS['pagination']['name'];
		if(($_SESSION[$pagi_name]['ipp'] > $_SESSION[$pagi_name]['max_results']) && !$force_display) { return false; }

		$max_page = ceil($_SESSION[$pagi_name]['max_results'] / $_SESSION[$pagi_name]['ipp']);
		$output = "";
		
		if(strtolower($type) == 'detailed') {
			$current_num = (($_SESSION[$pagi_name]['cp'] - 1) * $_SESSION[$pagi_name]['ipp']);
			$max_num = (($current_num + $_SESSION[$pagi_name]['ipp']) > $_SESSION[$pagi_name]['max_results'] ? $_SESSION[$pagi_name]['max_results'] : ($current_num + $_SESSION[$pagi_name]['ipp']));
			$output = "<div style='float:left;'></div><div class='pagi_details'>Results  <span class='pagi_nums'>". ($current_num + 1) ."</span> - <span class='pagi_nums'>". $max_num ."</span> of <span class='pagi_nums'>". number_format($_SESSION[$pagi_name]['max_results'], 0) ."</span></div>";
		}

		$url = "";
		foreach($_GET as $k => $v) {
			if($k != 'cp' && $k != 'ipp') {
				$url .= "&$k=$v";
			}
		}

		$output .= "<div class='pagi'>";
		
		if($max_page <= $_SESSION[$pagi_name]['num_pages_to_show']) {
			if($_SESSION[$pagi_name]['cp'] == 1) {
				$output .= "<div class='pagi_direction_disabled'>Newer</div> ";
			} else {
				if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
					$output .= "<div class='pagi_direction'><a href='?cp=". ($_SESSION[$pagi_name]['cp'] - 1) . $url ."' title='Go To ". ($_SESSION[$pagi_name]['cp'] - 1) ."'>Newer</a></div> ";
				} else {
					$output .= "<div class='pagi_direction'><a href='". $GLOBALS[$pagi_name]['clean_url'] . ($_SESSION[$pagi_name]['cp'] - 1) ."' title='Go To ". ($_SESSION[$pagi_name]['cp'] - 1) ."'>Newer</a></div> ";
				}
			}

			$i = 0;
			while($i++ < $max_page) {
				$len = strlen($i);
				$new_class = " class='". ($len > 2 ? ($len == 3 ? 'three_digit' : 'four_digit') : 'two_digit')."'";
				$bold_class = ($len > 2 ? ($len == 3 ? '_three' : '_four') : '_two');
				if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
					$output .= ($_SESSION[$pagi_name]['cp'] == $i ? " <div class='pagi_bold". $bold_class ."'>". $i ."</div>" : " <div class='pagi_page'><a href='?cp=". $i . $url ."' title='Go To Page ". $i ."'". $new_class .">". $i ."</a></div> ");
				} else {
					$output .= ($_SESSION[$pagi_name]['cp'] == $i ? " <div class='pagi_bold". $bold_class ."'>". $i ."</div>" : " <div class='pagi_page'><a href='". $GLOBALS[$pagi_name]['clean_url'] . $i ."' title='Go To Page ". $i ."'". $new_class .">". $i ."</a></div> ");
				}
				
			}
			if($_SESSION[$pagi_name]['cp'] == $max_page) {
				$output .= "<div class='pagi_direction_disabled'>Older</div> ";
			} else {
				if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
					$output .= " <div class='pagi_direction'><a href='?cp=". ($_SESSION[$pagi_name]['cp'] + 1) . $url ."' title='Go To ". ($_SESSION[$pagi_name]['cp'] + 1) ."'>Older</a></div>";
				} else {
					$output .= " <div class='pagi_direction'><a href='". $GLOBALS[$pagi_name]['clean_url'] . ($_SESSION[$pagi_name]['cp'] + 1) ."/' title='Go To ". ($_SESSION[$pagi_name]['cp'] + 1) ."'>Older</a></div>";
				}
			}
		} else {

			# If the check if we are above 1 in the display nav.  Ex:  3 4 5 _6_ 7 8 9
			$page_offset = floor($_SESSION[$pagi_name]['num_pages_to_show'] / 2);
			
			# Left Side of center
			$start = $_SESSION[$pagi_name]['cp'] - $page_offset;
			$end = $_SESSION[$pagi_name]['cp'] + $page_offset;
			
			# Right side of center
			$start_page = ($start >= 1 ? $start : 1);
			if($end <= $max_page) {
				$end_page = $end;
				if($_SESSION[$pagi_name]['cp'] <= $page_offset) {
					$end_page = ($page_offset * 2) + 1;
					# If the new value is bigger than the max page, show the max page.
					if($end_page > $max_page) {
						$end_page = $max_page;
					}
				}
			} else {
				$end_page = $max_page;
			}
			
			# Extra check to see if they are in an early set of pages, example 1-3 when 10 should be shown:
			$start_check = $max_page - $_SESSION[$pagi_name]['cp'];
			if($start_check <= $page_offset) {
				$start_page -= ($page_offset - $start_check);
				# If the new value is bigger than the max page, show the max page.
				if($start_page < 1) {
					$start_page = 1;
				}
			}
			

			# Set the auto padding even though it might not need it for the pagi.
			if($_SESSION[$pagi_name]['cp'] == 1) {
				$output .= "<div class='pagi_direction_disabled'>Previous</div> ";
			} else {
				if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
					$output .= "<div class='pagi_direction'><a href='?cp=". ($_SESSION[$pagi_name]['cp'] - 1) . $url ."' title='Go To ". ($_SESSION[$pagi_name]['cp'] - 1) ."'>Previous</a></div> ";
				} else {
					$output .= "<div class='pagi_direction'><a href='". $GLOBALS[$pagi_name]['clean_url'] . ($_SESSION[$pagi_name]['cp'] - 1) ."/' title='Go To ". ($_SESSION[$pagi_name]['cp'] - 1) ."'>Previous</a></div> ";
				}
			}
			
			# Set the auto padding even though it might not need it for the pagi.
			$style = ($start_page > 1 ? '' : ' style="visibility: hidden;"');
			if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
				$output .= " <span". $style ."><div class='pagi_page'><a href='?cp=1". $url ."' title='Go To First Page' class='two_digit'>1</a></div><div class='pagi_filler'>...</div></span> ";
			} else {
				$output .= " <span". $style ."><div class='pagi_page'><a href='". $GLOBALS[$pagi_name]['clean_url'] ."1/' title='Go To First Page' class='two_digit'>1</a></div><div class='pagi_filler'>...</div></span> ";
			}
			

			while($start_page <= $end_page) {
				$len = strlen($start_page);
				$new_class = " class='". ($len > 2 ? ($len == 3 ? 'three_digit' : 'four_digit') : 'two_digit')."'";
				$bold_class = ($len > 2 ? ($len == 3 ? '_three' : '_four') : '_two');
				if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
					$output .= ($_SESSION[$pagi_name]['cp'] == $start_page ? "<div class='pagi_bold". $bold_class ."'>". $start_page ."</div>" : " <div class='pagi_page'><a href='?cp=". $start_page . $url ."' title='Go To Page ". $start_page ."'". $new_class .">". $start_page ."</a></div> ");
				} else {
					$output .= ($_SESSION[$pagi_name]['cp'] == $start_page ? "<div class='pagi_bold". $bold_class ."'>". $start_page ."</div>" : " <div class='pagi_page'><a href='". $GLOBALS[$pagi_name]['clean_url']. $start_page ."/' title='Go To Page ". $start_page ."'". $new_class .">". $start_page ."</a></div> ");
				}
				$start_page++;
			}


			#$style = ($end_page < $max_page ? ' style="visibility: hidden;"' : '');
			$style = ($end_page < $max_page ? '' : ' style="visibility: hidden;"');
			$len = strlen($max_page);
			$new_class = " class='". ($len > 2 ? ($len == 3 ? 'three_digit' : 'four_digit') : 'two_digit')."'";
			$bold_class = ($len > 2 ? ($len == 3 ? '_three' : '_four') : '_two');
			if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
				$output .= " <span". $style ."><div class='pagi_filler'>...</div><div class='pagi_page'><a href='?cp=". $max_page . $url ."' title='Go To Last Page'". $new_class .">". $max_page ."</a></div></span>";
			} else {
				$output .= " <span". $style ."><div class='pagi_filler'>...</div><div class='pagi_page'><a href='". $GLOBALS[$pagi_name]['clean_url'] . $max_page ."/' title='Go To Last Page'". $new_class .">". $max_page ."</a></div></span>";
			}
			
			

			if($_SESSION[$pagi_name]['cp'] == $max_page) {
				$output .= "<div class='pagi_direction_disabled'>Next</div> ";
			} else {	
				if($_SESSION[$pagi_name]['pretty_urls'] == 0) {
					$output .= " <div class='pagi_direction'><a href='?cp=". ($_SESSION[$pagi_name]['cp'] + 1) . $url ."' title='Go To ". ($_SESSION[$pagi_name]['cp'] + 1) ."'>Next</a></div>";
				} else {
					$output .= " <div class='pagi_direction'><a href='". $GLOBALS[$pagi_name]['clean_url'] . ($_SESSION[$pagi_name]['cp'] + 1) ."/' title='Go To ". ($_SESSION[$pagi_name]['cp'] + 1) ."'>Next</a></div>";
				}
			}
		}
		$output .= "<div class='pagi_clear'></div></div>";
		echo $output;
		return true;
	}

##################################################
#
#
#
##################################################
	function pagination_query($query,$display,$extra='',$db='default',$query2='') {
		_error_debug('Pagination Query', __FUNCTION__,__LINE__,__FILE__);

		if(empty($db)) { $db = 'default'; }
		$pagi_name = $GLOBALS['pagination']['name'];
				
		# If a second query was supplied for the count, use that instead.
		if($query2 != "") {
			$results = db_query($query2,$display,$extra,$db);
			$res_count = db_fetch_row($results);
			$_SESSION[$pagi_name]['max_results'] = $res_count['total'];
		} else {

			$q2 = str_replace(stristr($query, 'order by'),'',$query);
			$q2 = "select count(*) as cnt from (". $q2 .") as q";

			$res = db_query($q2,$display,'fetch');
			$_SESSION[$pagi_name]['max_results'] = $res['cnt'];

		}
		
		$q2 = $query ." LIMIT ". $_SESSION[$pagi_name]['ipp'] ." OFFSET ". (($_SESSION[$pagi_name]['cp'] - 1) * $_SESSION[$pagi_name]['ipp']);
		$GLOBALS['returned_paginated_query'] = $q2;
		return db_query($q2,$display ." - limited",$extra,$db);
	}

?>