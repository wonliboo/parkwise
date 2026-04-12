<?php
require_once __DIR__ . '/includes/config.php';
if (isLoggedIn()) {
    redirect(getDashboardUrl());
} else {
    redirect(BASE_URL . '/landing.php');
}
