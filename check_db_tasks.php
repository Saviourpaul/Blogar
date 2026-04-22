<?php
$c = new mysqli('localhost', 'root', '', 'blogar');
$r = $c->query("SELECT * FROM comments WHERE message LIKE '%task%' OR message LIKE '%TODO%' OR message LIKE '%asign%'");
while($row = $r->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Message: " . $row['message'] . PHP_EOL;
}
