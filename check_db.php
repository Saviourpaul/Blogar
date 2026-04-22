<?php
$c = new mysqli('localhost', 'root', '', 'blogar');
$r = $c->query('DESCRIBE comments');
while($row = $r->fetch_assoc()) {
    echo implode(', ', $row) . PHP_EOL;
}
