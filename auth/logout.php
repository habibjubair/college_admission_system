<?php
// auth/logout.php

require_once '../includes/auth.php';

logout();

// Redirect to the homepage
header('Location: ../index.php');
exit();