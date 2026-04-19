<?php
require_once __DIR__ . '/../app/helpers/Mailer.php';

$result = Mailer::send(
    'aliceshmeis4@gmail.com',      // you can send to yourself for testing
    'Alice',
    'SMTP Test - Internal Portal',
    '<h2>It works ✅</h2><p>This email was sent from your project using Gmail SMTP + PHPMailer.</p>'
);

echo '<pre>';
print_r($result);
echo '</pre>';