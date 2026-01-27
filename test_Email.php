<?php

require_once 'EmailHelper.php';
require_once 'EmailTemplate.php';

echo "Testing email system...\n\n";

echo "Test 1: Sending simple email...\n";
$sent = EmailHelper::sendEmail(
    'adekelvin369@gmail.com',  
    'Test User',
    'Test Email from Expense Tracker',
    '<h1>Hello!</h1><p>This is a test email from your Expense Tracker.</p>'
);

if ($sent) {
    echo "✓ Simple email sent successfully!\n\n";
} else {
    echo "✗ Simple email failed!\n\n";
}

echo "Test 2: Sending expense summary...\n";
$testData = [
    'period' => 'This Month',
    'total' => 1234.56,
    'count' => 15,
    'categories' => [
        ['name' => 'Food', 'total' => 500.00],
        ['name' => 'Transport', 'total' => 300.00],
        ['name' => 'Utilities', 'total' => 434.56]
    ]
];

$html = EmailTemplates::expenseSummary('John Doe', $testData);
$sent2 = EmailHelper::sendEmail(
    'adekelvin369@gmail.com', 
    'John Doe',
    'Your Expense Summary - This Month',
    $html
);

if ($sent2) {
    echo "✓ Expense summary sent successfully!\n\n";
} else {
    echo "✗ Expense summary failed!\n\n";
}

echo "Testing complete!\n";