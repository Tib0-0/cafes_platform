<?php
require_once __DIR__ . "/../config/database.php";
require_once "TestHelper.php";

$db = (new Database())->getConnection();

assertEquals(true, $db instanceof PDO, "Database connection returns PDO instance");
