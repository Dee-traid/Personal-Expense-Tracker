<?php
namespace App\Controllers;

use App\Models\Budget;
use App\Views\Inputs\BudgetInput;
use App\Models\User;
use App\Models\Category;
use App\Services\EmailService;
use App\Views\CLIHelper;
use App\Core\DatabaseHelper;
use App\Core\UtilityFunction;
use App\Views\UIDisplay;
use PDO;
use PDOException;
use DateTimeImmutable;

class BudgetController{

	public static function addBudget(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$categories = CategoryController::viewAllCategories($userId);
		if (empty($categories)) {	
			CLIHelper::error("Create a category before adding a budget.");
			return null;
		}
		$choice = (int)CLIHelper::getInput("Select Category by S/N");
		$index = $choice - 1;

		if (!isset($categories[$index])) {
			CLIHelper::error("Invalid category selection.");
			return null;
		}

		$selectedCategory = $categories[$index];
		$categoryId = $selectedCategory->getId();
		$categoryName = $selectedCategory->getCategoryName();

		echo "Selected Category: $categoryName" . PHP_EOL;

		$id = uniqid();
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");
		$input = BudgetInput::budgetInput($userId);
		if (!$input) return null;
		extract($input);
		
		$query = " INSERT INTO Budget (id, user_id, category_id, category_name, amount, start_date, end_date, created_at, updated_at) VALUES (:id, :userId, :categoryId, :categoryName, :amount, :startDate, :endDate, :createdAt, :updatedAt)";

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':userId', $userId);
			$stmt->bindparam(':categoryId', $categoryId);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':amount', $amount);
			$stmt->bindparam(':startDate', $startDate);
			$stmt->bindparam(':endDate', $endDate);
			$stmt->bindparam(':createdAt', $timeStamp);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Added successful");
			return Budget::findOneByID($id);

		}catch(PDOException $e){
			CLIHelper::error(" Unknown Error(" . $e->getMessage());
		}

	}

	public static function viewAllBudgets(string $userId, bool $sortByDescending = true){
		$pdo = DatabaseHelper::getPDOInstance();
		$sort = $sortByDescending ? "desc" : "asc";
		$query = " SELECT * FROM  Budget WHERE user_id = :userId ORDER BY created_at $sort";
		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();

			$budgets = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$budgets[] = Budget::mapToBudgetRow($row);
			}

			UIDisplay::displayBudgetTable($budgets);
			return $budgets;

		} catch (PDOException $e) {
			CLIHelper:: error(" Unknown Error" . $e->getMessage());
		}
	}

	public static function updateBudgetDetails(string $userId) {
		$pdo = DatabaseHelper::getPDOInstance();

		$budgets = self::viewAllBudgets($userId);
   		if (empty($budgets)) return null;

		$bChoice = (int)CLIHelper::getInput("Select Budget S/N to update");
    	$bIndex = $bChoice - 1;

		if (!isset($budgets[$bIndex])) {
        CLIHelper::error("Invalid budget selection.");
        return null;
		}

		$selectedBudget = $budgets[$bIndex];
		$id = $selectedBudget->getID();

		$categories = CategoryController::viewAllCategories($userId); 
		echo "Current Category: " . $selectedBudget->getCategoryName() . PHP_EOL;
		$cChoice = CLIHelper::getInput("Select New Category S/N (Leave empty to keep current)");

		if (!empty($cChoice)) {
			$cIndex = (int)$cChoice - 1;
			if (isset($categories[$cIndex])) {
				$categoryId = $categories[$cIndex]->getID();
				$categoryName = $categories[$cIndex]->getCategoryName();
			} else {
				CLIHelper::error("Invalid category. Keeping current.");
				$categoryName = $selectedBudget->getCategoryName();
			}
		} else {
			$categoryName = $selectedBudget->getCategoryName();
		}

		$input = BudgetInput::updateBudgetInput($id);
		if(!$input) return null;
		extract($input);	
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");
		$query = "UPDATE Budget SET category_name = :categoryName, amount = :amount, start_date = :startDate, end_date = :endDate, updated_at = :updatedAt WHERE id = :id";

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':amount', $amount);
			$stmt->bindparam(':startDate', $startDate);
			$stmt->bindparam(':endDate', $endDate);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			if($stmt->rowCount() > 0){
				CLIHelper::success(" Update successful");
				return Budget::findOneByID($id);  
			} else {
				CLIHelper::error(" No rows updated - budget may not have changed");
				return null;
			}
		}catch(PDOException $e){
			CLIHelper::error(" Unknown Error" . $e->getMessage());
		}

	}

	public static function deleteBudgetByID(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		
		$budgets = self::viewAllBudgets($userId);
		if(!$budgets) {
			CLIHelper::error(" Budget not found.");
			return null;
		}

		$choice = (int)CLIHelper::getInput("Enter the S/N of the budget to delete");
		$index = $choice - 1;

		if (!isset($budgets[$index])) {
			CLIHelper::error("Invalid budget selection.");
			return null;
		}

		$budget = $budgets[$index];
		$id = $budget->getID();

		$query = "DELETE FROM Budget WHERE id = :id AND user_id = :userId";
		$confirm = UtilityFunction::confirm("Do you want to delete? (y/n): ");
		try{
			if(!$confirm) {
				CLIHelper::error("Deletion cancelled.");
				return null;
			}
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindParam(':userId', $userId);
			$stmt->execute();
			
			if($stmt->rowCount()  > 0){
				CLIHelper::success(" Budget '{$budget->getCategoryName()}' deleted successfully.");
				return $budget;
			}
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function deleteAllBudgets(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = UtilityFunction::confirm("Do you want to delete? (y/n): ");
		$confirm1= UtilityFunction::confirm(" Dangerous operation!!!!,  Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM Budget WHERE user_id = :userId"; 
				$stmt = $pdo->prepare($query);
				$stmt->bindParam(':userId', $userId);
				$stmt->execute();

				if($stmt->rowCount() > 0){
					CLIHelper::success(" Deleted sucessfully");
				}
			}else{
				CLIHelper::error(" Deletion Cancelled");
			}
		}catch(PDOException $e){
			CLIHelper::error(" Unknown Error(" . $e->getMessage());
		}
		
	}

	public static function budgetCheck(string $userId): bool{
		$pdo = DatabaseHelper::getPDOInstance();

		$categories = CategoryController::viewAllCategories($userId);
		if (empty($categories)) {
			CLIHelper::error(" No category found.");
			return false;
		}

		$choice = (int)CLIHelper::getInput("Enter the S/N to select the category");
    	$Index = $choice - 1; 

		if (!isset($categories[$Index])) {
			CLIHelper::error("Invalid selection.");
			return false;
   		}
		$selectedCategory = $categories[$Index];
    	$categoryId = $selectedCategory->getId();
    	$categoryName = $selectedCategory->getCategoryName();

		$query = "SELECT amount FROM Budget WHERE user_id  = :userId AND  category_name = :categoryName LIMIT 1";
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':userId', $userId);
		$stmt->bindparam(':categoryName', $categoryName);
		$stmt->execute();

		$budget = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$budget) return false;

		$queryE = " SELECT SUM(amount) as total_spent  FROM Expenses WHERE user_id = :userId AND category_name = :categoryName ";
		$stmtE = $pdo->prepare($queryE);
		$stmtE->bindparam(':userId', $userId);
		$stmtE->bindparam(':categoryName', $categoryName);
		$stmtE->execute();
		$expense = $stmtE->fetch(PDO::FETCH_ASSOC);
		if(!$expense) return false;

		$totalSpent = (float)($expense['total_spent'] ?? 0 );
		$budgetAmount = (float)$budget['amount'];
		
		if($totalSpent > $budgetAmount){
			CLIHelper::error(" You have exceeded your budget for $categoryName" . (" Amout spent: $totalSpent, , Budget: $budgetAmount"));

			$user = User::findOneByID($userId); 
        	if ($user) {
				EmailService::sendBudgetAlert(
					$user->getEmail(), 
					$user->getUserName(), 
					$categoryName, 
					$totalSpent, 
					$budgetAmount
				);
			}
			return false;
		}
		return true;
	}

}

?>