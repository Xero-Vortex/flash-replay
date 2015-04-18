<?php

function outputHeadCssAndJs()
{
	?>
	<link type="text/css" rel="stylesheet" href="css/bootstrap/bootstrap.css">
	<link type="text/css" rel="stylesheet" href="css/bootstrap/bootstrap-theme.css">
	<link href='http://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	<link type="text/css" rel="stylesheet" href="css/map_style.css">
	<?php
}


function outputMatchHistory($region, $matchHistory)
{
	if($matchHistory != null && $region != '')
	{
		// get some static champion data
		$champions = getChampionPHPArray();
		$acceptableSubTypes = getMatchSubTypeDescriptions();
	
		?>
		<div id="match-container">
			<div id="match-list-header">Select a recent match (of games played on Summoner's Rift):</div>
			<div class="match-table table">
			<div class="header-row row">
					<span class="match-cell cell">Champion</span>
					<span class="match-cell cell">Game Type</span>
					<span class="match-cell cell">KDA</span>
					<span class="match-cell cell">Win/Lose</span>
					<span class="match-cell cell">Match Length</span>
					<span class="match-cell cell">Date</span>
					<span class="match-cell cell">View Replay</span>
			</div>
		<?php
	
		//error_log('In outputMatchHistory. $matchHistory: ' . print_r($champions, true) . ', $matchHistory: ' . print_r($matchHistory, true));
	
		// loop through the matches
		foreach($matchHistory['games'] as $match)
		{
			// these variables are game constants from Riot: https://developer.riotgames.com/docs/game-constants
			$classicMode = 'CLASSIC';
			$matchedType = 'MATCHED_GAME';
			$SRMapID = '11';
		
			// we only want to grab classic, matched, normal games on Summoner's Rift
			if($match['gameMode'] == $classicMode && $match['gameType'] == $matchedType && $match['mapId'] == $SRMapID && array_key_exists($match['subType'], $acceptableSubTypes))
			{
				// champ img, game type, k/d/a, outcome, match length, date, view replay
			?>	
				<div class="match-row row">
					<span class="match-cell cell"><img src="img/champions/<?php echo $champions[$match['championId']]['key']; ?>_Square_0.png" title="<?php echo $champions[$match['championId']]['name']; ?>, <?php echo $champions[$match['championId']]['title']; ?>" /></span>
					<span class="match-cell cell"><?php echo $acceptableSubTypes[$match['subType']]; ?></span>
					<span class="match-cell cell"><?php 
						$kills = array_key_exists('championsKilled', $match['stats']) ? $match['stats']['championsKilled'] : 0;
						$deaths = array_key_exists('numDeaths', $match['stats']) ? $match['stats']['numDeaths'] : 0;
						$assists = array_key_exists('assists', $match['stats']) ? $match['stats']['assists'] : 0;
						
						echo $kills . '/' . $deaths . '/' . $assists; 
					?></span>
					<span class="match-cell cell"><?php echo $match['stats']['win'] == '1' ? 'Win' : 'Lose'; ?></span>
					<span class="match-cell cell"><?php echo gmdate('i:s', $match['stats']['timePlayed']); ?></span>
					<span class="match-cell cell"><?php echo getDateFromEpochTime($match['createDate']); ?></span>
					<span class="match-cell cell"><a href="map.php?region=<?php echo $region; ?>&matchid=<?php echo $match['gameId']; ?>">View Replay</a></span>
				</div>
			<?php
			}
		}
		
		?>
			</div>
		</div>
		<?php
	}
}

function outputHeader()
{
	?>
	<h1 id="main-title">Flash Replay</h1>
	<h3 id="main-subtitle">A quick replay of League of Legends matches</h3>
	<br />
	<?php
}


function outputFooter()
{
	?>
	<div id="site-footer">
		<div class="footer-text">Flash Replay was created for the Riot Games API Challenge that ran from March 30, 2015 through April 17, 2015.<br /><br />
		Flash Replay isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends © Riot Games, Inc.</div>
	</div>
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-61968631-1', 'auto');
	  ga('send', 'pageview');
	</script>
	<?php
}

?>