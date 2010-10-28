<?php 
class OpenLigaDB
{
    function getNextMatch($client, $league) {
    
        $params = array(
        'leagueShortcut'=>$league
        );
        
        try {
        
            $result = $client->GetNextMatch($params);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting NextMatch<hr>Prams:<br>".implode('<li>', $params);
            
        }
        
        return $result;
        
    }

    
    function getLastMatch($client, $league) {
    
        $params = array(
        'leagueShortcut'=>$league
        );
        
        try {
        
            $result = $client->GetLastMatch($params);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting LastMatch<hr>Prams:<br>".implode('<li>', $params);
            
        }
        
        return $result;
        
    }

    
    function GetAvailGroups($client, $league, $leagueSaison) {
    
        $params = array(
        'leagueShortcut'=>$league,
        'leagueSaison'=>$leagueSaison
        );
        
        try {
        
            $result = $client->GetAvailGroups($params);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting GetAvailGroups<hr>Prams:<br>".implode('<li>', $params).$fault;
            
        }
        
        return $result;
        
    }

    
    function GetCurrentGroupOrderID($client, $league) {
    
        $params = array(
        'leagueShortcut'=>$league
        );
        
        try {
        
            $result = $client->GetCurrentGroupOrderID($params);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting GroupOrderID<hr>Prams:<li>".implode('<li>', $params).'<hr>'.$fault;
            
        }
        
        return $result;
        
    }

    
    function GetAvailLeaguesBySports($client, $sportID = false) {
    
        $params = array(
        'sportID'=>$sportID
        );
        
        try {
        
            $result = $client->GetAvailLeaguesBySports($params);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting LastMatch<hr>Prams:<br>".implode('<li>', $params);
            
        }
        
        return $result;
        
    }

    
    function GetAvailSports($client) {
    
        try {
        
            $result = $client->GetAvailSports();
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting LastMatch";
            
        }
        
        return $result;
        
    }

    
    function getMatchdataByGroupLeagueSaison($client, $groupOrderID = 0, $leagueSaison, $league) {
    
        if (!(int) $groupOrderID)
            $groupOrderID = OpenLigaDbClass::GetCurrentGroupOrderID($client, $league)->GetCurrentGroupOrderIDResult;
            
        $param = array(
        'groupOrderID'=>$groupOrderID,
        'leagueShortcut'=>$league,
        'leagueSaison'=>$leagueSaison
        );
        
        //echo"<hr>";print_r($param); echo"<hr>";
        
        try {
        
            $result = $client->GetMatchdataByGroupLeagueSaison($param);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting MatchdataByGroupLeagueSaison<hr>Prams:<br>".implode('<li>', $param);
            
        }
        
        return $result;
        
    }

    
    function GetAvailLeagues($client) {
    
        try {
        
            $result = $client->GetAvailLeagues();
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting GetAvailLeagues";
            
        }
        
        return $result;
        
    }

    
    function GetMatchdataByGroupLeagueSaisonJSON($client, $leagueSaison, $league, $groupOrderID = 0) {
    
        if (!(int) $groupOrderID)
            $groupOrderID = OpenLigaDbClass::GetCurrentGroupOrderID($client, $league)->GetCurrentGroupOrderIDResult;
            
        $params = array(
        'leagueShortcut'=>$league,
        'groupOrderID'=>$groupOrderID,
        'leagueSaison'=>$leagueSaison
        );
        
        try {
        
            $result = $client->GetMatchdataByGroupLeagueSaisonJSON($params);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting GetTeamsByLeagueSaison<hr>Prams:<br>".implode('<li>', $params);
            
        }
        
        return $result;
        
    }

    
    function GetTeamsByLeagueSaison($client, $leagueSaison, $league) {
    
        $params = array(
        'leagueShortcut'=>$league,
        'leagueSaison'=>$leagueSaison
        );
        
        try {
        
            $result = $client->GetTeamsByLeagueSaison($params);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting GetTeamsByLeagueSaison<hr>Prams:<li>".implode('<li>', $params).'<hr>'.$fault;
            
        }
        
        return $result;
        
    }

    
    function GetMatchdataByLeagueSaison($client, $leagueSaison, $league, $groupOrderID = 0) {
    
        $params = array(
        'leagueShortcut'=>$league,
        'leagueSaison'=>$leagueSaison
        );
        
        //print_r($params);
        
        try {
        
            $result = $client->GetMatchdataByLeagueSaison($params);
            
        }
        catch(SoapFault $fault) {
        
            $result = "Error getting GetTeamsByLeagueSaison<hr>Prams:<li>".implode('<li>', $params).'<hr>'.$fault;
            
        }
        
        return $result;
        
    }

    
    function GetMatchdataByLeagueDateTime($client, $league, $fromDateTime = 0, $toDateTime = 0) {
    
        if ((int) $fromDateTime < mktime(1, 1, 1, 1, 1, 1970))
            $fromDateTime = mktime(0, 0, 0, date("m"), date("d") - 8, date("Y"));
            
        if ((int) $toDateTime < $fromDateTime)
            $toDateTime = mktime(0, 0, 0, date("m"), date("d") + 8, date("Y"));
            
        // saving the Time for later usage
        
        $this->fromDateTime = $fromDateTime;
        
        $this->toDateTime = $toDateTime;

        
        $params = array(
        'fromDateTime'=>$fromDateTime,
        'leagueShortcut'=>$league,
        'toDateTime'=>$toDateTime
        );
        
        try {
        
            $result = $client->GetMatchdataByLeagueDateTime($params);

            
        }
        catch(SoapFault $fault) {

        
            $result = "Error getting MatchdataByGroupLeagueSaison<hr>Prams:<br>".implode('<li>', $params);
            
        }

        
        return $result;
        
    }
    
}
?>
