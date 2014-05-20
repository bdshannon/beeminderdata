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
    <meta content="text/html; charset=utf-8" />
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
                foreach($this->headers as $header) {
                    $html .= sprintf("<th>%s</th>", ucwords($header));
                }
                $html .= "</tr></thead>";
                return $html;
            }

            private function toHtmlTableBody() {
                $html = "<tbody>";
                foreach($this->data as $dataobj) {
                    $html .= "<tr>";
                    foreach($this->headers as $header) {
                        $html .= sprintf("<td>%s</td>", $dataobj->{$header});
                    }
                    $html .= "</tr>";
                }
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
        $tokenfile = "./token.txt"; # FIXME: this should definitely be changed
        $token = trim(file_get_contents($tokenfile));

        $usernamefile = "./username.txt";
        $username = trim(file_get_contents($usernamefile));

        $goalslugsfile = "./goalslugs.txt"; # it's okay for this to be public
        $goalslugsstring = trim(file_get_contents($goalslugsfile));
        $goalslugs = split("\n", $goalslugsstring);

        $goals = [];
        foreach($goalslugs as $goalslug) {
            $url = sprintf($urlfmt, $username, trim($goalslug), $token);
            $goaljsonstring = file_get_contents($url);
            $goal = json_decode($goaljsonstring);
            $goals[] = $goal;
        }

        $table_args = $goals;
        array_unshift($table_args, ["title", "pledge"]);
        $tbuilderfactory = new ReflectionClass("TableBuilder");
        $tbuilder = $tbuilderfactory->newInstanceArgs($table_args);
        $tbuilder->css_classes[] = "table";
        echo $tbuilder->toHtml();
    ?>
</body>
</html>
