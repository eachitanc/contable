<?php
session_start();
$res = 1;
$_SESSION['vigencia'] = $_POST['vig'];

echo json_encode($res);