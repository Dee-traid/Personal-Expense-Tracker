<?php

class EmailTemplates {
    
    public static function expenseSummary(string $userName, array $data) {
        $period = $data['period'] ?? 'This Month';
        $totalExpenses = $data['total'] ?? 0;
        $transactionCount = $data['count'] ?? 0;
        $categories = $data['categories'] ?? [];
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
                .summary-box { background: white; padding: 20px; margin: 15px 0; border-left: 4px solid #4CAF50; }
                .category-item { padding: 10px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; }
                .amount { font-size: 28px; font-weight: bold; color: #4CAF50; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>💰 Expense Summary</h1>
                </div>
                <div class='content'>
                    <h2>Hello, $userName!</h2>
                    <p>Here's your expense summary for <strong>$period</strong>:</p>
                    
                    <div class='summary-box'>
                        <h3>Total Expenses</h3>
                        <p class='amount'>$" . number_format($totalExpenses, 2) . "</p>
                        <p>Total Transactions: $transactionCount</p>
                    </div>
                    
                    <h3>Breakdown by Category:</h3>";
        
        foreach ($categories as $category) {
            $html .= "
                    <div class='category-item'>
                        <strong>{$category['name']}</strong>
                        <span>$" . number_format($category['total'], 2) . "</span>
                    </div>";
        }
        
        $html .= "
                </div>
                <div class='footer'>
                    <p>This is an automated email from Expense Tracker</p>
                    <p>© " . date('Y') . " Expense Tracker. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    public static function monthlyReminder(string $userName, array $data) {
        $month = $data['month'] ?? date('F Y');
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2196F3; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
                .reminder-box { background: white; padding: 20px; margin: 15px 0; border-left: 4px solid #2196F3; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📅 Monthly Expense Reminder</h1>
                </div>
                <div class='content'>
                    <h2>Hello, $userName!</h2>
                    <p>It's a new month! Time to track your expenses for <strong>$month</strong>.</p>
                    
                    <div class='reminder-box'>
                        <h3>Remember to:</h3>
                        <ul>
                            <li>✓ Record your expenses daily</li>
                            <li>✓ Review your spending weekly</li>
                            <li>✓ Stay within your budget limits</li>
                            <li>✓ Set budgets for this month</li>
                        </ul>
                    </div>
                    
                    <p>Good luck managing your finances this month!</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email from Expense Tracker</p>
                    <p>© " . date('Y') . " Expense Tracker</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }
}