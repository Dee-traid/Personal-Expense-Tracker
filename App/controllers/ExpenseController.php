<?php
namespace App\Controllers;

use App\Models\Expense;
use App\Models\User;
use App\Models\Category;
use App\Controllers\CategoryController;
use App\Services\EmailService;
use App\Views\Inputs\ExpenseInput;
use App\Views\Inputs\UIDisplayInput;
use App\Views\CLIHelper;
use App\Views\UIDisplay;
use App\Core\DatabaseHelper;
use App\Core\UtilityFunction;
use PDO;
use PDOException;
use DateTimeImmutable;


class ExpenseController{
    public static function addExpense(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$categories = CategoryController::viewAllCategories($userId);
		if (empty($categories)) {	
			CLIHelper::error("Create a category before adding an expense.");
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
		$timeStamp = (new DateTimeImmutable('Now'))->format("Y-m-d H:i:s");
		$input = ExpenseInput::getExpenseInput($userId);
		if(!$input) return null;
		extract($input);
      
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
			return Expense::findOneByID($id);

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
				$expenses[] = Expense::mapToExpenseRow($row);
			}
			if (empty($expenses)) {
				CLIHelper::error("No expenses found.");
			} else {
				UIDisplay::displayExpenseTable($expenses);
			}
			return $expenses;
		} catch (PDOException $e){
			CLIHelper::error(" Unknown Error: " . $e->getMessage());
		}
	}

	public static function updateExpenseDetails(string $userId) {
		$pdo = DatabaseHelper::getPDOInstance();
		$expenses = self::viewAllExpenses($userId);
   		if (empty($expenses)) return null;

		$Choice = (int)CLIHelper::getInput("Select Expense S/N to update");
    	$Index = $Choice - 1;

		if (!isset($expenses[$Index])) {
        CLIHelper::error("Invalid expense selection.");
        return null;
		}

		$selectedExpense = $expenses[$Index];
		$id = $selectedExpense->getID();

		$categories = CategoryController::viewAllCategories($userId); 
		echo "Current Category: " . $selectedExpense->getCategoryName() . PHP_EOL;
		$cChoice = CLIHelper::getInput("Select New Category S/N (Leave empty to keep current)");

		if (!empty($cChoice)) {
			$cIndex = (int)$cChoice - 1;
			if (isset($categories[$cIndex])) {
				$categoryId = $categories[$cIndex]->getID();
				$categoryName = $categories[$cIndex]->getCategoryName();
			} else {
				CLIHelper::error("Invalid category. Keeping current.");
				$categoryName = $selectedExpense->getCategoryName();
			}
		} else {
			$categoryName = $selectedExpense->getCategoryName();
		}

		$input = ExpenseInput::updateExpenseInput($id);
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
				$expense = Expense::findOneByID($id);
				return $expense;
			}

		}catch(PDOException $e){
			CLIHelper::error(" Unknown Error" . $e->getMessage());
			return null;
		}

	}

	public static function deleteExpenseByID(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$expenses = self::viewAllExpenses($userId);
		if(!$expenses) {
			CLIHelper::error(" Budget not found.");
			return null;
		}

		$choice = (int)CLIHelper::getInput("Enter the S/N of the budget to delete");
		$index = $choice - 1;

		if (!isset($expenses[$index])) {
			CLIHelper::error("Invalid budget selection.");
			return null;
		}

		$expense = $expenses[$index];
		$id = $expense->getID();

		$query = "DELETE FROM Expenses WHERE id = :id AND user_id = :userId";
		$confirm = UtilityFunction::confirm("Do you want to delete? (y/n): ");
		try{
			if($confirm){
				$stmt = $pdo->prepare($query);
				$stmt->bindparam(':id', $id);
				$stmt->bindparam(':userId', $userId);
				$stmt->execute();
				
				if($stmt->rowCount()  > 0){
					CLIHelper::success(" Delete successful");
					return $expense;
				}
				return null;
			}
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function deleteAllExpenses(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = UtilityFunction::confirm(" Do you want to delete? (y/n): ");
		$confirm1= UtilityFunction::confirm(" Dangerous Operation!!, Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 === true){
				$query = " DELETE FROM Expenses WHERE user_id = :userId"; 
				$stmt = $pdo->prepare($query);
				$stmt->bindparam(':userId', $userId);
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

	public static function getExpenditureReport(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$period = UIDisplayInput::selectPeriod();
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
				CLIHelper::error("Invalid period selected.");
				return null;
		}

		$query = "SELECT $select, SUM(amount) as total FROM Expenses WHERE user_id = :userId GROUP BY $groupBy ORDER BY label DESC";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':userId', $userId);
			$stmt->execute();
			$report = $stmt->fetchAll(PDO::FETCH_ASSOC);
			UIDisplay::expenditureReportDisplay($report, $period);
			return $report;
		}catch(PDOException $e){
			CLIHelper::error("Stats Error: " . $e->getMessage());
			return null;
		}
	}

	public static function getExpenseStats(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$period = UIDisplayInput::selectPeriod();
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
				$query .= " AND Date (created_at) = CURRENT_DATE";
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
			$stats = $stmt->fetch(PDO::FETCH_ASSOC);

			UIDisplay::expenseStatsDisplay($stats, $period);

			return $stats;
		}catch(PDOException $e){
			CLIHelper::error("Stats Error: " . $e->getMessage());
			return null;
		}
		
	}

	public static function filterExpenses(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$period = UIDisplayInput::selectPeriod();

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
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();
			
			$expenses = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$expenses[] = Expense::mapToExpenseRow($row);
			}

			UIDisplay::filterExpenseDisplay($expenses, $period);
			return $expenses;

			}catch(PDOException $e){
				CLIHelper::error("Filter Error: " . $e->getMessage());
				return null;
		}
		
	}

	public static function searchExpensesByCategoryAndDate(string $userId) {
		$pdo = DatabaseHelper::getPDOInstance();
		
		$searchInput = UIDisplayInput::searchInput();
		$categoryName = $searchInput['categoryName'] ?? null;
		$startDate = $searchInput['startDate'] ?? null;
		$endDate = $searchInput['endDate'] ?? null;

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

			UIDisplay::searchExpensesDisplay($expenses, $searchInput);
			return $expenses;
			
		} catch (PDOException $e) {
			CLIHelper::error("Search Error: " . $e->getMessage());
			return [];
		}
	}

	public static function getExpenseReportByCategoryDateAmount(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$period = UIDisplayInput::selectPeriod();
		
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

			UIDisplay::expenseReportDisplay($groupedExpenses, $period);
			return $groupedExpenses;
			
		} catch (PDOException $e) {
			CLIHelper::error("Report Error: " . $e->getMessage());
			return [];
		}
	}

	public static function calculateExpensesByPeriod(string $userId){
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
			
			$results = [
				'daily' => $dailyData,
				'monthly' => $monthlyData,
				'yearly' => $yearlyData
			];

			UIDisplay::expenseCalculationDisplay($results);
			return $results;
			
		} catch (PDOException $e) {
			CLIHelper::error("Calculation Error: " . $e->getMessage());
			return null;
		}
	}

	
	public static function emailMonthlySummary(string $userId) {
		$groupedExpenses = self::getExpenseReportByCategoryDateAmount($userId);
		if (empty($groupedExpenses)) {
			CLIHelper::error("No expenses report found.");
			return false;
		}
		$categoriesForEmail = [];
		$grandTotal = 0;
		$count = 0;

		foreach ($groupedExpenses as $catName => $list) {
			$catTotal = 0;
			foreach ($list as $exp) {
				$catTotal += $exp->getAmount();
				$count++;
			}
			$categoriesForEmail[] = ['name' => $catName, 'total' => $catTotal];
			$grandTotal += $catTotal;
		}

		$emailData = [
			'period'     => date('F Y'),
			'total'      => $grandTotal,
			'count'      => $count,
			'categories' => $categoriesForEmail
		];

		$user = User::findOneByID($userId);
		return EmailService::sendMonthlyReport($user->getEmail(), $user->getUserName(), $emailData);
	}

}

?>