<?php
    namespace App\Core;

    use App\Models\User;
    use App\Controllers\AuthController;
    use App\Controllers\ExpenseController;
    use App\Controllers\CategoryController;
    use App\Controllers\BudgetController;
    use App\Core\DatabaseHelper;
    use App\Views\CLIHelper;
    use App\Core\UtilityFunction;
    use App\Views\UIDisplay;

    class Router{
        public static bool $isRunning = true;

         public static function Start(){
            $run = self::$isRunning;
            self::showWelcome();
            while($run){
                $userId = User::$loggedInUserID;
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
            echo "=======================================\n";
            echo "                   MENU               \n";
            echo "=======================================\n";
            echo "    [1] User Login\n";
            echo "    [2] User Registration\n";
            echo "    [3] Reset Password\n";
            echo "    [4] Exit\n";
            echo "─────────────────────────────────────\n";

            $choice = CLIHelper::getInput("Enter your choice");
            switch ($choice) {
                case '1':
                    $user = AuthController::userLogin();
                    if($user){
                        CLIHelper::success(" ====== Welcome, " . $user->getUserName() . "! =========");
                    }
                    break;
                case '2':
                   $user = AuthController::userRegistration();
                   if($user){
                        CLIHelper::success(" ====== Welcome, " . $user->getUserName() . "! =========");
                   }
                    break;
                case '3':
                    AuthController::resetPassword();
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
            $userId = User::$loggedInUserID;
            UtilityFunction::clearScreen();
            echo "======================================================\n";
            echo "                      MAIN MENU                          \n";
            echo "======================================================\n";
            echo "\n";
            echo "  [1] User Profile\n";
            echo "  [2] Categories Menu\n";
            echo "  [3] Budget Menu\n";
            echo "  [4] Reports & Analytics\n";
            echo "  [5] Expenses Menu\n";
            echo "  [6] Logout\n";
            echo "  [7] Exit Application\n";
            echo "  [0] Go Back\n";
            echo "───────────────────────────────────────────────────── \n";

            $choice = CLIHelper::getInput("Enter your choice");

            switch ($choice) {
                case '1':
                    self::userMenu($userId);
                    break;
                case '2':
                    self::categoryMenu($userId);
                    break;
                case '3':
                     self::budgetMenu($userId);
                    break;
                case '4':
                    self::Reports($userId);
                    break;
                case '5':
                    self::expenseMenu($userId);
                    break;
                case '6':
                    User::$loggedInUserID = null;
                    CLIHelper::success("Logged out successfully!");
                    break;
                case '7':
                    self::$isRunning = false;
                    self::loggedOut();
                    break;
                case '0':
                    self::authUserMenu();
                    break;
                default:
                    CLIHelper::error("Invalid choice. Please try again.");
            }
        }

        public static function userMenu($userId){
            UtilityFunction::clearScreen();
            echo "======================================================\n";
            echo "                      USER PROFILE                       \n";
            echo "======================================================\n";
            echo "  [1] View Profile\n";
            echo "  [2] Update Profile\n";
            echo "  [3] Delete Account\n";
            echo "  [4] Back to Main Menu\n";
            echo "─────────────────────────────────────────────────────\n";

            $choice = CLIHelper::getInput("Enter your choice");

            switch ($choice) {
                case '1':
                   AuthController::viewUserProfile($userId);
                   UtilityFunction::pauseForUser();
                    break;
                case '2':
                   AuthController::updateUserDetails($userId);
                   UtilityFunction::pauseForUser();
                    break;
                case '3':
                    AuthController::deleteUserById($userId);
                    break;
                case '4':
                    return;
                default:
                    CLIHelper::error(" Invalid Option.");
                    self::loggedOut();
                    break;
            }

        }

        public static function expenseMenu($userId){
            UtilityFunction::clearScreen();
            echo "======================================================\n";
            echo "                      EXPENSE MENU                       \n";
            echo "======================================================\n";
            echo "  [1] Add New Expense\n";
            echo "  [2] View All Expenses\n";
            echo "  [3] Update Expense\n";
            echo "  [4] Delete Expense\n";
            echo "  [5] Search Expenses\n";
            echo "  [6] Filter Expenses by Period\n";
            echo "  [7] Delete All Expenses\n";
            echo "  [0] Back to Main Menu\n";
            echo "─────────────────────────────────────────────────────\n";

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
                case '0':
                    # code...
                    break;  
                default:
                    CLIHelper::error(" Invalid option. ");
                    self::loggedOut();
                    break;
            }
        }

        public static function categoryMenu($userId){
            UtilityFunction::clearScreen();
            echo "======================================================\n";
            echo "                      CATEGORY MENU                       \n";
            echo "======================================================\n";
            echo "  [1] Add New Category\n";
            echo "  [2] View All Categories\n";
            echo "  [3] Update Category\n";
            echo "  [4] Delete Category\n";
            echo "  [5] Delete All Categories\n";
            echo "  [0] Back to Main Menu\n";
            echo "───────────────────────────────────────────────────── \n";

            $choice = CLIHelper::getInput("Enter your choice");

            switch ($choice) {
                case '1':
                     CategoryController::addCategory($userId);
                     UtilityFunction::pauseForUser();
                    break;
                case '2':
                    CategoryController::viewAllCategories($userId, $sortByDescending = true);
                    UtilityFunction::pauseForUser();
                    break;
                case '3':
                    CategoryController::updateCategoryDetails($userId);
                    UtilityFunction::pauseForUser();
                    break;
                case '4':
                    CategoryController::deleteCategoryByID($userId);
                    UtilityFunction::pauseForUser();
                    break;
                case '5':
                    CategoryController::deleteAllCategory($userId);
                    break;
                case '0':
                    return;
                default:
                    CLIHelper::error(" Invalid option,");
                    UtilityFunction::pauseForUser();
                    return;
                    break;
            }
        }

    public static function budgetMenu($userId){
        UtilityFunction::clearScreen();
        echo "======================================================\n";
        echo "                      BUDGET MENU                       \n";
        echo "======================================================\n";
        echo "  [1] Add New Budget\n";
        echo "  [2] View All Budgets\n";
        echo "  [3] Update Budget\n";
        echo "  [4] Delete Budget\n";
        echo "  [5] Check Budget Status\n";
        echo "  [6] Delete All Budgets\n";
        echo "  [0] Back to Main Menu\n";
        echo "───────────────────────────────────────────────────── \n";

        $choice = CLIHelper::getInput("Enter your choice");

        switch ($choice) {
            case '1':
                
                break;
            case '2':
                
                break;
            case '3':

                break;
            case '4':

                break;
            case '5':
                
                break;
            case '6':

                break;
            case '0':
                return;
            default:
                CLIHelper::error("Invalid choice. Please try again.");
        }

        UtilityFunction::pauseForUser();
     }


      private static function Reports($userId) {
        UtilityFunction::clearScreen();
        echo "======================================================\n";
        echo "                   REPORT & ANALYTICS                     \n";
        echo "======================================================\n";
        echo "  [1] Expense Statistics\n";
        echo "  [2] Expenditure Report\n";
        echo "  [3] Expense Report by Category\n";
        echo "  [4] Period Calculations\n";
        echo "  [0] Back to Main Menu\n";
        echo "─────────────────────────────────────────────────────\n ";

        $choice = CLIHelper::getInput("Enter your choice");

        switch ($choice) {
            case '1':
                
                break;
            case '2':
                
                break;
            case '3':
                
                break;
            case '4':

                break;
            case '0':
                return;
            default:
                CLIHelper::error("Invalid choice. Please try again.");
        }

        UtilityFunction::pauseForUser();
        
    }

}


?>