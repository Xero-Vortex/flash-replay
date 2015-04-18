<?php
// this file should define APIKEY to the value of the API key
require_once('inc/config.php');

// define some constants
// regions
define('REGION_NA', 'na');
define('REGION_BR', 'br');
define('REGION_EUNE', 'eune');
define('REGION_EUW', 'euw');
define('REGION_KR', 'kr');
define('REGION_LAN', 'lan');
define('REGION_LAS', 'las');
define('REGION_OCE', 'oce');
define('REGION_TR', 'tr');
define('REGION_RU', 'ru');

// global variable to store the static champion data
$championData = array();

function getAPIKey()
{
	if(defined('APIKEY'))
	{
		return APIKEY;
	}
	else
	{
		return null;
	}
}

function getRegions()
{
	// returning an array with all the regions, so we can check that we got a valid region input
	$rgn[REGION_NA] = 1;
	$rgn[REGION_BR] = 1;
	$rgn[REGION_EUNE] = 1;
	$rgn[REGION_EUW] = 1;
	$rgn[REGION_KR] = 1;
	$rgn[REGION_LAN] = 1;
	$rgn[REGION_LAS] = 1;
	$rgn[REGION_OCE] = 1;
	$rgn[REGION_TR] = 1;
	$rgn[REGION_RU] = 1;
	
	return $rgn;
}

function isValidRegion($region = '')
{
	// check if the given region is in the list of valid regions
	return array_key_exists($region, getRegions());
}

function getCleanSummonerName($name = '')
{
	if($name == '')
	{
		return null;
	}
	else
	{
		return htmlspecialchars(stripslashes(strtolower(str_replace(' ', '', trim($name)))));
	}
}

// returns: Array ( [xerovortex] => Array ( [id] => 333995 [name] => Xero Vortex [profileIconId] => 607 [summonerLevel] => 30 [revisionDate] => 1427773024000 ) )
function getPlayerInfo($region = '', $name = '')
{
	if($region != '' && $name != '' && isValidRegion($region))
	{	
		// make sure that name is clean
		$cleanName = getCleanSummonerName($name);

		$url = 'https://na.api.pvp.net/api/lol/' . $region . '/v1.4/summoner/by-name/' . $cleanName . '?api_key=' . getAPIKey();
		
		//error_log('In getPlayerInfo. name: ' . $name . ', cleanName: ' . $cleanName . ', region: ' . $region . ', url: ' .$url);
		
		return getJSONObject($url);
	}
	
	// need a name and region but didn't get at least one, returning null
	return null;
}

function getSummonerIdFromName($region = '', $name = '')
{
	if($region != '' && $name != '')
	{
		$playerData = getPlayerInfo($region, $name);
		
		//error_log('In getSummonerIdFromName. playerData: ' . print_r($playerData, true));
		
		if($playerData == null)
		{
			return null;
		}
		else
		{
			// return the summoner ID
			$cleanName = getCleanSummonerName($name);
			return $playerData[$cleanName]['id'];
		}
	}
}

// beginDate should be Epoch seconds representing the start date for the game list. 
// Must represent a time with an even 5 minute offset (e.g., 11:00, 11:05, 11:10, etc.)
/*
Array ( [beginDate] => 1427990400 [matchIDs] => Array ( [0] => 1780698745 [1] => 1780717464 [2] => 1780698974 [3] => 1780699478 [4] => 1780699659 [5] => 1780708376 [6] => 1780709139 [7] => 1780709250 [8] => 1780709268 [9] => 1780718713 [10] => 1780730852 [11] => 1780718799 [12] => 1780699561 [13] => 1780708809 [14] => 1780709369 [15] => 1780717949 [16] => 1780718318 [17] => 1780718474 [18] => 1780718629 [19] => 1780719400 [20] => 1780709069 [21] => 1780709552 [22] => 1780718854 [23] => 1780718921 [24] => 1780718991 [25] => 1780719203 [26] => 1780719266 [27] => 1780719319 ) )
*/ 
function getURFMatchIDs($region = '', $beginDate)
{
	if($region != '' && $beginDate != null && isInteger($beginDate))
	{	
		// 4/2/2015 16:00:00 UTC = 1427990400
		$url = 'https://na.api.pvp.net/api/lol/' . $region . '/v4.1/game/ids?beginDate=' . $beginDate . '&api_key=' . getAPIKey();
		
		//echo 'Getting contents of url: <a href="' . $url . '">' . $url . '</a><br /><br />';
		
		$data = getJSONObject($url);
		
		$returnData['beginDate'] = $beginDate;
		$returnData['matchIDs'] = $data;
		
		return $returnData;
	}
	
	// invalid or missing input, returning null
	return null;
} // end method getURFMatchIDs

function getRankedStats($region = '', $playerID = null)
{
	if($region != '' && $playerID != null)
	{	
		$url = 'https://na.api.pvp.net/api/lol/' . $region . '/v1.3/stats/by-summoner/' . $playerID . '/ranked?season=SEASON2015&api_key=' . getAPIKey();
		
		//echo 'Getting contents of url: <a href="' . $url . '">' . $url . '</a><br /><br />';
		
		return getJSONObject($url);
	}
	
	// need a name, returning null
	return null;
}

//https://na.api.pvp.net/api/lol/na/v2.2/match/1780698745?includeTimeline=true&api_key=e6883f84-4171-4226-aea0-391ed716b1a9
function getMatchData($region = '', $matchID = null)
{
	if($region != '' && $matchID != null)
	{	
		$incTimeline = 'true';
	
		$url = 'https://na.api.pvp.net/api/lol/' . $region . '/v2.2/match/' . $matchID . '?includeTimeline=' . $incTimeline . '&api_key=' . getAPIKey();
		
		//echo 'Getting contents of url: <a href="' . $url . '">' . $url . '</a><br /><br />';
		
		return getJSONObject($url);
	}
	
	// need a name, returning null
	return null;
}


//https://na.api.pvp.net/api/lol/na/v1.3/game/by-summoner/333995/recent?api_key=e6883f84-4171-4226-aea0-391ed716b1a9
function getRecentGamesForSummoner($region = '', $summonerID = null)
{
	if($region != '' && $summonerID != null)
	{	
		$url = 'https://na.api.pvp.net/api/lol/' . $region . '/v1.3/game/by-summoner/' . $summonerID . '/recent?&api_key=' . getAPIKey();
		
		//echo 'Getting contents of url: <a href="' . $url . '">' . $url . '</a><br /><br />';
		
		return getJSONObject($url);
	}
	
	// need a name, returning null
	return null;
}


//https://global.api.pvp.net/api/lol/static-data/na/v1.2/champion?api_key=e6883f84-4171-4226-aea0-391ed716b1a9
function getStaticChampionData()
{
	$url = 'https://global.api.pvp.net/api/lol/static-data/na/v1.2/champion?api_key=' . getAPIKey();
	
	//echo 'Getting contents of url: <a href="' . $url . '">' . $url . '</a><br /><br />';
	
	return getJSONObject($url);
}

function getChampionPHPArray()
{
	// we're reformatting the static champion data to a PHP array with the champion ID
	// as the index of the array
	
	// but first, let me take a selfie
	// no, we're checking if the global variable is already filled
	global $championData;
	
	if(!empty($championData))
	{
		return $championData;
	}
	// otherwise, grab the static champion data and store it in the global variable
	else
	{
		$staticChampionData = getStaticChampionData();
		
		//error_log('In getChampionPHPArray. staticChampionData: ' . print_r($staticChampionData, true));
		
		// if the service is unavailable (ugh), return null
		if((array_key_exists('status', $staticChampionData) 
			&& array_key_exists('status_code', $staticChampionData)
			&& $staticChampionData['status_code'] == '503')
			|| !array_key_exists('data', $staticChampionData)
			)
		{
			return null;
		}
		
		$champions = array();
		
		foreach($staticChampionData['data'] as $champion)
		{
			$champions[$champion['id']] = $champion;
		}
		
		$championData = $champions;
		
		return $champions;
	}
	
}

function getMatchSubTypeDescriptions()
{
	$desc = array();
	
	//'NORMAL', 'BOT', 'RANKED_SOLO_5x5', 'RANKED_TEAM_5x5', 'CAP_5x5', 'SR_6x6', 'URF', 'URF_BOT'
	
	$desc['NORMAL'] = 'Normal';
	$desc['BOT'] = 'Normal Co-op vs. AI';
	$desc['RANKED_SOLO_5x5'] = 'Ranked Solo Queue';
	$desc['RANKED_TEAM_5x5'] = 'Ranked Team';
	$desc['CAP_5x5'] = 'Team Builder';
	$desc['SR_6x6'] = 'Hexakill';
	$desc['URF'] = 'Ultra Rapid Fire';
	$desc['URF_BOT'] = 'Ultra Rapid Fire Co-op vs. AI';
	
	return $desc;
}


function getTimelineCoordsForPlayer($matchData, $participantNum = 0)
{
	// we want to return a string of positions similar to:
	// [561 ,581], [561 ,361], [351 ,293], [222 ,471], [311 ,649] ...
	
	// check for a valid participant number, and not null match data
	if($matchData != null && isInteger($participantNum) && $participantNum > 0 && $participantNum <= 10)
	{
		$coords = '';
		
		// loop through each timeline frame in the match
		foreach($matchData['timeline']['frames'] as $frame)
		{
			if(!empty($frame['participantFrames'][$participantNum]['position']))
			{
				$coords .= '[' . $frame['participantFrames'][$participantNum]['position']['x'] . ', ' . $frame['participantFrames'][$participantNum]['position']['y'] . '],';
			}
		}
		
		// get rid of the extra comma at the end
		return rtrim($coords, ',');
	}
	
	// not valid input, returning null
	return null;
}


function getParticipantsMatchData($matchData)
{
	// we want to return an array of coord arrays, using the participant id as the index
	
	// check for a valid participant number, and not null match data
	if($matchData != null)
	{
		$coords = array();
		$eventStats = array();
		$frameTimestamps = array();
		$teamStats = array();
		$participantTeamIDs = array();
		$teamObjectiveTotals = array('100' => array(), '200' => array());
		$teamObjectiveDefaults = array('teamDragons' => 0, 'teamBarons' => 0, 'teamTurrets' => 0, 'teamInhibitors' => 0);
		
		// initialize the teamStats array with default values
		$teamStatsDefaults = array('teamKills' => 0, 'teamDeaths' => 0, 'teamAssists' => 0, 'teamTotalGold' => 0, 'teamDragons' => 0, 'teamBarons' => 0, 'teamTurrets' => 0, 'teamInhibitors' => 0);
		$teamStats['100'] = array();
		$teamStats['200'] = array();
		
		// create an array that will hold all the changes we want to track from events in each frame
		$eventStatsDefault = array();
		$eventStatsModifiers = array();
		
		// initialize the event stats array for each participant
		// and create a participant to team ID map array
		foreach($matchData['participants'] as $part)
		{
			$partID = $part['participantId'];
			
			// default values for all the event stats we want to track
			$eventStatsDefault[$partID]['champKills'] = 0;
			$eventStatsDefault[$partID]['champDeaths'] = 0;
			$eventStatsDefault[$partID]['champAssists'] = 0;
			
			$participantTeamIDs[$partID] = $part['teamId'];
		}
		
		//error_log('In getParticipantsMatchData. eventStatsDefault: ' . print_r($eventStatsDefault, true));
		
		// loop through each timeline frame in the match
		foreach($matchData['timeline']['frames'] as $frameID => $frame)
		{
			// if we are not on the first frame, bring previous objective values forward
			if($frameID != 0)
			{
				$teamStats['100'][$frameID]['teamDragons'] = $teamStats['100'][$frameID - 1]['teamDragons'];
				$teamStats['100'][$frameID]['teamBarons'] = $teamStats['100'][$frameID - 1]['teamBarons'];
				$teamStats['100'][$frameID]['teamTurrets'] = $teamStats['100'][$frameID - 1]['teamTurrets'];
				$teamStats['100'][$frameID]['teamInhibitors'] = $teamStats['100'][$frameID - 1]['teamInhibitors'];
				
				$teamStats['200'][$frameID]['teamDragons'] = $teamStats['200'][$frameID - 1]['teamDragons'];
				$teamStats['200'][$frameID]['teamBarons'] = $teamStats['200'][$frameID - 1]['teamBarons'];
				$teamStats['200'][$frameID]['teamTurrets'] = $teamStats['200'][$frameID - 1]['teamTurrets'];
				$teamStats['200'][$frameID]['teamInhibitors'] = $teamStats['200'][$frameID - 1]['teamInhibitors'];
			}
			// otherwise start at 0
			else
			{
				// start off the objectives values at 0
				$teamStats['100'][$frameID]['teamDragons'] = 0;
				$teamStats['100'][$frameID]['teamBarons'] = 0;
				$teamStats['100'][$frameID]['teamTurrets'] = 0;
				$teamStats['100'][$frameID]['teamInhibitors'] = 0;
				
				$teamStats['200'][$frameID]['teamDragons'] = 0;
				$teamStats['200'][$frameID]['teamBarons'] = 0;
				$teamStats['200'][$frameID]['teamTurrets'] = 0;
				$teamStats['200'][$frameID]['teamInhibitors'] = 0;
			}
		
			if(array_key_exists('timestamp', $frame))
			{
				// get the timestamp for the frame
				$frameTimestamps[$frameID] = $frame['timestamp'];
			}
		
			if(array_key_exists('participantFrames', $frame))
			{
				// loop through each participant in this frame
				foreach($frame['participantFrames'] as $part)
				{
					$partID = $part['participantId'];
					
					// get the champion's coordinates for this frame
					if(!empty($part['position']))
					{
						$coords[$partID][$frameID] = $part['position'];
					}
					
					// get the rest of the champion data for this frame
					$eventStats[$partID][$frameID]['champLevel'] = $frame['participantFrames'][$partID]['level'];
					$eventStats[$partID][$frameID]['totalGold'] = $frame['participantFrames'][$partID]['totalGold'];
					$eventStats[$partID][$frameID]['totalMinions'] = intval($frame['participantFrames'][$partID]['minionsKilled']) + intval($frame['participantFrames'][$partID]['jungleMinionsKilled']);
				}
			}
			
			if(array_key_exists('events', $frame))
			{
				// reset the event stats modifiers array to default values
				$eventStatsModifiers = $eventStatsDefault;
			
				// loop through each of the events that happened this frame
				foreach($frame['events'] as $eventID => $event)
				{
					// handle each event in the required way
					switch ($event['eventType'])
					{
						case 'CHAMPION_KILL':
							// add to the killer's kills and the victim's deaths, plus assists
							// if the killerId = 0, the champion was executed so no one got a kill from it
							if($event['killerId'] != 0)
							{
								$eventStatsModifiers[$event['killerId']]['champKills']++;
							}
							$eventStatsModifiers[$event['victimId']]['champDeaths']++;
							
							// dem assists
							if(array_key_exists('assistingParticipantIds', $event))
							{
								foreach($event['assistingParticipantIds'] as $assistPartID)
								{
									$eventStatsModifiers[$assistPartID]['champAssists']++;
								}
							}
							break;
						case 'ELITE_MONSTER_KILL':
							// checking for Dragon or Baron kills here
							if(array_key_exists('monsterType', $event))
							{
								if($event['monsterType'] == 'DRAGON')
								{
									if($event['killerId'] != 0)
									{
										$teamIDCalc = $participantTeamIDs[$event['killerId']];
									}
									else
									{
										// killerId is 0 (meaning a minion killer, I assume) so get the teamId directly
										if($event['killerId'] == 0 && $event['teamId'] == '100')
										{
											// the teamId here is for the team that owned the building
											// so we want to increase the value for the team that destroyed it
											$teamIDCalc = '200';
										}
										else if($event['killerId'] == 0 && $event['teamId'] == '200')
										{
											$teamIDCalc = '100';
										}
									}
								
									$teamStats[$teamIDCalc][$frameID]['teamDragons']++;
								}
								else if($event['monsterType'] == 'BARON_NASHOR')
								{
									if($event['killerId'] != 0)
									{
										$teamIDCalc = $participantTeamIDs[$event['killerId']];
									}
									else
									{
										// killerId is 0 (meaning a minion killer, I assume) so get the teamId directly
										if($event['killerId'] == 0 && $event['teamId'] == '100')
										{
											// the teamId here is for the team that owned the building
											// so we want to increase the value for the team that destroyed it
											$teamIDCalc = '200';
										}
										else if($event['killerId'] == 0 && $event['teamId'] == '200')
										{
											$teamIDCalc = '100';
										}
									}
								
									$teamStats[$teamIDCalc][$frameID]['teamBarons']++;
								}
							}
							break;
						case 'BUILDING_KILL':
							// checking for turret or inhibitor kills
							if(array_key_exists('buildingType', $event))
							{
								if($event['buildingType'] == 'TOWER_BUILDING')
								{
									if($event['killerId'] != 0)
									{
										$teamIDCalc = $participantTeamIDs[$event['killerId']];
									}
									else
									{
										// killerId is 0 (meaning a minion killer, I assume) so get the teamId directly
										if($event['killerId'] == 0 && $event['teamId'] == '100')
										{
											// the teamId here is for the team that owned the building
											// so we want to increase the value for the team that destroyed it
											$teamIDCalc = '200';
										}
										else if($event['killerId'] == 0 && $event['teamId'] == '200')
										{
											$teamIDCalc = '100';
										}
									}
									
									$teamStats[$teamIDCalc][$frameID]['teamTurrets']++;
								}
								else if($event['buildingType'] == 'INHIBITOR_BUILDING')
								{
									if($event['killerId'] != 0)
									{
										$teamIDCalc = $participantTeamIDs[$event['killerId']];
									}
									else
									{
										// killerId is 0 (meaning a minion killer, I assume) so get the teamId directly
										if($event['killerId'] == 0 && $event['teamId'] == '100')
										{
											// the teamId here is for the team that owned the building
											// so we want to increase the value for the team that destroyed it
											$teamIDCalc = '200';
										}
										else if($event['killerId'] == 0 && $event['teamId'] == '200')
										{
											$teamIDCalc = '100';
										}
									}
									
									$teamStats[$teamIDCalc][$frameID]['teamInhibitors']++;
								}
							}
							break;
					}
				}
				
				// add the new values to the overall array that contains values for all frames
				foreach($matchData['participants'] as $part)
				{
					$partID = $part['participantId'];
					// adding the stat modifier to the previous frame's value
					// ie: adding any new kills to previous kills to have total kills up to this point
					$eventStats[$partID][$frameID]['champKills'] = $eventStats[$partID][$frameID - 1]['champKills'] + $eventStatsModifiers[$partID]['champKills'];
					$eventStats[$partID][$frameID]['champDeaths'] = $eventStats[$partID][$frameID - 1]['champDeaths'] + $eventStatsModifiers[$partID]['champDeaths'];
					$eventStats[$partID][$frameID]['champAssists'] = $eventStats[$partID][$frameID - 1]['champAssists'] + $eventStatsModifiers[$partID]['champAssists'];
				}
			}
			// otherwise there are no events this frame
			else
			{
				// if we're on the first frame, we'll start with the default values
				if($frameID == 0)
				{
					// initialize the event stats array for each participant
					foreach($matchData['participants'] as $part)
					{
						$partID = $part['participantId'];
						// default values for all the event stats we want to track
						// eventStats.participant->frame->value
						// this is only for data in the 'events' array of the frame 
						// (not for data in the participantFrames array)
						$eventStats[$partID][$frameID]['champKills'] = 0;
						$eventStats[$partID][$frameID]['champDeaths'] = 0;
						$eventStats[$partID][$frameID]['champAssists'] = 0;
						
					}
				}
				// otherwise I guess there's nothing to update this frame, so pull forward previous values?
				else
				{
					foreach($matchData['participants'] as $part)
					{
						$partID = $part['participantId'];
						// pull forward the previous frame's values
						$eventStats[$partID][$frameID]['champLevel'] = $eventStats[$partID][$frameID - 1]['champLevel'];
						$eventStats[$partID][$frameID]['champKills'] = $eventStats[$partID][$frameID - 1]['champKills'];
						$eventStats[$partID][$frameID]['champDeaths'] = $eventStats[$partID][$frameID - 1]['champDeaths'];
						$eventStats[$partID][$frameID]['champAssists'] = $eventStats[$partID][$frameID - 1]['champAssists'];
						$eventStats[$partID][$frameID]['totalGold'] = $eventStats[$partID][$frameID - 1]['totalGold'];
						$eventStats[$partID][$frameID]['totalMinions'] = $eventStats[$partID][$frameID - 1]['totalMinions'];
						
						// we should be able to add gold values in here...right?
					}
				}
			}
		}
		
		// now calculate the team data
		//error_log('In getParticipantsMatchData. teamStats: ' . print_r($teamStats, true));
		
		// loop through each timeline frame in the match
		foreach($matchData['timeline']['frames'] as $frameID => $frame)
		{
			// initialize all values to 0 for this frame, and then add
			// to that for each participant that has a value for the specific stat
			$teamStats['100'][$frameID]['teamLevels'] = 0;
			$teamStats['100'][$frameID]['teamKills'] = 0;
			$teamStats['100'][$frameID]['teamDeaths'] = 0;
			$teamStats['100'][$frameID]['teamAssists'] = 0;
			$teamStats['100'][$frameID]['teamTotalGold'] = 0;
			$teamStats['100'][$frameID]['teamMinions'] = 0;
			
			$teamStats['200'][$frameID]['teamLevels'] = 0;
			$teamStats['200'][$frameID]['teamKills'] = 0;
			$teamStats['200'][$frameID]['teamDeaths'] = 0;
			$teamStats['200'][$frameID]['teamAssists'] = 0;
			$teamStats['200'][$frameID]['teamTotalGold'] = 0;
			$teamStats['200'][$frameID]['teamMinions'] = 0;
		
			// loop through each participant to add all the values together
			foreach($matchData['participants'] as $part)
			{
				$partID = $part['participantId'];
				$teamID = $part['teamId'];
				
				// add the values for this participant to the values we already have for this frame
				$teamStats[$teamID][$frameID]['teamLevels'] += $eventStats[$partID][$frameID]['champLevel'];
				$teamStats[$teamID][$frameID]['teamKills'] += $eventStats[$partID][$frameID]['champKills'];
				$teamStats[$teamID][$frameID]['teamDeaths'] += $eventStats[$partID][$frameID]['champDeaths'];
				$teamStats[$teamID][$frameID]['teamAssists'] += $eventStats[$partID][$frameID]['champAssists'];
				$teamStats[$teamID][$frameID]['teamTotalGold'] += $eventStats[$partID][$frameID]['totalGold'];
				$teamStats[$teamID][$frameID]['teamMinions'] += $eventStats[$partID][$frameID]['totalMinions'];
			}
		}
		
		
		//error_log('In getParticipantsMatchData. coords: ' . print_r($coords, true));
		//error_log('In getParticipantsMatchData. teamStats: ' . print_r($teamStats, true));
		//error_log('In getParticipantsMatchData. matchData: ' . print_r($matchData, true));
		//error_log('In getParticipantsMatchData. participantTeamIDs: ' . print_r($participantTeamIDs, true));
		
		$retArray = array();
		
		$retArray['coords'] = $coords;
		$retArray['eventStats'] = $eventStats;
		$retArray['teamStats'] = $teamStats;
		$retArray['frameTimestamps'] = $frameTimestamps;
		
		
		
		//error_log('In getParticipantsMatchData. eventStats: ' . print_r($eventStats, true) . ', coords: ' . print_r($coords, true));
		//error_log('In getParticipantsMatchData. eventStats: ' . print_r($eventStats, true));
		
		//return $coords;
		return $retArray;
	}
	
	// not valid input, returning null
	return null;
}


function getParticipants($matchData)
{
	// check for a valid participant number, and not null match data
	if($matchData != null)
	{
		$champions = getChampionPHPArray();
		
		// if we didn't get static champion data, the service is down. Sadface
		if($champions == null)
		{
			return 'Service unavailable';
		}
		
		$participantsData = getParticipantsMatchData($matchData);
		
		$coords = $participantsData['coords'];
		$eventStats = $participantsData['eventStats'];
		$teamStatsData = $participantsData['teamStats'];
		$frameTimestamps = $participantsData['frameTimestamps'];
		
		//error_log('In getParticipants. champions: ' . print_r($champions, true));
		//error_log('In getParticipants. Returning participants: ' . print_r($teamStatsData, true));
	
		$participants = '';
		$timestamps = '';
		$leftTeamStats = '';
		$rightTeamStats = '';
	
		// calculate the strings for the timestamps of each frame
		foreach($frameTimestamps as $timestamp)
		{
			$timestamps .= '"' . getHrsAndMinsString($timestamp) . '", ';
		}
		
		$timestamps = rtrim($timestamps, ', ');
	
	
		// loop through each participant in the match
		foreach($matchData['participants'] as $participant)
		{
			$participants .= '{ ';
			
			// add all the info we have on the participant
			
			if(!empty($participant['participantId']))
			{
				$participants .= 'participantId:"' . $participant['participantId'] . '", ';
			}
			
			if(!empty($participant['teamId']))
			{
				$participants .= 'teamId:"' . $participant['teamId'] . '", ';
			}
			
			if(!empty($participant['championId']))
			{
				$participants .= 'championId:"' . $participant['championId'] . '", ';
				
				// if we got some champion data, we'll add it in here
				if($champions != null)
				{
					$champInfo = $champions[$participant['championId']];
					
					// grab the champion name, key, and title from the champion data
					$participants .= 'championKey:"' . $champInfo['key'] . '", ';
					$participants .= 'championName:"' . $champInfo['name'] . '", ';
					$participants .= 'championTitle:"' . $champInfo['title'] . '", ';
				}
				
			}
			
			if(!empty($participant['highestAchievedSeasonTier']))
			{
				$participants .= 'highestAchievedSeasonTier:"' . $participant['highestAchievedSeasonTier'] . '", ';
			}
			
			
			if(!empty($participant['participantId']))
			{
				$participants .= 'coords:['; 
				
				// get all the coords for this participant
				foreach($coords[$participant['participantId']] as $coord)
				{
					$participants .= '[' . $coord['x'] . ',' . $coord['y'] . '], ';
				}

				$participants = rtrim($participants, ', ');
				
				$participants .= '], ';
			}
			
			// get event data for the participant
			if(!empty($participant['participantId']))
			{
				// get all the data into variables first
				$levelData = $champKillData = $champDeathData = $champAssistData = $champTotalGoldData = $champTotalMinionsData = '';
				
				foreach($eventStats[$participant['participantId']] as $eventFrame)
				{
					$levelData .= $eventFrame['champLevel'] . ', ';
					$champKillData .= $eventFrame['champKills'] . ', ';
					$champDeathData .= $eventFrame['champDeaths'] . ', ';
					$champAssistData .= $eventFrame['champAssists'] . ', ';
					$champTotalGoldData .= $eventFrame['totalGold'] . ', ';
					$champTotalMinionsData .= $eventFrame['totalMinions'] . ', ';
				}
				
				// drop the ending commas
				$levelData = rtrim($levelData, ', ');
				$champKillData = rtrim($champKillData, ', ');
				$champDeathData = rtrim($champDeathData, ', ');
				$champAssistData = rtrim($champAssistData, ', ');
				$champTotalGoldData = rtrim($champTotalGoldData, ', ');
				$champTotalMinionsData = rtrim($champTotalMinionsData, ', ');
				
				// now use the variables to contruct javascript arrays				
				$participants .= 'champLevel:[' . $levelData . '], ';
				$participants .= 'champKills:[' . $champKillData . '], ';
				$participants .= 'champDeaths:[' . $champDeathData . '], ';
				$participants .= 'champAssists:[' . $champAssistData . '], ';
				$participants .= 'champTotalGold:[' . $champTotalGoldData . '], ';
				$participants .= 'champTotalMinions:[' . $champTotalMinionsData . '], ';
			}
			
			$participants = rtrim($participants, ', ');
			
			$participants .= ' }, 
';
		}
		
		//error_log('In getParticipants. Returning participants: ' . print_r($participants, true));
		
		// get rid of the extra comma at the end
		$participants = rtrim($participants, ', 
');

		//$teamStatsData = $participantsData['teamStats'];
		// loop through each team in the match
		foreach($teamStatsData as $teamID => $teamData)
		{
			$teamStats = '{ ';
			
			$teamLevelsData = $teamKillsData = $teamDeathsData = $teamAssistsData = $teamTotalGoldData = $teamMinionsData = $teamDragonsData = $teamBaronsData = $teamTurretsData = $teamInhibitorsData = '';
			
			// loop through all the frames
			foreach($teamData as $frameID => $frameStats)
			{
				// get all the data into variables first
				$teamLevelsData .= $frameStats['teamLevels'] . ', ';
				$teamKillsData .= $frameStats['teamKills'] . ', ';
				$teamDeathsData .= $frameStats['teamDeaths'] . ', ';
				$teamAssistsData .= $frameStats['teamAssists'] . ', ';
				$teamTotalGoldData .= $frameStats['teamTotalGold'] . ', ';
				$teamMinionsData .= $frameStats['teamMinions'] . ', ';
				$teamDragonsData .= $frameStats['teamDragons'] . ', ';
				$teamBaronsData .= $frameStats['teamBarons'] . ', ';
				$teamTurretsData .= $frameStats['teamTurrets'] . ', ';
				$teamInhibitorsData .= $frameStats['teamInhibitors'] . ', ';
			}
			
			// drop the ending commas
			$teamLevelsData = rtrim($teamLevelsData, ', ');
			$teamKillsData = rtrim($teamKillsData, ', ');
			$teamDeathsData = rtrim($teamDeathsData, ', ');
			$teamAssistsData = rtrim($teamAssistsData, ', ');
			$teamTotalGoldData = rtrim($teamTotalGoldData, ', ');
			$teamMinionsData = rtrim($teamMinionsData, ', ');
			$teamDragonsData = rtrim($teamDragonsData, ', ');
			$teamBaronsData = rtrim($teamBaronsData, ', ');
			$teamTurretsData = rtrim($teamTurretsData, ', ');
			$teamInhibitorsData = rtrim($teamInhibitorsData, ', ');
				
			// now use the variables to contruct javascript arrays				
			$teamStats .= 'teamLevels:[' . $teamLevelsData . '], ';
			$teamStats .= 'teamKills:[' . $teamKillsData . '], ';
			$teamStats .= 'teamDeaths:[' . $teamDeathsData . '], ';
			$teamStats .= 'teamAssists:[' . $teamAssistsData . '], ';
			$teamStats .= 'teamTotalGold:[' . $teamTotalGoldData . '], ';
			$teamStats .= 'teamMinions:[' . $teamMinionsData . '], ';
			$teamStats .= 'teamDragons:[' . $teamDragonsData . '], ';
			$teamStats .= 'teamBarons:[' . $teamBaronsData . '], ';
			$teamStats .= 'teamTurrets:[' . $teamTurretsData . '], ';
			$teamStats .= 'teamInhibitors:[' . $teamInhibitorsData . '], ';
			
			$teamStats = rtrim($teamStats, ', ');
			
			$teamStats .= ' }';
			
			// now put this team data in the correct team variable
			if($teamID == 100)
			{
				$leftTeamStats = $teamStats;
			}
			else if($teamID == 200)
			{
				$rightTeamStats = $teamStats;
			}
		}
		
		//error_log('In getteamStats. Returning teamStats: ' . print_r($teamStats, true));
		//error_log('In getteamStats. Returning leftTeamStats: ' . print_r($leftTeamStats, true) . ', rightTeamStats: ' . print_r($rightTeamStats, true));
		
		// get rid of the extra comma at the end
		$teamStats = rtrim($teamStats, ', 
');


		$retValue = array();
		
		$retValue['participants'] = $participants;
		$retValue['timestamps'] = $timestamps;
		$retValue['leftTeamStats'] = $leftTeamStats;
		$retValue['rightTeamStats'] = $rightTeamStats;
		

		return $retValue;
	}
	
	//error_log('In getParticipants. Match data is null!');
	
	// not valid input, returning null
	return null;
} // end function getParticipants


function getTimelineDataForAllPlayers($matchData)
{
	// we want to return a string of positions similar to:
	// [561 ,581], [561 ,361], [351 ,293], [222 ,471], [311 ,649] ...
	
	// check that we got some match data
	if($matchData != null)
	{
		// get the participants
		foreach($matchData['participantIdentities'] as $partID)
		{
			$participants[] = $partID['participantId'];
		}
		
		//error_log('Got participants: ' . print_r($participants, true));
		
		$retFrames = array();
		$currentFrame = 0;
		
		// loop through each timeline frame in the match
		foreach($matchData['timeline']['frames'] as $frame)
		{
			$retFrames[$currentFrame]['timestamp'] = $frame['timestamp'];
		
			// get the participant coords for this frame
			foreach($participants as $participant)
			{
				if(!empty($frame['participantFrames'][$participant]['position']))
				{
					$retFrames[$currentFrame][$participant] = $frame['participantFrames'][$participant]['position'];
				}
			}
			
			$currentFrame++;
		}

		//error_log('Got frames: ' . print_r($retFrames, true));
		
		return $retFrames;
	}
	
	// not valid input, returning null
	return null;
}


function getMapJS($matchData)
{
	$participantData = getParticipants($matchData);
	
	// if the service is unavailable, we'll pass the error up
	if($participantData['participants'] == 'Service unavailable')
	{
		return 'Service unavailable';
	}

	// using php to build javascript code to output... yeah.
	return '//<![CDATA[
	
var frameTimestamps = [
	' . $participantData['timestamps'] . '
];

var leftTeamStats = ' . $participantData['leftTeamStats'] . ';

var rightTeamStats = ' . $participantData['rightTeamStats'] . ';

var participants = [
	' . $participantData['participants'] . '
];

var currentTimelineStep = 0;
var miniMapWidth = 512;
var miniMapHeight = 512;
var miniMapImg = "img/minimap-ig.png";
var iconWidth = 24;
var iconHeight = 24;
var halfIconSize = 12;
// icon path example: "img/champion-icons/Thresh_Square_0_icon.png"
var championIconPathPt1 = "img/champion-icons/";
var championIconPathPt2 = "_Square_0_icon.png";
// full sized path example: "img/champions/Thresh_Square_0.png"
var championImgPathPt1 = "img/champions/";
var championImgPathPt2 = "_Square_0.png";
var highlightTime = 500;
var highlightColor = "#FFFC00";

var maxSteps = 0;
// store the milliseconds between steps
var stepSpeed = 1000;

var currentlyPlaying = false;

var playIconClass = "glyphicon glyphicon-play";
var pauseIconClass = "glyphicon glyphicon-pause";

if(frameTimestamps.length > 0)
{
	maxSteps = frameTimestamps.length - 1;
}	

window.onload=function(){
/////
// Timeline Data taken from the following match:
// http://matchhistory.na.leagueoflegends.com/en/#match-details/NA1/1653950767
/////

// the map starts out not visible, so make it visible now
document.getElementById("map-container").style.display = "block";

// Domain for the current Summoner\'s Rift on the in-game mini-map
var domain = {
            min: {x: -120, y: -120},
            max: {x: 14870, y: 14980}
    },
    xScale, yScale, svg;

xScale = d3.scale.linear()
  .domain([domain.min.x, domain.max.x])
  .range([0, miniMapWidth]);

yScale = d3.scale.linear()
  .domain([domain.min.y, domain.max.y])
  .range([miniMapHeight, 0]);
  

svg = d3.select("#map").append("svg:svg")
    .attr("width", miniMapWidth)
    .attr("height", miniMapHeight);


svg.append("image")
    .attr("xlink:href", miniMapImg)
    .attr("x", "0")
    .attr("y", "0")
    .attr("width", miniMapWidth)
    .attr("height", miniMapHeight);

// load the data for all of the participants
var i, curParticipant, participantId, championIcon, teamCssClass;

for(i = 0; i < participants.length; i++)
{
	// grab the current participant and calculate some values
	curParticipant = participants[i];
	participantId = "participant-" + curParticipant.participantId;
	championIcon = championIconPathPt1 + curParticipant.championKey + championIconPathPt2;
	teamCssClass = GetTeamCssFromId(curParticipant.teamId);
	
	if(teamCssClass == null)
	{
		teamCssClass = "";
	}
	
	var championData = [];
	// the coordinate that the champion should be at should always be in [0]
	// we\'re starting out with the first coordinate, hence the hardcoded 0
	championData[0] = curParticipant.coords[0];
	
	svg.append("svg:g").selectAll("image") // select the current participant
		.data(championData) // bind the data to thecurrent participant
		.enter() // gets any new data without elements associated (which is all of the data at this point)
		.append("svg:image") // add the new elements for the new data
			.attr("xlink:href", championIcon)
			.attr("x", function(d) { return xScale(d[0]) - halfIconSize }) // dynamically gets a scaled x position from bound data for this element
			.attr("y", function(d) { return yScale(d[1]) - halfIconSize }) // dynamically gets a scaled y position from bound data for this element (subtracting halfIconSize so the image is centered on the data point)
			.attr("width", iconWidth)
			.attr("height", iconHeight)
			.attr("id", participantId)
			.attr("class", "champion " + teamCssClass); // add the team css class to the element
		
}

document.getElementById("current-step").innerHTML = frameTimestamps[currentTimelineStep];

document.getElementById("play-replay").onclick = ToggleReplay;
document.getElementById("next-step").onclick = NextStep;
document.getElementById("prev-step").onclick = PrevStep;

// set up the team info area
setupTeamInfoHTML();


function GetTeamCssFromId(teamId)
{
	if(teamId != null)
	{
		if(teamId == "100")
		{
			return "blueteam";
		}
		else if(teamId == "200")
		{
			return "purpleteam";
		}
		else
		{
			return null;
		}
	}
	
	return null;
}


function ToggleReplay()
{
	
	if(currentlyPlaying == false)
	{
		// if we\'re on the last step, reset to the beginning
		if(currentTimelineStep == maxSteps)
		{
			// set the current step to -1 because we\'re going to be incrementing it with NextStep shortly
			currentTimelineStep = -1;
		}
	
		// start the replay playing from the current step
		// we\'ll call the first step, then kick off the replay loop
		NextStep();
		
		currentlyPlaying = true;
		
		// change the icon to the pause icon
		document.getElementById("btn-play").className = pauseIconClass;
		
		setTimeout(ReplayTick, stepSpeed);
	}
	else if(currentlyPlaying == true)
	{
		// change the icon back to the play icon
		document.getElementById("btn-play").className = playIconClass;
		
		// indicate we are no longer playing the replay 
		// so the next time ReplayTick is called, it will know to stop ticking
		currentlyPlaying = false;
	}
	
}

function ReplayTick()
{
	// if we\'re still playing the replay, go to the next step and continue the loop
	if(currentlyPlaying == true)
	{
		NextStep();
		
		// check if we hit the final step, and stop if so
		if(currentTimelineStep == maxSteps)
		{
			// we\'re done playing the replay
			currentlyPlaying = false;
			
			// change the icon back to the play icon
			document.getElementById("btn-play").className = playIconClass;
		}
		// otherwise, we keep going
		else
		{
			setTimeout(ReplayTick, stepSpeed);
		}		
	}
	else
	{
		currentlyPlaying = false;
	}
}


function NextStep()
{
	if(currentTimelineStep < maxSteps)
	{	
		// increase the current step to the next step
		currentTimelineStep++;
		
		// loop through all participants and move them
		SetStepForAllParticipants(currentTimelineStep);
		
		document.getElementById("current-step").innerHTML = frameTimestamps[currentTimelineStep];
	}
}

function PrevStep()
{
	if(currentTimelineStep - 1 >= 0)
	{	
		// increase the current step to the next step
		currentTimelineStep--;

		// loop through all participants and move them
		SetStepForAllParticipants(currentTimelineStep);
		
		document.getElementById("current-step").innerHTML = frameTimestamps[currentTimelineStep];
	}
}

function SetStepForAllParticipants(newStep)
{
	if(newStep != null && newStep <= maxSteps && newStep >= 0)
	{
		// update the data for each participant
		for(i = 0; i < participants.length; i++)
		{
			// grab the current participant and calculate some values
			curParticipant = participants[i];
			participantId = "participant-" + curParticipant.participantId;
			
			var championData = [];
			// the coordinate that the champion should be at should always be in [0]
			// grab the coordinates at the given step
			championData[0] = curParticipant.coords[newStep];
			
			svg.selectAll("image#" + participantId) // select the current participant
				.data(championData) // bind the data to the current participant
					.transition()
					.ease("linear") // linear, poly(k), quad, cubic, sin, exp, circle, elastic(a, p), back(s), bounce 
					.duration(stepSpeed)
					.attr("x", function(d) { return xScale(d[0]) - halfIconSize }) // dynamically gets a scaled x position from bound data for this element (subtracting halfIconSize so the image is centered on the data point)
					.attr("y", function(d) { return yScale(d[1]) - halfIconSize }) // dynamically gets a scaled y position from bound data for this element (subtracting halfIconSize so the image is centered on the data point)	
			
			// update all stats that are outside of the map for the given participant
			updateParticipantStats(newStep, curParticipant);
		}
		
		// update all team stats
		updateTeamStats(newStep);
	}
}

function updateParticipantStats(newStep, participant)
{
	var pID = participant.participantId;
	var eID = "";
	
	// update all stats that are outside of the map for the given participant
	// if the value changed, update and highlight it
	
	eID = "participant-" + pID + "-champ-level";
	if(document.getElementById(eID).innerHTML != participant.champLevel[newStep]) 
	{
		document.getElementById(eID).innerHTML = participant.champLevel[newStep];
		highlightElement(eID, highlightColor); 
	}
	
	eID = "participant-" + pID + "-champ-kills";
	if(document.getElementById(eID).innerHTML != participant.champKills[newStep]) 
	{
		document.getElementById(eID).innerHTML = participant.champKills[newStep];
		highlightElement(eID, highlightColor); 
	}
	
	eID = "participant-" + pID + "-champ-deaths";
	if(document.getElementById(eID).innerHTML != participant.champDeaths[newStep]) 
	{
		document.getElementById(eID).innerHTML = participant.champDeaths[newStep];
		highlightElement(eID, highlightColor); 
	}
	
	eID = "participant-" + pID + "-champ-assists";
	if(document.getElementById(eID).innerHTML != participant.champAssists[newStep]) 
	{
		document.getElementById(eID).innerHTML = participant.champAssists[newStep];
		highlightElement(eID, highlightColor); 
	}
	
	eID = "participant-" + pID + "-total-gold";
	if(document.getElementById(eID).innerHTML != participant.champTotalGold[newStep]) 
	{
		document.getElementById(eID).innerHTML = participant.champTotalGold[newStep];
		// not going to highlight total gold
		//highlightElement(eID, highlightColor); 
	}
	
	eID = "participant-" + pID + "-total-minions";
	if(document.getElementById(eID).innerHTML != participant.champTotalMinions[newStep]) 
	{
		document.getElementById(eID).innerHTML = participant.champTotalMinions[newStep];
		highlightElement(eID, highlightColor); 
	}
}

function updateTeamStats(newStep)
{
	var eID = "";
	var newVal = "";
	var teamSides = ["left", "right"];
	var side = "";
	var teamData = "";
	
	// loop through each side
	for(i = 0; i < teamSides.length; i++)
	{
		side = teamSides[i];
		
		if(side == "left")
		{
			teamData = leftTeamStats;
		}
		else if(side == "right")
		{
			teamData = rightTeamStats;
		}
		
		// update the value if we need to (not going to highlight these elements on change)
		eID = side + "-team-stats-kills";
		newVal = teamData.teamKills[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-deaths";
		newVal = teamData.teamDeaths[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-assists";
		newVal = teamData.teamAssists[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-levels";
		newVal = teamData.teamLevels[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-turret-kills";
		newVal = teamData.teamTurrets[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-inhibitor-kills";
		newVal = teamData.teamInhibitors[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-gold";
		newVal = teamData.teamTotalGold[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-dragon-kills";
		newVal = teamData.teamDragons[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-baron-kills";
		newVal = teamData.teamBarons[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
		eID = side + "-team-stats-minions";
		newVal = teamData.teamMinions[newStep];
		if(document.getElementById(eID).innerHTML != newVal) 
		{
			document.getElementById(eID).innerHTML = newVal;
		}
		
	}

}

function highlightElement(elementID, highlightingColor)
{
	$("#" + elementID).effect("highlight", { color: highlightingColor }, highlightTime);
}

} // end onload function

function setupTeamInfoHTML()
{
	// we\'re going to set up all the html for the team info
	var cellClass = "team-data-cell div-cell";
	
	// first grab the containers for the team data
	var leftTeam = document.getElementById("team-table-left");
	var rightTeam = document.getElementById("team-table-right");
	
	// create header rows for each side
	var leftHeaderRow = document.createElement("div");
	leftHeaderRow.className = "team-data-row header-row div-row";
	
	var rightHeaderRow = document.createElement("div");
	rightHeaderRow.className = "team-data-row header-row div-row";
	
	var champImgHeaderCell = document.createElement("div");
	champImgHeaderCell.className = cellClass + " champ-img-header-cell";
	champImgHeaderCell.innerHTML = "Champion";
	
	
	// now add the cells to the headers for each side
	// left
	leftHeaderRow.appendChild(champImgHeaderCell);
	
	leftTeam.appendChild(leftHeaderRow);
	
	// right
	rightHeaderRow.appendChild(champImgHeaderCell);
	
	rightTeam.appendChild(leftHeaderRow);
	
	
	// loop through the data for each participant
	for(i = 0; i < participants.length; i++)
	{
		var participant = participants[i];
		var pID = participant.participantId;
		
		// create a row for the participant
		var row = document.createElement("div");
		row.className = "team-data-row div-row";
		row.id = "participant-" + pID + "-row";
		
		// create all the fields
		
		// champion image
		var champImgCell = document.createElement("div");
		champImgCell.className = cellClass;
		champImgCell.id = "participant-" + pID + "-champ-img-cell";
		// img element inside the cell
		var champImg = document.createElement("img");
		champImg.id = "participant-" + pID+ "-champ-img";
		champImg.className = "champ-portrait";
		champImg.src = championImgPathPt1 + participant.championKey + championImgPathPt2;
		
		// add the champion image to the cell and then the cell to the row
		champImgCell.appendChild(champImg);
		
		// champion level
		var curChampLevelCell = document.createElement("div");
		curChampLevelCell.className = cellClass;
		curChampLevelCell.id = "participant-" + pID + "-champ-level-cell";
		// span element inside the cell
		var curChampLevel = document.createElement("span");
		curChampLevel.id = "participant-" + pID + "-champ-level";
		curChampLevel.innerHTML = "0";
		
		// add the current gold span to the cell
		curChampLevelCell.appendChild(curChampLevel);
		
		// kda
		var kdaCell = document.createElement("div");
		kdaCell.className = cellClass;
		kdaCell.id = "participant-" + pID + "-kda-cell";
		// kda elements inside the cell
		var champKills = document.createElement("span");
		champKills.id = "participant-" + pID + "-champ-kills";
		champKills.innerHTML = "0";
		var champDeaths = document.createElement("span");
		champDeaths.id = "participant-" + pID + "-champ-deaths";
		champDeaths.innerHTML = "0";
		var champAssists = document.createElement("span");
		champAssists.id = "participant-" + pID + "-champ-assists";
		champAssists.innerHTML = "0";
		
		// these slashNode are a bit of a hack
		var slashNode1 = document.createElement("span");
		slashNode1.innerHTML = "/";
		var slashNode2 = document.createElement("span");
		slashNode2.innerHTML = "/";
		
		// add the kda spans to the cell
		kdaCell.appendChild(champKills);
		kdaCell.appendChild(slashNode1);
		kdaCell.appendChild(champDeaths);
		kdaCell.appendChild(slashNode2);
		kdaCell.appendChild(champAssists);
		
		// total gold
		var totalGoldCell = document.createElement("div");
		totalGoldCell.className = cellClass;
		totalGoldCell.id = "participant-" + pID + "-total-gold-cell";
		// span element inside the cell
		var totalGold = document.createElement("span");
		totalGold.id = "participant-" + pID + "-total-gold";
		totalGold.innerHTML = "0";
		
		// add the total gold span to the cell
		totalGoldCell.appendChild(totalGold);
		
		// total minions
		var totalMinionsCell = document.createElement("div");
		totalMinionsCell.className = cellClass;
		totalMinionsCell.id = "participant-" + pID + "-total-minions-cell";
		// span element inside the cell
		var totalMinions = document.createElement("span");
		totalMinions.id = "participant-" + pID + "-total-minions";
		totalMinions.innerHTML = "0";
		
		// add the total minions span to the cell
		totalMinionsCell.appendChild(totalMinions);
		
		// now add it to the team data html
		// figure out which side to put the participant on based on their team
		// the team also determines the order that we add the elements (each side is a mirror of the other)
		if(participant.teamId == "100")
		{
			// left side
			row.appendChild(champImgCell);
			row.appendChild(curChampLevelCell);
			row.appendChild(kdaCell);
			row.appendChild(totalGoldCell);
			row.appendChild(totalMinionsCell);
			
		
			leftTeam.appendChild(row);
		}
		else if(participant.teamId == "200")
		{
			// right side
			
			
			row.appendChild(totalMinionsCell);
			row.appendChild(totalGoldCell);
			row.appendChild(kdaCell);
			row.appendChild(curChampLevelCell);
			row.appendChild(champImgCell);
			
			rightTeam.appendChild(row);
		}
	}
}


function updateParticipantStats()
{
	// here we update all the participant stats based on the current frame
	
	// loop through each participant
	for(i = 0; i < participants.length; i++)
	{
		var participant = participants[i];
		var participantID = participant.participantId;
		var frameData = participant.frameData;
		
		// kda spans
		champKills = document.getElementById("participant-" + participantID + "-champ-kills");
		if(champKills.innerHTML != frameData[currentTimelineStep].champKills)
		{
			champKills.innerHTML = frameData[currentTimelineStep].champKills;
		}
	}
}

//]]>';
}


function getMatchDataTest()
{
	return getMatchData(REGION_NA, 1780698745);
}

function getMatchHistoryForSummoner($region = '', $summonerID = '')
{
	if($summonerID != '' && $region != '')
	{
		$recentGames = getRecentGamesForSummoner($region, $summonerID);
		
		//error_log('In getMostRecentMatchForSummoner. recentGames: ' . print_r($recentGames, true));
	}
}

function getEpochTimeFromDate($dateString)
{
	// dateString should be in the a similar format to: 2015/4/2 16:00:00 UTC
	$dateTime = new DateTime($dateString);
	
	return $dateTime->format('U');
} // end method getEpochTiemFromDate

function getDateFromEpochTime($epochTime)
{
	// such a hack - get the first 10 characters of the milliseconds (because it was too big for an int value?)
	$epochTimeSeconds = intval(substr($epochTime, 0, 10));
	
	//error_log('In getDateFromEpochTime. epochTime: ' . $epochTime . ', epochTimeSeconds: ' . $epochTimeSeconds . ', intval: ' . intval($epochTime));
	
	$dateTime = new DateTime();
	$dateTime->setTimestamp($epochTimeSeconds);
	
	//return date('Y/m/d', $epochTimeSeconds);
	return $dateTime->format('Y/m/d');
} // end method getEpochTiemFromDate


function getHrsAndMinsString($time)
{	
	$milliSec = $time % 1000;
	$time = floor($time / 1000);

	$seconds = $time % 60;
	$minutes = floor($time / 60);
	
	return $minutes . ':' . str_pad($seconds, 2, "0", STR_PAD_LEFT);
}

function getJSONObject($url)
{
	//error_log('Getting URL contents for: [' . $url . ']');

	$leagueData = getUrlContents($url);
	
	if($leagueData)
	{
		//error_log('Got League data: [' . print_r($leagueData, true) . ']');
	
		return json_decode($leagueData, true);
	}
	
	return null;
}

function getUrlContents($url)
{
	$curl = curl_init();
	$timeout = 5;
	curl_setopt ($curl, CURLOPT_URL,$url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($curl);
	curl_close($curl);
	
	return $data;
}

function isInteger($input)
{
    return(ctype_digit(strval($input)));
}

?>