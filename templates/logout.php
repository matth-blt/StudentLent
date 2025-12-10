<?php session_start();

require_once("../lib/auth_functions.php");

logoutUser();
header('Location: ../home.php');
