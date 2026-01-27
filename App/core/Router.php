<?php
    namespace App\core;

    use App\Controllers\AuthController;
    use App\Controllers\ExpenseController;
    use App\Controllers\CategoryController;
    use App\Controllers\BudgetController;
    use App\Views\CLIHelper;

    class Router{
        private static ?string $loggedInUserID = null;
        private static bool $isRunning;

        public static function Start(){
            $run = self::$isRunning;
            $userId = self::$loggedInUserID;
            self::showWelcome();
            while($run){
                if($userId === null){
                    self::mainMenu();
                }else{
                    self::authUserMenu();
                }
            }
            self::loggedOut();
        }

        private static function showWelcome(){
            echo "\n";
            echo "╔════════════════════════════════════════════════════╗\n";
            echo "║                                                    ║\n";
            echo "║           EXPENSE TRACKER APPLICATION              ║\n";
            echo "║                                                    ║\n";
            echo "╚════════════════════════════════════════════════════╝\n";
            echo "\n";
        }

        public static function loggedOut() {
            echo "\n";
            echo "╔════════════════════════════════════════════════════╗\n";
            echo "║                                                    ║\n";
            echo "║     Thank you for using Expense Tracker!           ║\n";
            echo "║                                                    ║\n";
            echo "╚════════════════════════════════════════════════════╝\n";
            echo "\n";
        }

        public static function mainMenu(){
            echo "  \n┌─────────────────────────────────────┐\n";
            echo "  │                   MENU              │\n";
            echo "  └─────────────────────────────────────┘\n";
            echo "     [1] Login\n";
            echo "     [2] Register\n";
            echo "     [3] Reset Password\n";
            echo "     [4] Exit\n";
            echo "  ─────────────────────────────────────\n";

            $choice = CLIHelper::getInput("Enter your choice");

            switch ($choice) {
                case '1':
                    self::
                    break;
                case '2':
                    self::
                    break;
                case '3':
                    self::
                    break;
                case '4':
                    $isRunning = false;
                    break;
                default:
                    self::loggedOut();
                    break;
            }

        }
        
        public static function authUserMenu(){
            echo "\n╔════════════════════════════════════════════════════╗\n";
            echo "║                   MAIN MENU                        ║\n";
            echo "╚════════════════════════════════════════════════════╝\n";
            echo "\n";
            echo "  [1] Expenses Menu\n";
            echo "  [2] Categories Menu\n";
            echo "  [3] Budget Menu\n";
            echo "  [4] Reports & Analytics\n";
            echo "  [5] User Profile\n";
            echo "  [6] Logout\n";
            echo "  [7] Exit Application\n";
            echo "─────────────────────────────────────────────────────\n";

            $choice = CLIHelper::getInput("Enter your choice");

            switch ($choice) {
                case '1':
                    self::showExpenseMenu();
                    break;
                case '2':
                    self::showCategoryMenu();
                    break;
                case '3':
                    self::showBudgetMenu();
                    break;
                case '4':
                    self::showReportsMenu();
                    break;
                case '5':
                    self::showProfileMenu();
                    break;
                case '6':
                    self::handleLogout();
                    break;
                case '7':
                    self::$isRunning = false;
                    self::loggedOut();
                    break;
                default:
                    CLIHelper::error("Invalid choice. Please try again.");
            }
        }

        public static function userMenu(){
            echo "\n┌─────────────────────────────────────┐\n";
            echo "│         USER PROFILE                │\n";
            echo "└─────────────────────────────────────┘\n";
            echo "  1. View Profile\n";
            echo "  2. Update Profile\n";
            echo "  3. Delete Account\n";
            echo "  4. Back to Main Menu\n";
            echo "─────────────────────────────────────\n";

            $choice = CLIHelper::getInput("Enter your choice");

            switch ($choice) {
                case '1':
                    # code...
                    break;
                case '2':
                    # code...
                    break;
                case '3':
                    # code...
                    break;
                case '4':
                    # code...
                    break;
                default:
                    CLIHelper::error(" Invalid Option.");
                    self::loggedOut();
                    break;
            }

        }

        public static function expenseMenu(){
             echo " \n┌─────────────────────────────────────┐\n";
            echo "  │               EXPENSE MENU           │\n";
            echo "  └─────────────────────────────────────┘\n";
            echo "  1. Add New Expense\n";
            echo "  2. View All Expenses\n";
            echo "  3. Update Expense\n";
            echo "  4. Delete Expense\n";
            echo "  5. Search Expenses\n";
            echo "  6. Filter Expenses by Period\n";
            echo "  7. Delete All Expenses\n";
            echo "  8. Back to Main Menu\n";
            echo "─────────────────────────────────────\n";

            $choice = CLIHelper::getInput("Enter your choice");

            switch ($choice){
                case '1':
                    # code...
                    break;
                case '2':
                    # code...
                    break;
                case '3':
                    # code...
                    break;
                case '4':
                    # code...
                    break;
                case '5':
                    # code...
                    break;
                case '6':
                    # code...
                    break;
                case '7':
                    # code...
                    break;
                case '8':
                    # code...
                    break;  
                default:
                    CLIHelper::error(" Invalid option. ");
                    self::loggedOut();
                    break;
            }
        }

        public static function categoryMenu(){
            echo "\n┌─────────────────────────────────────┐\n";
            echo "│      CATEGORY MANAGEMENT            │\n";
            echo "└─────────────────────────────────────┘\n";
            echo "  1. Add New Category\n";
            echo "  2. View All Categories\n";
            echo "  3. Update Category\n";
            echo "  4. Delete Category\n";
            echo "  5. Delete All Categories\n";
            echo "  6. Back to Main Menu\n";
            echo "─────────────────────────────────────\n";

            $choice = CLIHelper::getInput("Enter your choice");

            switch ($choice) {
                case '1':
                    # code...
                    break;
                case '2':
                    # code...
                    break;
                case '3':
                    # code...
                    break;
                case '4':
                    # code...
                    break;
                case '5':
                    # code...
                    break;
                case '6':
                    # code...
                    break;
                default:
                    # code...
                    break;
            }
        }

    public static function budgetMenu(){
        echo "\n┌─────────────────────────────────────┐\n";
        echo "│       BUDGET MANAGEMENT             │\n";
        echo "└─────────────────────────────────────┘\n";
        echo "  1. Add New Budget\n";
        echo "  2. View All Budgets\n";
        echo "  3. Update Budget\n";
        echo "  4. Delete Budget\n";
        echo "  5. Check Budget Status\n";
        echo "  6. Delete All Budgets\n";
        echo "  7. Back to Main Menu\n";
        echo "─────────────────────────────────────\n";

        $choice = CLIHelper::getInput("Enter your choice");

        switch ($choice) {
            case '1':
                $controller->addBudget(self::$currentUserId);
                break;
            case '2':
                $controller->viewAllBudgets(self::$currentUserId);
                break;
            case '3':
                $controller->updateBudget();
                break;
            case '4':
                $controller->deleteBudget();
                break;
            case '5':
                $controller->checkBudget(self::$currentUserId);
                break;
            case '6':
                $controller->deleteAllBudgets();
                break;
            case '7':
                return;
            default:
                CLIHelper::error("Invalid choice. Please try again.");
        }

        self::pauseForUser();
     }


      private static function showReportsMenu(): void {
        echo "\n┌─────────────────────────────────────┐\n";
        echo "│      REPORTS & ANALYTICS            │\n";
        echo "└─────────────────────────────────────┘\n";
        echo "  1. Expense Statistics\n";
        echo "  2. Expenditure Report\n";
        echo "  3. Expense Report by Category\n";
        echo "  4. Period Calculations\n";
        echo "  5. Back to Main Menu\n";
        echo "─────────────────────────────────────\n";

        $choice = CLIHelper::getInput("Enter your choice");

        $controller = new ExpenseController();

        switch ($choice) {
            case '1':
                $controller->showExpenseStats(self::$currentUserId);
                break;
            case '2':
                $controller->showExpenditureReport(self::$currentUserId);
                break;
            case '3':
                $controller->showExpenseReportByCategory(self::$currentUserId);
                break;
            case '4':
                $controller->showExpenseCalculations(self::$currentUserId);
                break;
            case '5':
                return;
            default:
                CLIHelper::error("Invalid choice. Please try again.");
        }

        self::pauseForUser();
    }


    // Utility methods

     private static function pauseForUser(): void {
        echo "\n";
        readline("Press Enter to continue...");
    }

    public static function getCurrentUserId(): ?string {
        return self::$currentUserId;
    }

    public static function setCurrentUserId(?string $userId): void {
        self::$currentUserId = $userId;
    }



     }

    

?>