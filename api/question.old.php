<?php
/******************************************************************
XML Question API

This API will allow an external device to upload scoring data to
the database for a specific child. The parameters world, parent,
child, game, score, and question are all required but this API
also allows for an optional parameter time that can be passed as
a unix timestamp. This parameter allows the program using the API
to cache data over a given period and make multiple calls to store
the data keeping the correct time of the score.

Example using a get request:
	
	//to request a list of questions
	http://clevercrazes.com/xml/question.php?world=worldchar&theme=theme&genre=genre 
		optional parameters are:
			subcat (any subcat is assumed),
			grade (4th grade by default),
			items (the number of items to return, default to 10)
	
Example returning XML:

	<CMSData>
	  <Request>
	    <World>b</World>
	    <Theme>or</Theme>
	    <Genre>q</Genre>
	    <Grade>4</Grade>
	    <Items>3</Items>
	  </Request>
	  <Questions>
	    <Question>
	      <Id>27600</Id>
	      <MaximumPoints>100</MaximumPoints>
	      <World>b</World>
	      <Theme>or</Theme>
	      <DateInformation>10,27,11</DateInformation>
	      <Grade>4</Grade>
	      <Genre>q</Genre>
	      <Number>18</Number>
	      <Fact>How many body parts do all insects have?</Fact>
	      <Answer1>two</Answer1>
	      <Answer2>three</Answer2>
	      <Answer3>four</Answer3>
	      <Answer4/>
	      <Answer5/>
	      <CorrectAnswer>2</CorrectAnswer>
	      <Subcat/>
	      <Audio>at-1.mp3</Audio>
	    </Question>
	    <Question>
	      <Id>27612</Id>
	      <MaximumPoints>100</MaximumPoints>
	      <World>b</World>
	      <Theme>or</Theme>
	      <DateInformation>10,27,11</DateInformation>
	      <Grade>4</Grade>
	      <Genre>q</Genre>
	      <Number>24</Number>
	      <Fact>Crickets are an example of insects that rub their legs and wings together to make a chirping sound.  Why do they do this?</Fact>
	      <Answer1>They like to make pretty music.</Answer1>
	      <Answer2>They are communicating with other crickets.</Answer2>
	      <Answer3>They are exercising.</Answer3>
	      <Answer4/>
	      <Answer5/>
	      <CorrectAnswer>2</CorrectAnswer>
	      <Subcat/>
	      <Audio>at-2.mp3</Audio>
	    </Question>
	    <Question>
	      <Id>18287</Id>
	      <MaximumPoints>100</MaximumPoints>
	      <World>b</World>
	      <Theme>or</Theme>
	      <DateInformation>4,4,11</DateInformation>
	      <Grade>4</Grade>
	      <Genre>q</Genre>
	      <Number>6</Number>
	      <Fact>Living things work together to perform various processes.</Fact>
	      <Answer1>TRUE</Answer1>
	      <Answer2>FALSE</Answer2>
	      <Answer3/>
	      <Answer4/>
	      <Answer5/>
	      <CorrectAnswer>1</CorrectAnswer>
	      <Subcat/>
	      <Audio>at-3.mp3</Audio>
	    </Question>
	  </Questions>
	</CMSData>
	
	
******************************************************************/
//Here we go!!!

//Not our cms but we still need this
CHDIR('../');
include_once('inc/preferences.php');
CHDIR('xml');

//let's always return something xml friendly
header('Content-type: text/xml');

echo "<CMSData>";

if(array_key_exists('world', $_REQUEST) AND array_key_exists('genre', $_REQUEST))
{
	$numitems = (array_key_exists('items', $_REQUEST) ? ((int)$_REQUEST['items']) : 10);
	
    if(!isset($_REQUEST['theme']) or empty($_REQUEST['theme']))
    {
        $findtheme = '1';
    }
    else
    {
        $findtheme = '';
        if(strstr($_REQUEST['theme'], ',') !== FALSE)
        {
            $themes = explode(',', $_REQUEST['theme']);
            $findtheme .= '(';
            $cthemes = count($themes);
            for($i = 0; $i < $cthemes; $i++)
            {
                $findtheme .= " theme = '".mysql_real_escape_string($themes[$i])."'";
                if($i < ($cthemes - 1))
                    $findtheme .= " OR ";
            }
            $findtheme .= ' )';
        }
        else
        {
            $findtheme = "theme = '".mysql_real_escape_string($_REQUEST['theme'])."'";
        }
    }
	
	
    $query = "SELECT * FROM cccurriculum WHERE world = '".mysql_real_escape_string($_REQUEST['world']).
            "' AND ".$findtheme." AND genre = '".mysql_real_escape_string($_REQUEST['genre'])."'".
            " AND active = '1'";
    
	if(array_key_exists('grade', $_REQUEST))
	{
		if((strcasecmp($_REQUEST['grade'], 'k') === 0) OR (strcasecmp($_REQUEST['grade'], '1') === 0))
		{
			$query .= " AND grade = 'k1'";
		}
		else
		{
			$query .= " AND grade = '".mysql_real_escape_string($_REQUEST['grade'])."'";
		}
	}
	else
	{
		$query .= " AND grade = '4'";
	}
	if(array_key_exists('subcat', $_REQUEST))
	{
		$query .= " AND subcat = '".mysql_real_escape_string($_REQUEST['subcat'])."'";
	}
        
	//$query .= " LIMIT 200";
    
	//echo "<Query>$query</Query>";
	$result = mysql_query($query);

	if(!$result || (mysql_num_rows($result) < 1))
	{
		echo "<Error>".
			"<Message>Unable to provide any questions matching parameters passed.</Message>".
			"<Details>We were unable to find questions matching your criteria.</Details>".
			"</Error>";
	}
	else
	{        
		if(mysql_num_rows($result) < $numitems)
		{
			echo "<Warning>".
				"<Message>There were not enough items to return $numitems.</Message>".
				"<Details>Do to the parameters passed we were unable to provide you with $numitems items because there were less than $numitems items as a result of your query.</Details>".
				"</Warning>";
			$numitems = mysql_num_rows($result);
		}
		
		echo "<Request>".
			"<World>".$_REQUEST['world']."</World>".
			"<Theme>".$_REQUEST['theme']."</Theme>".
			"<Genre>".$_REQUEST['genre']."</Genre>".
			(array_key_exists('grade', $_REQUEST) ? "<Grade>".$_REQUEST['grade']."</Grade>" : "<Grade>4</Grade>" ).
			(array_key_exists('subcat', $_REQUEST) ? "<Subcat>".$_REQUEST['subcat']."</Subcat>" : "" ).
			"<Items>$numitems</Items>".
			"</Request>";
		
		//Now we'll get a list of questions that will work for us
		$AllQuestions = array();

		
		while ($q = mysql_fetch_array($result, MYSQL_ASSOC))
			$AllQuestions[] = $q;
    	
		//echo "<Count>".count($AllQuestions)."</Count>";
		$SelectedQuestions = array();
		$AllItemCount = count($AllQuestions);
		$SelectedCount = 0;
		
		echo "<AllItemCount>".$AllItemCount."</AllItemCount>";
		
        if($numitems < $AllItemCount)
        {
            while($SelectedCount <= $numitems)
            {
                $random = mt_rand(0, $AllItemCount);
                if(($AllQuestions[$random]['id'] != '') && !array_key_exists($random, $SelectedQuestions))
                {
                    $SelectedQuestions[$random] = $AllQuestions[$random];
                    //$AllItemCount--;
                    $SelectedCount++;
                }
            }
        }
        else
        {
            $SelectedQuestions = $AllQuestions;
        }
		
		foreach($AllQuestions as $thisQuestion)
		{
			if($_REQUEST['genre'] == 'q')
			{
				if($thisQuestion['id'] == '134253')
				{
					$SelectedQuestions[] = $thisQuestion;
					break;
				}
			}
			else if($_REQUEST['genre'] == 'f')
			{
				if($thisQuestion['id'] == '134252')
				{
					$SelectedQuestions[] = $thisQuestion;
					break;
				}
			}
		}
		
		//print_r($SelectedQuestions);
		echo "<Questions>";
		foreach($SelectedQuestions as $question)
		{
            if($question['world'] == 'ar') {
                $question['Fact'] = htmlspecialchars($question['Fact']);
                $question['Answer1'] = htmlspecialchars($question['Answer1']);
                $question['Answer2'] = htmlspecialchars($question['Answer2']);
                $question['Answer3'] = htmlspecialchars($question['Answer3']);
            }
            else {
                $question['Fact'] = htmlentities(htmlentities($question['Fact'], ENT_COMPAT | ENT_HTML401));
                $question['Answer1'] = htmlentities(htmlentities($question['Answer1'], ENT_COMPAT | ENT_HTML401));
                $question['Answer2'] = htmlentities(htmlentities($question['Answer2'], ENT_COMPAT | ENT_HTML401));
                $question['Answer3'] = htmlentities(htmlentities($question['Answer3'], ENT_COMPAT | ENT_HTML401));
            }
            
			//$question = $SelectedQuestions[$i];
			echo "<Question>".
				"<Id>".$question['id']."</Id>".
				"<MaximumPoints>".$question['pointvalue']."</MaximumPoints>".
				"<World>".$question['world']."</World>".
				"<Theme>".$question['theme']."</Theme>".
				"<DateInformation>".$question['month'].",".$question['day'].",".$question['year']."</DateInformation>".
				"<Grade>".$question['grade']."</Grade>".
				"<Genre>".$question['genre']."</Genre>".
				"<Number>".$question['number']."</Number>".
				($question['Fact'] != '' ? "<Fact>".$question['Fact']."</Fact>" : "<Fact/>").
				($question['FactMedia'] != '' ? "<FactMedia>".$question['question_media']."</FactMedia>" : "<FactMedia/>").
				($question['Answer1'] != '' ? "<Answer1>".$question['Answer1']."</Answer1>" : "<Answer1/>").
				($question['Answer2'] != '' ? "<Answer2>".$question['Answer2']."</Answer2>" : "<Answer2/>").
				($question['Answer3'] != '' ? "<Answer3>".$question['Answer3']."</Answer3>" : "<Answer3/>").
				($question['Answer4'] != '' ? "<Answer4>".htmlentities(htmlentities($question['Answer4'], ENT_COMPAT | ENT_HTML401))."</Answer4>" : "<Answer4/>").
				($question['Answer5'] != '' ? "<Answer5>".htmlentities(htmlentities($question['Answer5'], ENT_COMPAT | ENT_HTML401))."</Answer5>" : "<Answer5/>").
				($question['CorrectAnswer'] != '' ? "<CorrectAnswer>".$question['CorrectAnswer']."</CorrectAnswer>" : "<CorrectAnswer/>").
				($question['subcat'] != '' ? "<Subcat>".$question['subcat']."</Subcat>" : "<Subcat/>").
				(($question['audio'] != '' && $question['audio'] != null) ? "<Audio>".$question['audio']."</Audio>" : "<Audio/>").
				(($question['audio2'] != '' && $question['audio2'] != null) ? "<Audio2>".$question['audio2']."</Audio2>" : "<Audio2/>").
				(($question['audio3'] != '' && $question['audio3'] != null) ? "<Audio3>".$question['audio3']."</Audio3>" : "<Audio3/>").
				(($question['audio4'] != '' && $question['audio4'] != null) ? "<Audio4>".$question['audio4']."</Audio4>" : "<Audio4/>").
                (($question['photo_content'] != '' && $question['photo_content'] != null) ? "<PhotoContent>".$question['photo_content']."</PhotoContent>" : "<PhotoContent/>").
                (($question['video_content'] != '' && $question['video_content'] != null) ? "<VideoContent>".$question['video_content']."</VideoContent>" : "<VideoContent/>").
                (($question['video_content2'] != '' && $question['video_content2'] != null) ? "<VideoContent2>".$question['video_content2']."</VideoContent2>" : "<VideoContent2/>").
                (($question['video_content3'] != '' && $question['video_content3'] != null) ? "<VideoContent3>".$question['video_content3']."</VideoContent3>" : "<VideoContent3/>").
                (($question['sound_content'] != '' && $question['sound_content'] != null) ? "<SoundContent>".$question['sound_content']."</SoundContent>" : "<SoundContent/>").
                (($question['sound_content2'] != '' && $question['sound_content2'] != null) ? "<SoundContent2>".$question['sound_content2']."</SoundContent2>" : "<SoundContent2/>").
                (($question['sound_content3'] != '' && $question['sound_content3'] != null) ? "<SoundContent3>".$question['sound_content3']."</SoundContent3>" : "<SoundContent3/>").
                (($question['generation'] != '' && $question['generation'] != null) ? "<Generation>".$question['generation']."</Generation>" : "<Generation/>").
				"</Question>";
		}
		echo "</Questions>";
	}
	
}
else
{
	echo "<Error>".
		"<Message>All required data must be provided.</Message>".
		"<Details>The fields world, theme, and genre are required to proceed.</Details>".
		"</Error>";
}

echo "</CMSData>";


//need to join cccurriculum with ccworldcodes to get worldid using curriculumid
//sd
?>