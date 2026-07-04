<?php
require_once __DIR__ . '/../../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);
/**
 * Admin header adapter for the shared dashboard layout.
 */

require_once __DIR__ . '/sidebar.php';
require_once __DIR__ . '/../../includes/layouts/header.php';
