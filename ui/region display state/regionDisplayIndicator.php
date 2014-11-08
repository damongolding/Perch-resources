<?php

$createMarkUp  = "<script type='text/javascript'>";
$createMarkUp .= "$('.inner table thead tr th:first').after('<th>Is region displayed on page?</th>');"; // Create header cell
$createMarkUp .= "$('.inner table tbody tr').each(function(){";
$createMarkUp .= "$(this).find('td:first').after('<td class=\"isLive\"></td>');"; // Create body cell(s)
$createMarkUp .= "});";
$createMarkUp .= "</script>";


if(strpos($_SERVER['REQUEST_URI'],"core/apps/content/page/")) // are we on page view
{

	echo $createMarkUp;// inject into page

	$DB = PerchDB::fetch(); // create connection to database
	
	foreach($regions as $Region) // for each region on page
	{
		$id = $Region->id(); // ID of region
		$rev = $Region->regionRev(); // Rev (most recent history item) of region
	
		$isSiteLive = $DB->get_value("SELECT itemJSON FROM perch2_content_items WHERE itemJSON!='' AND regionID='$id' AND itemRev='$rev'"); // Get the JSON
	
		$isSiteLive = preg_replace('/\s+/', '', $isSiteLive); //strip whitespace
		
		$isSiteLive = json_decode($isSiteLive,true); // Format JSON into a array


		if(isset($isSiteLive['hideSection']) && $isSiteLive['hideSection'] === "Hidden")
		{
			$out  = "<script>";
			$out .= "$('a:contains(\"" . $Region->regionKey() . "\")').parent().siblings('.isLive').text('Hidden');";
			$out .= "</script>";
			echo $out;
		}
		else
		{
			$out  = "<script>";
			$out .= "$('a:contains(\"" . $Region->regionKey() . "\")').parent().siblings('.isLive').text('Displayed');";
			$out .= "</script>";
			echo $out;
		}
	
	
	}
}

?>