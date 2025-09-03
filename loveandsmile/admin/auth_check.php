<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: access_denied.php");
    exit();
}