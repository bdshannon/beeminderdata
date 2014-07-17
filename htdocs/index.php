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

    Copyright Brian Shannon 2014
**/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta content="text/html; charset=utf-8" />
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <style>
    .header
    {
        position: relative;
        padding: 30px 15px;
        color: #CDBFE3;
        text-align: center;
        text-shadow: 0px 1px 0px rgba(0, 0, 0, 0.1);
        background-color: #6F5499;
        background-image: linear-gradient(to bottom, #563D7C 0px, #6F5499 100%);
        background-repeat: repeat-x;
    }
    </style>
</head>
<body>
    <div class="header">
        <h1>Goals</h1>
        <p>What I want to do, by when and how much it'll sting if I don't do it</p>
    </div>
    <div class="container">
    <p>
        The way it works: I make an ongoing measurable goal. If I keep to my goal on an ongoing basis, great. Otherwise, the sting kicks in and I lose cold, hard cash.
    </p>
    <p>
        Below you have the (hopefully descriptive) names of my goals, when I'll lose if I do nothing (if I study enough in one week, I don't have to study much the next) and the amount I'd lose on that date.
    </p>
    <?php
        function fget_contents() {
            $args = func_get_args();
            // the @ can be removed if you lower error_reporting level
            $contents = @call_user_func_array('file_get_contents', $args);

            if ($contents === false) {
                throw new Exception('Failed to open ' . $file);
            } else {
                return $contents;
            }
        }

        class TableBuilder {
            function __construct() {
                $args = func_get_args();
                if (count($args) < 2) {
                    throw new BadMethodCallException("Less than two arguments.");
                }
                $this->headers = array_shift($args);
                $this->data = $args;
                $this->css_classes = [];
                $this->value_if_empty = "";
                return $this;
            }

            private function toHtmlTableHeader() {
                $html = "<thead><tr>";
                foreach(array_keys($this->headers) as $header) {
                    $html .= sprintf("<th>%s</th>", htmlentities(ucwords($header)));
                }
                unset($header);
                $html .= "</tr></thead>";
                return $html;
            }

            private function toHtmlTableBody() {
                $html = "<tbody>";
                foreach($this->data as $dataobj) {
                    $html .= "<tr>";
                    foreach($this->headers as $propname) {
                        $html .= sprintf("<td>%s</td>", htmlentities($dataobj->{$propname}));
                    }
                    unset($propname);
                    $html .= "</tr>";
                }
                unset($dataobj);
                $html .= "</tbody>";
                return $html;
            }

            public function toHtml() {
                $html = sprintf("<table class=\"%s\">", join($this->css_classes));
                $html .= $this->toHtmlTableHeader();
                $html .= $this->toHtmlTableBody();
                $html .= "</table>";
                return $html;
            }
        }

        # FIXME: fetching from a url should be done in a cronjob
        $urlfmt = "https://www.beeminder.com/api/v1/users/%s/goals/%s.json?datapoints=False&auth_token=%s";
        $configfilename = "../config/beeminder.config.ini";
        $config = parse_ini_file($configfilename);

        $token = trim($config["auth_token"]);
        $username = trim($config["username"]);
        $goalslugs = $config["goals"];

        $goals = [];
        foreach($goalslugs as $goalslug) {
            $url = sprintf($urlfmt, $username, trim($goalslug), $token);
            try {
                $goaljsonstring = fget_contents($url);
            } catch (Exception $e) {
                echo "Catastrophe! Failed to get details for a goal!";
                exit;
            }
            $goal = json_decode($goaljsonstring);
            $goals[] = $goal;
        }

        array_map(function($goal) {
            $goal->losedatestring = htmlentities(date("d-m-y", $goal->losedate));
        }, $goals);

        array_map(function($goal) {
           $goal->pledgestring = htmlentities(sprintf("$%s", $goal->pledge));
        }, $goals);

        array_map(function($goal) {
            $losedate = new DateTime();
            $losedate->setTimestamp($goal->losedate);
            $goal->timeuntillose = $losedate->diff(new DateTime("now"));
            $goal->timeuntillosestring = $goal->timeuntillose->days . " days, " .
                                         $goal->timeuntillose->h . " hours";
        }, $goals);

        $table_args = $goals;
        $headers = [
            "Title" => "title",
            "Pledge" => "pledgestring",
            "Lose Date" => "losedatestring",
            "Time Until Lose" => "timeuntillosestring",
        ];
        array_unshift($table_args, $headers);
        $tbuilderfactory = new ReflectionClass("TableBuilder");
        $tbuilder = $tbuilderfactory->newInstanceArgs($table_args);
        $tbuilder->css_classes[] = "table";
        echo $tbuilder->toHtml();
    ?>
    </div> <!-- div container -->
</body>
</html>
