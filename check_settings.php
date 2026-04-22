<?php
$c = new mysqli('localhost', 'root', '', 'blogar');
$r = $c->query('SELECT * FROM settings WHERE id = 1');
$row = $r->fetch_assoc();
print_r($row);
