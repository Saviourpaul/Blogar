<?php
$c = new mysqli('localhost', 'root', '', 'blogar');
$r = $c->query("SELECT * FROM posts WHERE title LIKE '%task%' OR body LIKE '%task%'");
while($row = $r->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Title: " . $row['title'] . PHP_EOL;
}
