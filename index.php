<?php
require_once('inc/endpoint_helper.php');
require_once('inc/page_components.php');

$errorEncountered = false;
$errorMsg = '';
$matchHistory = null;

// check if the form was submitted
if(!empty($_GET['name']) && !empty($_GET['region']))
{
	// get the summoner ID from the given summoner name and region
	$summonerID = getSummonerIdFromName($_GET['region'], $_GET['name']);
	
	if($summonerID == null)
	{
		$errorEncountered = true;
		$errorMsg = 'Could not find summoner for that region.';
	}
	else
	{
		// get the match history data
		$matchHistory = getRecentGamesForSummoner($_GET['region'], $summonerID);
		
		//error_log('Got match history. $summonerID: ' . $summonerID . ', $matchHistory: ' . print_r($matchHistory, true));
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Flash Replay</title>
	<?php outputHeadCssAndJs(); ?>
	
	<style type="text/css">
		#map svg {
			margin-left: auto;
			margin-right: auto;
		}
		/*
		#map svg {
			position: absolute;
			top: 0;
			left: 0;
		}

		#map img {
			 position: absolute;
			top: 0;
			left: 0;   
		}
		*/
	</style>
</head>
<body>
	<?php outputHeader(); ?>
	<div id="main-content">
		<?php
		// if we had an error, we'll display it here
		if($errorEncountered == true)
		{
		?>
		<div id="error-msg" class="bg-danger">
		<?php
			// if we got a specific error message, display it
			if($errorMsg != null && $errorMsg != '')
			{
				echo $errorMsg;
			}
			// otherwise we'll display a generic error message
			else
			{
				echo 'An error occured.';
			}
		?>
		</div>
		<?php	
		}
		?>
		<div id="form-container">
			<div id="form-wrap">
				<form id="summoner-form" method="get">
					<div>
						Summoner Name: <input type="text" name="name" /> Region: 
						<select name="region">
							<option value="na">North America</value>
							<option value="br">Brazil</value>
							<option value="eune">Europe Nordic & East</value>
							<option value="euw">Europe West</value>
							<option value="kr">Republic of Korea</value>
							<option value="lan">Latin America North</value>
							<option value="las">Latin America North</value>
							<option value="oce">Oceania</value>
							<option value="tr">Turkey</value>
							<option value="ru">Russia</value>
						</select>
						<input class="btn btn-default flash-button" type="submit" value="Get Recent Matches"/>
					</div>
				</form>
			</div>
		</div>
		<?php 
		if($matchHistory != null)
		{
			outputMatchHistory($_GET['region'], $matchHistory);
		}
		?>
	</div>
	<?php outputFooter(); ?>
</body>
</html>
<?php

?>