<?php
    header('Content-type:text/json');

    require_once('inc/db.php');
    $ini = parse_ini_file('inc/config.ini');
    $key = $ini['steamkey'];
    $result = $db->query("SELECT * FROM vaclist", PDO::FETCH_ASSOC);
    $ids = "";
    $data = array();
    $userdata = array();
    $bandata = array();

    if ($result->rowCount()){
        foreach($result as $row) {
            $ids .= $row['steamid'] . ",";
            $listed = $row['listed'];
            $data[$row['steamid']] = $row;
        }

        $banurl = "http://api.steampowered.com/ISteamUser/GetPlayerBans/v1//?key=$key&steamids=[$ids]&format=json";
        $userurl = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$key&steamids=[$ids]&format=json";

        $banurl = file_get_contents("$banurl");
        $banjson = json_decode($banurl, true);

        $userurl = file_get_contents("$userurl");
        $userjson = json_decode($userurl, true);

        for ($i=0; $i < $result->rowCount(); $i++) {
            $bandata[$banjson['players'][$i]['SteamId']] = $banjson['players'][$i];
            $userdata[$userjson['response']['players'][$i]['steamid']] = $userjson['response']['players'][$i];
        }

        for ($i=0; $i < $result->rowCount(); $i++) { 
            $id = $userjson['response']['players'][$i]['steamid'];

            // USER INFO
            $data[$id]['user']['personaname']                                                            =           $userdata[$id]['personaname'];
            $data[$id]['user']['communityvisibilitystate']                                               =           $userdata[$id]['communityvisibilitystate'];
            $data[$id]['user']['profilestate']                                                           =           $userdata[$id]['profilestate'];
            $data[$id]['user']['profileurl']                                                             =           $userdata[$id]['profileurl'];
            $data[$id]['user']['avatar']                                                                 =           $userdata[$id]['avatar'];
            $data[$id]['user']['avatarmedium']                                                           =           $userdata[$id]['avatarmedium'];
            $data[$id]['user']['avatarfull']                                                             =           $userdata[$id]['avatarfull'];
            $data[$id]['user']['avatarhash']                                                             =           $userdata[$id]['avatarhash'];
            $data[$id]['user']['personastate']                                                           =           $userdata[$id]['personastate'];
            
            if (!empty($userdata[$id]['timecreated'])) $data[$id]['user']['timecreated']                 =           $userdata[$id]['timecreated'];
            if (!empty($userdata[$id]['primaryclanid'])) $data[$id]['user']['primaryclanid']             =           $userdata[$id]['primaryclanid'];
            if (!empty($userdata[$id]['personastateflags'])) $data[$id]['user']['personastateflags']     =           $userdata[$id]['personastateflags'];
            if (!empty($userdata[$id]['loccountrycode'])) $data[$id]['user']['loccountrycode']           =           $userdata[$id]['loccountrycode'];
            if (!empty($userdata[$id]['locstatecode'])) $data[$id]['user']['locstatecode']               =           $userdata[$id]['locstatecode'];
            if (!empty($userdata[$id]['commentpermission'])) $data[$id]['user']['commentpermission']     =           $userdata[$id]['commentpermission'];

            // BAN INFO
            $data[$id]['ban']['VACBanned']                                                               =           $bandata[$id]['VACBanned'];
            $data[$id]['ban']['CommunityBanned']                                                         =           $bandata[$id]['CommunityBanned'];
            $data[$id]['ban']['NumberOfVACBans']                                                         =           $bandata[$id]['NumberOfVACBans'];
            $data[$id]['ban']['DaysSinceLastBan']                                                        =           $bandata[$id]['DaysSinceLastBan'];
            $data[$id]['ban']['NumberOfGameBans']                                                        =           $bandata[$id]['NumberOfGameBans'];
            $data[$id]['ban']['EconomyBan']                                                              =           $bandata[$id]['EconomyBan'];
        }
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
?>