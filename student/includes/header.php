<?php
require_once __DIR__ . '/../../includes/helpers/auth.php';
sms_require_auth('student');
/**
 * Module header adapter.
 * Existing pages keep requiring includes/header.php while the shared layout
 * renders assets, navbar, sidebar, and wrappers from one reusable place.
 */

require_once __DIR__ . '/sidebar.php';
require_once __DIR__ . '/../../includes/layouts/header.php';
