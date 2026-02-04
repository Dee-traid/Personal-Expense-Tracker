<?php
namespace App\Services;

require_once __DIR__ . '/../Services/EmailHelper.php'; 
require_once __DIR__ . '/../Services/EmailTemplate.php';

use App\Models\User;
use EmailHelper;
use EmailTemplates;

class EmailService{
    public static function sendBudgetAlert(string $userEmail, string $userName, string $category, float $spent, float $budget) {
        $subject = "⚠️ Budget Exceeded: $category";
        
        $body = "
            <h2>Budget Alert!</h2>
            <p>Hello $userName,</p>
            <p>You have exceeded your budget for the <strong>$category</strong> category.</p>
            <ul>
                <li><strong>Budget Amount:</strong> $" . number_format($budget, 2) . "</li>
                <li><strong>Total Spent:</strong> $" . number_format($spent, 2) . "</li>
                <li><strong>Over by:</strong> $" . number_format($spent - $budget, 2) . "</li>
            </ul>
            <p>Please review your expenses to stay on track!</p>
        ";

        return EmailHelper::sendEmail($userEmail, $userName, $subject, $body);
    }

    public static function sendMonthlyReport(string $userEmail, string $userName, array $expenseData) {
        $subject = "📊 Your Monthly Expense Report - " . date('F Y');
        
        $htmlContent = EmailTemplates::expenseSummary($userName, $expenseData);
        
        return EmailHelper::sendEmail($userEmail, $userName, $subject, $htmlContent);
    }

}

?>