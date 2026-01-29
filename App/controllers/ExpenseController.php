<?php
namespace App\Controllers;

use App\Models\Expense;
use App\Views\Input\ExpenseInput;
use App\Views\CLIHelper;
use App\Views\UIDisplay;
use App\Core\DatabaseHelper;
use PDOException;
use DateTimeImmutable;


class ExpenseController{
    public static function addExpense(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$input = ExpenseInput::getExpenseInput($userId);
		if(!$input) return null;
		extract($input);
		$id = uniqid();
		$timeStamp = (new DateTimeImmutable('Now'))->format("Y-m-d H:i:s");
      
		try{
			$query = " INSERT INTO Expenses (id, user_id, category_name, expense_name, amount, date, description, created_at, updated_at) VALUES (:id, :userId, :categoryName, :expenseName, :amount, :date, :description, :createdAt, :updatedAt)";

			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':userId', $userId);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':expenseName', $expenseName);
			$stmt->bindparam(':amount', $amount);
			$stmt->bindparam(':date', $date);
			$stmt->bindparam(':description', $description);
			$stmt->bindparam(':createdAt', $timeStamp);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper:: success(" Added successfully");
			return Category::findOneByID($id);

		}catch(PDOException $e){
			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}
	}

	public static function ViewAllExpenses(string $userId, bool $sortByDescending = true){
		$pdo = DatabaseHelper::getPDOInstance();
		$sort = $sortByDescending ? "desc" : "asc";
		$query = " SELECT * FROM  Expenses WHERE user_id = :userId ORDER BY created_at $sort";
		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();

			$expenses = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$expenses[] = Category::mapToExpenseRow($row);
			}
			return $expenses;
		} catch (PDOException $e) {
			CLIHelper::error(" Unknown Error: " . $e->getMessage());
		}
	}

	public static function updateExpense(string $id) {
		$pdo = DatabaseHelper::getPDOInstance();
		$input = CategoryInput::updateExpenseInput($id);
		if (!$input)  return null;
		extract($input);
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");
		$query = " UPDATE Expenses SET category_name = :categoryName, expense_name = :expenseName, amount = :amount,  description = :description, updated_at = :updatedAt WHERE  id = :id";

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':expenseName', $expenseName);
			$stmt->bindparam(':amount', $amount);
			$stmt->bindparam(':description', $description);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Updated successfully");

			if($stmt->rowCount() > 0){
				$expense = Category::findOneByID($id);
				return $expense;
			}

		}catch(PDOException $e){
			CLIHelper::error(" Unknown Error" . $e->getMessage());
			return null;
		}

	}

	public static function deleteExpenseByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$expense = Category::findOneByID($id);
		if(!$expense) {
			CLIHelper::error(" Expense with ID '$id' not found.");
			return null;
		}

		$query = "DELETE FROM  Expenses  WHERE id = :id";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->execute();
			
			if($stmt->rowCount()  > 0){
				CLIHelper::success(" Delete successful");
				return $expense;
			}
			return null;
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function DeleteAllExpenses(){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = AppManager::confirm("Do you want to delete? (y/n): ");
		$confirm1= AppManager::confirm("Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM Expenses "; 
				$stmt = $pdo->prepare($query);
				$stmt->execute();

				if($stmt->rowCount() > 0){
					CLIHelper::success(" Deleted sucessfully");
				}
			}else{
				CLIHelper::error(" Deletion Cancelled");
			}

		}catch(PDOException $e){
			
			CLIHelper:: error(" Unknown Error(" . $e->getMessage());
		}

	}

	public static function getExpenditureReport(string $userId, string $period) {
		$pdo = DatabaseHelper::getPDOInstance();
		switch ($period) {
			case 'day':
				$select = "created_at::date as label";
				$groupBy = "created_at::date";
				break;
			case 'month':
				$select = "TO_CHAR(created_at, 'YYYY-MM') as label";
				$groupBy = "TO_CHAR(created_at, 'YYYY-MM')";
				break;
			case 'year':
				$select = "EXTRACT(YEAR FROM created_at) as label";
				$groupBy = "EXTRACT(YEAR FROM created_at)";
				break;
			case 'all':
			$select = "TO_CHAR(created_at, 'YYYY-MM') as label";
			$groupBy = "TO_CHAR(created_at, 'YYYY-MM')";
			break;
			default:
				throw new Exception("Invalid period selected.");
		}

		$query = "SELECT $select, SUM(amount) as total FROM Expenses WHERE user_id = :userId GROUP BY $groupBy ORDER BY label DESC";

		$stmt = $pdo->prepare($query);
		$stmt->bindParam(':userId', $userId);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getExpenseStats(string $userId, string $period){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = " SELECT 
						AVG(amount) as average_expense, 
						MAX(amount) as highest_expense, 
						MIN(amount) as lowest_expense, 
						SUM(amount) as total_expense,
						(SELECT category_name FROM Expenses WHERE user_id = :userId AND amount = (SELECT MIN(amount) FROM Expenses WHERE user_id = :userId)LIMIT 1) as lowest_category,
						(SELECT category_name FROM Expenses WHERE user_id = :userId AND amount = (SELECT MAX(amount) FROM Expenses WHERE user_id = :userId)LIMIT 1) as highest_category
						FROM  Expenses WHERE user_id = :userId ";

		switch (strtolower($period)) {
			case 'date':
				$query .= " AND Date (created_at) = 'CURRENT_DATE' ";
				break;
			case 'month':
				$query .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE) AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)";
				break;
			case 'year':
				$query .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
				break;
			case 'all':
			default:
				break;
		}
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();

			$expenses = $stmt->fetch(PDO::FETCH_ASSOC);
			return $expenses;
		}catch(PDOException $e){
			CLIHelper::error("Stats Error: " . $e->getMessage());
			return null;
		}
		
	}

	public static function filterExpenses(string $userId, string $period){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = "SELECT * FROM Expenses WHERE user_id = :userId"; 
		switch (strtolower($period)) {
			case 'day':
				$query  .= " AND Date(created_at) = CURRENT_DATE";
				break;
			case 'month':
				$query  .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE) AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)";
				break;
			case 'year':
				$query .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
				break;
			case 'all':
				break;
			default:
				CLIHelper::error(" Invalid Input (Day,Month,Year)");
				break;
		}

		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':userId', $userId);
		$stmt->execute();
		$expenses = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$expenses[] = Expense::mapToExpenseRow($row);
		}
		return $expenses;
	}

	public static function searchExpensesByCategoryAndDate(string $userId, ?string $categoryName = null, ?string $startDate = null, ?string $endDate = null) {
		$pdo = DatabaseHelper::getPDOInstance();
		$query = "SELECT * FROM Expenses WHERE user_id = :userId";
		
		if ($categoryName !== null && $categoryName !== '') {
			$query .= " AND LOWER(category_name) LIKE LOWER(:categoryName)";
		}
		
		if ($startDate !== null && $startDate !== '') {
			$query .= " AND DATE(created_at) >= :startDate";
		}
		
		if ($endDate !== null && $endDate !== '') {
			$query .= " AND DATE(created_at) <= :endDate";
		}
		
		$query .= " ORDER BY date DESC, created_at DESC";
		
		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':userId', $userId);
			
			if ($categoryName !== null && $categoryName !== '') {
				$categoryNameParam = "%$categoryName%";
				$stmt->bindParam(':categoryName', $categoryNameParam);
			}
			
			if ($startDate !== null && $startDate !== '') {
				$stmt->bindParam(':startDate', $startDate);
			}
			
			if ($endDate !== null && $endDate !== '') {
				$stmt->bindParam(':endDate', $endDate);
			}
			
			$stmt->execute();
			
			$expenses = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$expenses[] = Expense::mapToExpenseRow($row);
			}
			return $expenses;
		} catch (PDOException $e) {
			CLIHelper::error("Search Error: " . $e->getMessage());
			return [];
		}
	}

	public static function getExpenseReportByCategoryDateAmount(string $userId, string $period) {
		$pdo = DatabaseHelper::getPDOInstance();
		
		$query = "SELECT category_name, date, expense_name, amount, description FROM Expenses WHERE user_id = :userId";
		
		switch (strtolower($period)) {
			case 'day':
				$query .= " AND DATE(created_at) = CURRENT_DATE";
				break;
			case 'month':
				$query .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE) 
							AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)";
				break;
			case 'year':
				$query .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
				break;
			case 'all':
			default:
				break;
		}
		$query .= " ORDER BY category_name, date DESC";
		
		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':userId', $userId);
			$stmt->execute();
			
			$expenses = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$expenses[] = Expense::mapToExpenseRow($row);
			}
			
			$groupedExpenses = [];
			foreach ($expenses as $expense) {
				$category = $expense->getCategoryName();
				if (!isset($groupedExpenses[$category])) {
					$groupedExpenses[$category] = [];
				}
				$groupedExpenses[$category][] = $expense;
			}
			
			return $groupedExpenses;
			
		} catch (PDOException $e) {
			CLIHelper::error("Report Error: " . $e->getMessage());
			return [];
		}
	}

	public static function calculateExpensesByPeriod(string $userId) {
		$pdo = DatabaseHelper::getPDOInstance();
		try {
			$queryDay = "SELECT  COUNT(*) as count, COALESCE(SUM(amount), 0) as total, COALESCE(AVG(amount), 0) as average FROM Expenses WHERE user_id = :userId AND DATE(created_at) = CURRENT_DATE";	
			$stmtDay = $pdo->prepare($queryDay);
			$stmtDay->bindParam(':userId', $userId);
			$stmtDay->execute();
			$dailyData = $stmtDay->fetch(PDO::FETCH_ASSOC);
		
			$queryMonth = "SELECT  COUNT(*) as count, COALESCE(SUM(amount), 0) as total, COALESCE(AVG(amount), 0) as average FROM Expenses  WHERE user_id = :userId 
			AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE) 
			AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)";
			
			$stmtMonth = $pdo->prepare($queryMonth);
			$stmtMonth->bindParam(':userId', $userId);
			$stmtMonth->execute();
			$monthlyData = $stmtMonth->fetch(PDO::FETCH_ASSOC);
			
			$queryYear = "SELECT  COUNT(*) as count, COALESCE(SUM(amount), 0) as total, COALESCE(AVG(amount), 0) as average FROM Expenses WHERE user_id = :userId 
			AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
			
			$stmtYear = $pdo->prepare($queryYear);
			$stmtYear->bindParam(':userId', $userId);
			$stmtYear->execute();
			$yearlyData = $stmtYear->fetch(PDO::FETCH_ASSOC);
			
			return [
				'daily' => $dailyData,
				'monthly' => $monthlyData,
				'yearly' => $yearlyData
			];
			
		} catch (PDOException $e) {
			CLIHelper::error("Calculation Error: " . $e->getMessage());
			return null;
		}
	}

}

?>