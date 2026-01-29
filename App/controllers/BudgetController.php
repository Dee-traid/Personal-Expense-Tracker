<?php

namespace App\Controllers;

use App\Models\Budget;
use App\Models\User;
use App\Models\Category;
use App\View\CLIHelper;
use App\Core\DatabaseHelper;
use PDO;
use PDOException;
use DateTimeImmutable;

class BudgetController{

	public static function addBudget( string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$id = uniqid();
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");
		$input = BudgetInput::budgetInput($userId);
		if (!$input) return null;
		extract($input);
		$query = " INSERT INTO Budget (id, user_id, category_name, amount, start_date, end_date, created_at, updated_at) VALUES (:id, :userId, :categoryName, :amount, :startDate, :endDate, :createdAt, :updatedAt)";
        
		if (!Category::findOneByCategoryName($categoryName)) {
            throw new Exception("Category not found");
        }

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':userId', $userId);
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

	public static function ViewAllBudgets(string $userId, bool $sortByDescending = true){
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
			return $budgets;
	
		} catch (PDOException $e) {
			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}
	}

	public static function updateBudgetDetails(string $id) {
		$pdo = DatabaseHelper::getPDOInstance();
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

	public static function deleteBudgetByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$budget = Budget::findOneByID($id);
		if(!$budget) {
			CLIHelper::error(" Budget with ID '$id' not found.");
			return null;
		}

		$query = "DELETE FROM  Budget WHERE id = :id";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->execute();
			
			if($stmt->rowCount()  > 0){
				CLIHelper::success(" Budget '{$budget->getCategoryName()}' deleted successfully.");
				return $budget;
			}
			return null;
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function DeleteAllBudgets(){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = AppManager::confirm("Do you want to delete? (y/n): ");
		$confirm1= AppManager::confirm("Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM Budget "; 
				$stmt = $pdo->prepare($query);
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

	public static function budgetCheck(string $userId, string $categoryName): bool{
		$pdo = DatabaseHelper::getPDOInstance();
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
		}
		return false;
	}

}

?>