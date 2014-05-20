<?php
/**
    This file is part of BeeminderPublicData.

    BeeminderPublicData is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BeeminderPublicData is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with BeeminderPublicData.  If not, see <http://www.gnu.org/licenses/>.
**/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</head>
<body>
    <p>I have stuff to do. These are some of those things.</p>
    <?php
        # FIXME: fetching from a url should be done in a cronjob
        $urlfmt = "https://www.beeminder.com/api/v1/users/%s/goals/%s.json?datapoints=True&auth_token=%s";
        $tokenfile = "./token.txt"; # FIXME: this should definitely be changed
        $token = trim(file_get_contents($tokenfile));

        $usernamefile = "./username.txt";
        $username = trim(file_get_contents($usernamefile));

        $goalslugsfile = "./goalslugs.txt"; # it's okay for this to be public
        $goalslugsstring = trim(file_get_contents($goalslugsfile));
        $goalslugs = split("\n", $goalslugsstring);

        $goalmessage = function($lane) {
            # FIXME: Works only if positive lane is good
            $goallanemessages = [
                2 => "I'm exceeding expectations!",
                1 => "I'm on track.",
                -1 => "I'm nearly off track! Annoy me!",
                -2 => "I'm off track. Poke me until I tow the line",
            ];

            $lane = $lane > 2 ? 2 : $lane;
            $lane = $lane < -2 ? -2 : $lane;
            return $goallanemessages[$lane]; 
        };

        echo "<table>";
        foreach($goalslugs as $goalslug) {
            $url = sprintf($urlfmt, $username, trim($goalslug), $token);
            $goaljsonstring = file_get_contents($url);
            $goaljson = json_decode($goaljsonstring);

            echo "<tr>";
            echo sprintf("<td>%s</td><td>%s</td>", $goaljson->title, $goalmessage($goaljson->lane));
            echo "</tr>";
        }
        echo "</table>";
    ?>
</body>
</html>
