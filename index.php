<?php
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

        for ($i=0; $i < count($banjson['players']); $i++) { 
            $bandata[$banjson['players'][$i]['SteamId']] = $banjson['players'][$i];
        }
    
        $userurl = file_get_contents("$userurl");
        $userjson = json_decode($userurl, true);

        for ($i=0; $i < count($userjson['response']['players']); $i++) { 
            $userdata[$userjson['response']['players'][$i]['steamid']] = $userjson['response']['players'][$i];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VAC LIST</title>
    </head>

    <body>
        <div class="container">
            <div class="row">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Steam 64 ID</th>
                            <th scope="col">Nick</th>
                            <th scope="col">VAC Status</th>
                            <th scope="col">Listed</th>
                            <th scope="col" data-sorter="shortDate" data-date-format="yyyymmdd">Listing Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            if ($result->rowCount()){
                                for ($i=0; $i < $result->rowCount() ; $i++) {
                                    $id = $userjson['response']['players'][$i]['steamid'];
                                    $name = $userdata[$id]['personaname'];
                                    $avatar = $userdata[$id]['avatarfull'];
                                    $vac = ($bandata[$id]['VACBanned']) ? "<font color='#FF0000'>BANNED</font>" : "<font color='#008000'>CLEAR</font>";
                                    $listed = $data[$id]['listed'];
                                    $date = $data[$id]['date'];
                                    $datespan = date( 'Ymd', strtotime($date));
        
                                    $row = "<tr>
                                    <th scope='row' width='40'><img src='$avatar' width='32' height='32'></th>
                                        <td><a href='https://steamcommunity.com/profiles/$id' target='_blank'>$id</a></td>
                                        <td>$name</td>
                                        <td><b>$vac</b></td>
                                        <td>$listed</td>
                                        <td>$date</td>
                                    </tr>
                                    <tr>";

                                    echo $row;
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="container bg-light text-center text-lg-start">
            <div class="text-center p-3">
                <span>© <?php 
                    $copyYear = 2021; 
                    $curYear = date('Y'); 
                    echo $copyYear . (($copyYear != $curYear) ? '-' . $curYear : '');
                    ?></span>
                <a class="text-primary" href="https://github.com/sametgirginer">GitHub</a>
                <a class="text-primary" href="json.php">JSON</a>
            </div>
        </footer>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.3/js/jquery.tablesorter.min.js"></script>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>

        <script>
            $(document).ready(function() { 
                $("table").tablesorter();
            });
        </script>
    </body>
</html>