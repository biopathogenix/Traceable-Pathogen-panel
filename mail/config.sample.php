<?php
/**
 * Copy this file to config.php and fill in real values.
 * config.php is git-ignored — never commit real credentials.
 */
return [
    'smtp_host'   => 'smtp.office365.com',
    'smtp_port'   => 587,
    'smtp_secure' => 'tls',          // 'tls' (STARTTLS, port 587) or 'ssl' (port 465)
    'smtp_user'   => 'noreply@biopathogenix.com',
    'smtp_pass'   => 'REPLACE_WITH_REAL_PASSWORD',

    'from_email'  => 'noreply@biopathogenix.com',
    'from_name'   => 'BioPathogenix Website',

    'to_email'    => 'rajeswari.gopu@biopathogenix.com',
    'to_name'     => 'Rajeswari Gopu',

    // Set true to also send the submitter an acknowledgement email.
    'send_confirmation' => true,
];
