<?php
require_once('inc/endpoint_helper.php');
require_once('inc/page_components.php');

$errorEncountered = false;
$errorMsg = '';
$mapJS = '';

// check if we got a matchid
if(!empty($_GET['matchid']) && !empty($_GET['region']))
{
	// get the summoner ID from the given summoner name and region
	//$summonerID = getSummonerIdFromName($_GET['region'], $_GET['name']);
	
	$matchID = $_GET['matchid'];
	$region = $_GET['region'];
	
	if($matchID == null)
	{
		$errorEncountered = true;
		$errorMsg = 'Could not find match.';
	}
	else if(!isValidRegion($region))
	{
		$errorEncountered = true;
		$errorMsg = 'A valid region was not selected.';
	}
	else
	{
		// get the match data
		$matchData = getMatchData($region, $matchID);
		
		//error_log('In map.php. matchData: ' . print_r($matchData, true));

		$mapJS = getMapJS($matchData);
		
		// check for errors
		if($mapJS == 'Service unavailable')
		{
			$errorEncountered = true;
			$errorMsg = 'Riot\'s data service is currently unavailable. :(';
		}
	}
}
else
{
	$errorEncountered = true;

	if(empty($_GET['matchid']))
	{	
		$errorMsg = 'No match searched.';
	}
	
	if(empty($_GET['region']))
	{			
		$errorMsg .= ' No region specified.';
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Flash Replay</title>
	<?php outputHeadCssAndJs(); ?>
	<script src="http://d3js.org/d3.v3.js"></script>
	<?php
	// if we didn't hit an error, we'll add the javascript that will load the map and data
	if($errorEncountered == false && $mapJS != '')
	{
	?>
	<script type="text/javascript">
	<?php echo $mapJS; ?>
	</script>
	<?php
	}
	?>
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
		<div id="map-container" style="display: none;">
			<div id="team-stats">
				<div id="team-stats-header">Overall Team Totals</div>
				<div id="team-stats-header-row">
					<span id="left-team-stats" class="team-stats-half-bar">
						<span class="team-stats-cell"><img src="img/scoreboardicon_minion.png" title="Total Minions (Normal and Jungle)" /><span id="left-team-stats-minions">0</span></span>
						<span class="team-stats-cell"><img src="img/baron_nashor_100.png" class="objective-icon" title="Total Baron Nashor Kills" /><span id="left-team-stats-baron-kills">0</span></span>
						<span class="team-stats-cell"><img src="img/dragon_100.png" class="objective-icon" title="Total Dragon Kills" /><span id="left-team-stats-dragon-kills">0</span></span>
						<span class="team-stats-cell"><img src="img/scoreboardicon_gold.png" title="Total Gold" /><span id="left-team-stats-gold">0</span></span>
						<span class="team-stats-cell"><img src="img/inhibitor_building_100.png" class="objective-icon" title="Total Inhibitors Destroyed" /><span id="left-team-stats-inhibitor-kills">0</span></span>
						<span class="team-stats-cell"><img src="img/turret_100.png" class="objective-icon" title="Total Turrets Destroyed" /><span id="left-team-stats-turret-kills">0</span></span>
						<span class="team-stats-cell">Lvl <span id="left-team-stats-levels">0</span></span>
						<span class="team-stats-kda">
							<img src="img/scoreboardicon_score.png" title="Kills / Deaths / Assists" />
							<span id="left-team-stats-kills">0</span> / <span id="left-team-stats-deaths">0</span> / <span id="left-team-stats-assists">0</span>
						</span>
					</span>
					<span id="right-team-stats" class="team-stats-half-bar">
						<span class="team-stats-kda">
							<img src="img/scoreboardicon_score.png" title="Kills / Deaths / Assists" />
							<span id="right-team-stats-kills">0</span> / <span id="right-team-stats-deaths">0</span> / <span id="right-team-stats-assists">0</span>
						</span>
						<span class="team-stats-cell">Lvl <span id="right-team-stats-levels">0</span></span>
						<span class="team-stats-cell"><img src="img/turret_200.png" class="objective-icon" title="Total Turrets Destroyed" /><span id="right-team-stats-turret-kills">0</span></span>
						<span class="team-stats-cell"><img src="img/inhibitor_building_200.png" class="objective-icon" title="Total Inhibitors Destroyed" /><span id="right-team-stats-inhibitor-kills">0</span></span>
						<span class="team-stats-cell"><img src="img/scoreboardicon_gold.png" title="Total Gold" /><span id="right-team-stats-gold">0</span></span>
						<span class="team-stats-cell"><img src="img/dragon_200.png" class="objective-icon" title="Total Dragon Kills" /><span id="right-team-stats-dragon-kills">0</span></span>
						<span class="team-stats-cell"><img src="img/baron_nashor_200.png" class="objective-icon" title="Total Baron Nashor Kills" /><span id="right-team-stats-baron-kills">0</span></span>
						<span class="team-stats-cell"><img src="img/scoreboardicon_minion.png" title="Total Minions (Normal and Jungle)" /><span id="right-team-stats-minions">0</span></span>
					</span>
				</div>
			</div>
			<div id="team-map-container">
				<div id="team-info-left">
					<div id="team-table-left" class="team-data-table div-table">
						<div class="team-data-row header-row div-row">
							<div class="team-data-cell div-cell"><img src="img/scoreboardicon_champion.png" title="Champion" /></div>
							<div class="team-data-cell div-cell">Lvl</div>
							<div class="team-data-cell div-cell"><img src="img/scoreboardicon_score.png" title="Kills / Deaths / Assists" /></div>
							<div class="team-data-cell div-cell"><img src="img/scoreboardicon_gold.png" title="Total Gold" /></div>
							<div class="team-data-cell div-cell"><img src="img/scoreboardicon_minion.png" title="Total Minions (Normal and Jungle)" /></div>
						</div>
					</div>
				</div>
				<div id="team-info-right">
					<div id="team-table-right" class="team-data-table div-table">
						<div class="team-data-row header-row div-row">
							<div class="team-data-cell div-cell"><img src="img/scoreboardicon_minion.png" title="Total Minions (Normal and Jungle)" /></div>
							<div class="team-data-cell div-cell"><img src="img/scoreboardicon_gold.png" title="Total Gold" /></div>
							<div class="team-data-cell div-cell"><img src="img/scoreboardicon_score.png" title="Kills / Deaths / Assists" /></div>
							<div class="team-data-cell div-cell">Lvl</div>
							<div class="team-data-cell div-cell"><img src="img/scoreboardicon_champion.png" title="Champion" /></div>
						</div>
					</div>
				</div>
				<div id="map">
				</div>
			</div>
			<div id="controls">
				<?php
				/*
				// maybe a rangeslider at some point
				<div id="rangeslider">
					<!--<input type="range" id="timeline" min="0" max="100" value="0" />-->
				</div>
				*/
				?>
				<br />
				<div id="controls-container">
					<div id="button-controls">
					<button type="button" class="btn btn-default flash-button" title="Previous step" aria-label="Previous step" id="prev-step"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span></button>
					<button type="button" class="btn btn-default flash-button" title="Play replay" aria-label="Play replay" id="play-replay"><span id="btn-play" class="glyphicon glyphicon-play" aria-hidden="true"></span></button>
					<button type="button" class="btn btn-default flash-button" title="Next step" aria-label="Next step" id="next-step"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></button>
					<div id="time-container">
						<div>Match Time:</div>
						<div id="current-step"></div>
					</div>
					</div>
				</div>
			</div>
		</div>
		<br />
		<div id="back-link"><a href="index.php">Back</a></div>
	</div>
	<?php outputFooter(); ?>
</body>
</html>
<?php

?>