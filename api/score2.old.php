<?php
/******************************************************************
XML Scores API

This API will allow an external device to upload scoring data to
the database for a specific child. The parameters world, parent,
child, game, score, and question are all required but this API
also allows for an optional parameter time that can be passed as
a unix timestamp. This parameter allows the program using the API
to cache data over a given period and make multiple calls to store
the data keeping the correct time of the score.

Example Posting a Score using a get request:
	
	http://clevercrazes.com/xml/score.php?world=14&parent=379&child=3529&game=124&score=100&question=15852
	
	
Example returning XML:

	<CMSData>
	  <Result>
	    <Value>true</Value>
	  </Result>
	</CMSData>
	
------------------------------------------------------------------

Example Request for Top scores and child score
	
	http://clevercrazes.com/xml/score.php?act=topscores&child=29367
	
Example returning XML

	<CMSData>
	  <Scores>
	    <TopTen>
	      <Child>
	        <Id>29367</Id>
	        <FirstName>Richie</FirstName>
	        <Grade>2</Grade>
	        <LastPlayed>2012-06-03 10:27:37</LastPlayed>
	        <Score>299680</Score>
	      </Child>
			.
			.
			.
	      <Child>
	        <Id>56686</Id>
	        <FirstName>Mena I</FirstName>
	        <Grade>7</Grade>
	        <LastPlayed>2012-06-04 13:17:13</LastPlayed>
	        <Score>26735</Score>
	      </Child>
	    </TopTen>
	    <Child>
	      <Id>29367</Id>
	      <FirstName>Richie</FirstName>
	      <Grade>2</Grade>
	      <LastPlayed>2012-06-03 10:27:37</LastPlayed>
	      <Score>299680</Score>
	    </Child>
	  </Scores>
	</CMSData>	
	
------------------------------------------------------------------

Example Request for child score (ONLY)
	
	http://clevercrazes.com/xml/score.php?act=topscores&child=29367&omit=additional
	
Example returning XML

	<CMSData>
	  <Scores>
	    <Child>
	      <Id>29367</Id>
	      <FirstName>Richie</FirstName>
	      <Grade>2</Grade>
	      <LastPlayed>2012-06-03 10:27:37</LastPlayed>
	      <Score>299680</Score>
	    </Child>
	  </Scores>
	</CMSData>
	
******************************************************************/
//Here we go!!!

//Not our cms but we still need this
CHDIR('../');
include_once('inc/preferences.php');
CHDIR('xml');

header('Content-type: text/xml');
echo "<CMSData>";

//We need many more fields for this one
if(array_key_exists('world', $_REQUEST) AND array_key_exists('game', $_REQUEST) AND
	 array_key_exists('child', $_REQUEST) AND array_key_exists('parent', $_REQUEST) AND
	 array_key_exists('question', $_REQUEST) AND array_key_exists('score', $_REQUEST) AND
	 array_key_exists('reps', $_REQUEST) AND array_key_exists('playasaclassroom', $_REQUEST))
{

	//The problem here is that we want to validate input without taking too much time
	//make sure the child exists, if so check to make sure the parent is correct
	//otherwise we really don't care
	$child_query = "SELECT * FROM children WHERE id = ".mysql_real_escape_string($_REQUEST['child']);
	$child_result = mysql_query($child_query);
	
	if(mysql_num_rows($child_result) === 1)
	{
		$child = mysql_fetch_array($child_result, MYSQL_ASSOC);
		
		if($child['parentid'] !== $_REQUEST['parent'])
		{
			echo "<Error>".
				"<Message>The data provided was malformed.</Message>".
				"<Details>The child parent's id must match that provided.</Details>".
				"</Error>";
		}
		else
		{
			//quick and dirty method of simple validation
			if(is_numeric($_REQUEST['world']) AND is_numeric($_REQUEST['game']) AND is_numeric($_REQUEST['child']) AND
				is_numeric($_REQUEST['parent']) AND is_numeric($_REQUEST['question']) AND is_numeric($_REQUEST['score']) AND
				is_numeric($_REQUEST['reps']) AND is_numeric($_REQUEST['playasaclassroom']) AND $_REQUEST['score'] > 0)
			{
				$insert_query = "INSERT INTO scores (world_ID, game_ID, child_ID, parent_ID, question_ID, score, time, client, ip, reps, playingasaclassroom) ".
					"VALUES (".$_REQUEST['world'].", ".$_REQUEST['game'].", ".$_REQUEST['child'].", ".$_REQUEST['parent'].", ".
					$_REQUEST['question'].", ".$_REQUEST['score'].", ".
					"'".(array_key_exists('time', $_REQUEST) ? date('Y-m-d H:i:s', strtotime($_REQUEST['time'])) : date('Y-m-d H:i:s'))."'".
					", '".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."', '".$_SERVER['REMOTE_ADDR']."', ".
					$_REQUEST['reps'].", ".$_REQUEST['playasaclassroom'].")";
					
					
				$insert_result = mysql_query($insert_query);
				
				if($insert_result === TRUE)
					echo "<Result><Value>true</Value></Result>";
				else
					echo "<Result><Value>false</Value><Message>".mysql_error()." Please contact the system administrator.</Message></Result>";
			}
			else
			{
				echo "<Error>".
					"<Message>The data provided was malformed.</Message>".
					"<Details>All require values must be past as integer values.</Details>".
					"</Error>";
			}
		}
	}
	else
	{
		echo "<Error>".
			"<Message>The data provided was malformed.</Message>".
			"<Details>The requested child doesn't exist.</Details>".
			"</Error>";
	}
}
elseif(array_key_exists('act', $_REQUEST) AND ($_REQUEST['act'] == 'topscores'))
{
	//get the beginning date for the day of the week
	$beginningOfWeek = date('Y-m-d', strtotime('this week -1 day', time()));
	$omitAdditional = array_key_exists('omit', $_REQUEST) AND ($_REQUEST['omit'] == 'additional');
	/************
	* Special Note: I decided to put the topscore query above the child section because I
	* 	wanted all queries done first, this helps when returning data to the requester.
	************/
	$topscore_query = 'SELECT c.id, c.firstname, c.grade, time, sum(score) AS score FROM scores '.
						'left join children as c on child_ID = c.id '.
						"WHERE child_ID != 0 AND time >= '".$beginningOfWeek.
						"' AND game_id != 109 AND game_id is not null ".
						'GROUP BY child_ID ORDER BY score DESC LIMIT 10';
	
	//echo '<Query>'.$topscore_query.'</Query>';
	$topscore_result = null;
	$child_xml = null;
	$additional_xml = null;
	
	//this allows the requester to skip performing the time intensive query for all scores
	if(!$omitAdditional)
		$topscore_result = mysql_query($topscore_query);
	
	//if a child was specified we'll include their data will the topscore data
	if(array_key_exists('child', $_REQUEST))
	{
		//get child data and include it in results
		$child_query = 'SELECT c.id, c.firstname, c.grade, time, sum(score) AS score FROM scores '.
						'left join children as c on child_ID = c.id '.
						'WHERE child_ID != 0 AND time >= \''.$beginningOfWeek.'\' AND game_id != 109 AND game_id is not null AND '.
						'child_ID = '.mysql_real_escape_string($_REQUEST['child']);
		$child_result = mysql_query($child_query);
		
		if(mysql_num_rows($child_result) > 0)
		{
			$child = mysql_fetch_array($child_result, MYSQL_ASSOC);
			$child_xml = '<Child>'.
							'<Id>'.$child['id'].'</Id>'.
							'<FirstName>'.$child['firstname'].'</FirstName>'.
							'<Grade>'.$child['grade'].'</Grade>'.
							'<LastPlayed>'.$child['time'].'</LastPlayed>'.
							'<Score>'.$child['score'].'</Score>'.
							'</Child>';
			
		}
		else
		{
			echo "<Error>".
			"<Message>The data requested could not be provided.</Message>".
			"<Details>The requested child does not exist.</Details>".
			"</Error>";
		}
	}
	
	//run through the additional results
	if(!$omitAdditional AND !is_null($topscore_result))
	{
		if(mysql_num_rows($topscore_result) > 0)
		{
			
			$Additional = array();
			while ($a = mysql_fetch_array($topscore_result, MYSQL_ASSOC))
				$Additional[] = $a;
				
			$additional_xml = '';
			$cAdditional = count($Additional);
			for($i = 0; $i < $cAdditional; $i++)
			{
				$additional_xml .= '<Child>'.
							'<Id>'.$Additional[$i]['id'].'</Id>'.
							'<FirstName>'.$Additional[$i]['firstname'].'</FirstName>'.
							'<Grade>'.$Additional[$i]['grade'].'</Grade>'.
							'<LastPlayed>'.$Additional[$i]['time'].'</LastPlayed>'.
							'<Score>'.$Additional[$i]['score'].'</Score>'.
							'</Child>';
			}
		}
		else
		{
			echo "<Warning>".
			"<Message>No scores were found.</Message>".
			"<Details>The requested information could not be provided because no additional data exists.</Details>".
			"</Warning>";
		}
	}
	//echo '<Date>'.date('Y-m-d', strtotime('this week -1 day', time())).'</Date>';
	
	echo '<Scores>';
	if(!$omitAdditional AND !is_null($additional_xml))
	{
		echo '<TopTen>';
		echo $additional_xml;
		echo '</TopTen>';
	}
	if(!is_null($child_xml))
	{
		echo $child_xml;	
	}
		
	echo '</Scores>';

}
else
{
	echo "<Error>".
		"<Message>The data required was not provided.</Message>".
		"<Details>Required fields: world, game, child, parent, question, score, and time were not all provided.</Details>".
		"</Error>";
}

echo "</CMSData>";

?>