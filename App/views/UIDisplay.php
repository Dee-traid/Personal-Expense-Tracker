<?php
class UIDisplay{
	Public static function filterExpenseDisplay(){
		echo "=== FILTER EXPENSES ===\n";
		$period = UIDisplay::selectPeriod();
		$expenses = Expense::filterExpenses('6950fd9de6ca7', $period);

		if (empty($expenses)) {
			echo "No expenses found for this period.\n";
		} else {
			$periodTitle = match($period) {
				'day' => 'Today',
				'month' => 'This Month',
				'year' => 'This Year',
				'all' => 'All Time',
				default => ucfirst($period)
			};
			
			echo "\n" . str_repeat("=", 90) . "\n";
			echo str_pad("Filtered Expenses - $periodTitle", 90, " ", STR_PAD_BOTH) . "\n";
			echo str_repeat("=", 90) . "\n";
			echo sprintf("%-12s %-18s %-20s %-15s %-20s\n", 
				"Date", "Category", "Expense", "Amount", "Description");
			echo str_repeat("-", 90) . "\n";
			
			$totalAmount = 0;
			
			foreach ($expenses as $expense) {
				$totalAmount += $expense->getAmount();	
				echo sprintf(
					"%-12s %-18s %-20s $%-14s %-20s\n",
					$expense->getDate()->format('Y-m-d'),
					substr($expense->getCategoryName(), 0, 17),
					substr($expense->getExpenseName(), 0, 19),
					number_format($expense->getAmount(), 2),
					substr($expense->getDescription(), 0, 19)
				);
			}	
			echo str_repeat("=", 90) . "\n";
			echo sprintf("%-52s $%-14s\n", 
				"TOTAL (" . count($expenses) . " expenses)", 
				number_format($totalAmount, 2)
			);
			echo sprintf("%-52s $%-14s\n", 
				"AVERAGE", 
				number_format($totalAmount / count($expenses), 2)
			);
			echo str_repeat("=", 90) . "\n\n";
		}
	}

	public static function expenseReportDisplay(){
		echo "=== EXPENSE REPORT BY CATEGORY, DATE & AMOUNT ===\n";
		$period = UIDisplay::selectPeriod();
		$report = Expense::getExpenseReportByCategoryDateAmount('6950fd9de6ca7', $period);

		if (empty($report)) {
			echo "No expenses found for this period.\n";
		} else {
			$periodTitle = match($period) {
				'day' => 'Today',
				'month' => 'This Month',
				'year' => 'This Year',
				'all' => 'All Time',
				default => ucfirst($period)
			};		
			echo "\n" . str_repeat("=", 95) . "\n";
			echo str_pad("Expense Report - $periodTitle", 95, " ", STR_PAD_BOTH) . "\n";
			echo str_repeat("=", 95) . "\n\n";

			$grandTotal = 0;
			$totalTransactions = 0;		
			foreach ($report as $category => $expenses) {
				echo "┌" . str_repeat("─", 93) . "┐\n";
				echo sprintf("│ %-91s │\n", strtoupper($category));
				echo "├" . str_repeat("─", 93) . "┤\n";
				echo sprintf("│ %-12s %-35s %-18s %-25s │\n", 
					"Date", "Expense", "Amount", "Description");
				echo "├" . str_repeat("─", 93) . "┤\n";
				
				$categoryTotal = 0;		
				foreach ($expenses as $expense) {
					$categoryTotal += $expense->getAmount();
					$totalTransactions++;				
					echo sprintf("│ %-12s %-35s $%-17s %-25s │\n",
						$expense->getDate()->format('Y-m-d'),
						substr($expense->getExpenseName(), 0, 34),
						number_format($expense->getAmount(), 2),
						substr($expense->getDescription(), 0, 24)
					);
				}
				
				$grandTotal += $categoryTotal;
				
				echo "├" . str_repeat("─", 93) . "┤\n";
				echo sprintf("│ %-50s $%-17s %-24s │\n",
					"SUBTOTAL (" . count($expenses) . " transaction(s))",
					number_format($categoryTotal, 2),
					number_format(($categoryTotal / $grandTotal) * 100, 1) . "% of total"
				);
				echo "└" . str_repeat("─", 93) . "┘\n\n";
			}
			
			echo str_repeat("=", 95) . "\n";
			echo sprintf("%-52s $%-17s\n",
				"GRAND TOTAL (" . $totalTransactions . " transactions)",
				number_format($grandTotal, 2)
			);
			echo sprintf("%-52s $%-17s\n",
				"AVERAGE PER TRANSACTION",
				number_format($grandTotal / $totalTransactions, 2)
			);
			echo str_repeat("=", 95) . "\n\n";
		}
	}

	public static function expenseCalculationDisplay(){
		$calculations = Expense::calculateExpensesByPeriod('6950fd9de6ca7');
		if (!$calculations) {
			echo "Unable to calculate expenses.\n";
		} else {
			$today = date('F d, Y');
			$thisMonth = date('F Y');
			$thisYear = date('Y');
			
			echo "\n" . str_repeat("=", 70) . "\n";
			echo str_pad("Expense Calculations Summary", 70, " ", STR_PAD_BOTH) . "\n";
			echo str_pad("Generated on: $today", 70, " ", STR_PAD_BOTH) . "\n";
			echo str_repeat("=", 70) . "\n\n";
			
			echo "TODAY ($today):\n";
			echo str_repeat("-", 70) . "\n";
			echo sprintf("  %-35s %30s\n", "Total Expenses:", 
				'$' . number_format($calculations['daily']['total'], 2));
			echo sprintf("  %-35s %30s\n", "Number of Transactions:", 
				$calculations['daily']['count']);
			if ($calculations['daily']['count'] > 0) {
				echo sprintf("  %-35s %30s\n", "Average per Transaction:", 
					'$' . number_format($calculations['daily']['average'], 2));
			}
			echo "\n";
			
			echo " MONTH ($thisMonth):\n";
			echo str_repeat("-", 70) . "\n";
			echo sprintf("  %-35s %30s\n", "Total Expenses:", 
				'$' . number_format($calculations['monthly']['total'], 2));
			echo sprintf("  %-35s %30s\n", "Number of Transactions:", 
				$calculations['monthly']['count']);
			if ($calculations['monthly']['count'] > 0) {
				echo sprintf("  %-35s %30s\n", "Average per Transaction:", 
					'$' . number_format($calculations['monthly']['average'], 2));
				
				$daysInMonth = date('t');
				$dailyAvg = $calculations['monthly']['total'] / $daysInMonth;
				echo sprintf("  %-35s %30s\n", "Average per Day (this month):", 
					'$' . number_format($dailyAvg, 2));
			}
			echo "\n";
			
			echo " YEAR ($thisYear):\n";
			echo str_repeat("-", 70) . "\n";
			echo sprintf("  %-35s %30s\n", "Total Expenses:", 
				'$' . number_format($calculations['yearly']['total'], 2));
			echo sprintf("  %-35s %30s\n", "Number of Transactions:", 
				$calculations['yearly']['count']);
			if ($calculations['yearly']['count'] > 0) {
				echo sprintf("  %-35s %30s\n", "Average per Transaction:", 
					'$' . number_format($calculations['yearly']['average'], 2));
				
				$currentMonth = date('n');
				$monthlyAvg = $calculations['yearly']['total'] / $currentMonth;
				echo sprintf("  %-35s %30s\n", "Average per Month (so far):", 
					'$' . number_format($monthlyAvg, 2));
			}
			
			echo str_repeat("=", 70) . "\n\n";
		}
	}

	public static function expenseStatsDisplay(){
		$period = UIDisplay::selectPeriod();
		$stats = Expense::getExpenseStats('694427f5b6a6d', $period);

		if (!$stats || $stats['total_expense'] == 0) {
			echo "No expense statistics available for this period.\n";
		} else {
			$periodTitle = match($period) {
				'day' => 'Today',
				'month' => 'This Month',
				'year' => 'This Year',
				'all' => 'All Time',
				default => ucfirst($period)
			};
			
			echo "\n" . str_repeat("=", 70) . "\n";
			echo str_pad("Expense Statistics - $periodTitle", 70, " ", STR_PAD_BOTH) . "\n";
			echo str_repeat("=", 70) . "\n\n";
			
			echo sprintf("%-40s %25s\n", "Total Expenses:", '$' . number_format($stats['total_expense'], 2));
			echo sprintf("%-40s %25s\n", "Average Expense:", '$' . number_format($stats['average_expense'], 2));
			echo str_repeat("-", 70) . "\n";
			
			echo sprintf("%-40s %25s\n", "Highest Expense:", '$' . number_format($stats['highest_expense'], 2));
			echo sprintf("%-40s %25s\n", "  Category:", $stats['highest_category'] ?? 'N/A');
			echo str_repeat("-", 70) . "\n";
			
			echo sprintf("%-40s %25s\n", "Lowest Expense:", '$' . number_format($stats['lowest_expense'], 2));
			echo sprintf("%-40s %25s\n", "  Category:", $stats['lowest_category'] ?? 'N/A');
			
			echo str_repeat("=", 70) . "\n\n";
		}
	}

	public static function expenditureReportDisplay(){
		$period = UIDisplay::selectPeriod();
		$report = Expense::getExpenditureReport('6950fd9de6ca7', $period);

		if (empty($report)) {
			echo "No expenses found for this period.\n";
		} else {
			$periodLabel = match($period) {
				'day' => 'Daily Breakdown',
				'month' => 'Monthly Breakdown',
				'year' => 'Yearly Breakdown',
				default => 'Expense Report'
			};
			
			$columnHeader = match($period) {
				'day' => 'Date',
				'month' => 'Month',
				'year' => 'Year',
				default => 'Period'
			};
			
			echo "\n" . str_repeat("=", 70) . "\n";
			echo str_pad("Expenditure Report - $periodLabel", 70, " ", STR_PAD_BOTH) . "\n";
			echo str_repeat("=", 70) . "\n";
			echo sprintf("%-30s %20s %15s\n", $columnHeader, "Amount", "% of Total");
			echo str_repeat("-", 70) . "\n";
			
			$grandTotal = array_sum(array_column($report, 'total'));
			
			foreach ($report as $row) {
				$label = $row['label'];
				$total = $row['total'];
				$percentage = ($grandTotal > 0) ? ($total / $grandTotal) * 100 : 0;
				
				echo sprintf(
					"%-30s %19s %14s%%\n",
					$label,
					'$' . number_format($total, 2),
					number_format($percentage, 1)
				);
			}
			
			echo str_repeat("-", 70) . "\n";
			
			$summaryText = match($period) {
				'day' => count($report) . " day(s)",
				'month' => count($report) . " month(s)",
				'year' => count($report) . " year(s)",
				default => count($report) . " period(s)"
			};
			
			echo sprintf("%-30s %19s %15s\n", 
				"TOTAL ($summaryText)", 
				'$' . number_format($grandTotal, 2),
				"100.0%"
			);
			
			$average = $grandTotal / count($report);
			$avgText = match($period) {
				'day' => 'Average per day',
				'month' => 'Average per month',
				'year' => 'Average per year',
				default => 'Average per period'
			};
			
			echo sprintf("%-30s %19s\n", 
				$avgText, 
				'$' . number_format($average, 2)
			);
			
			echo str_repeat("=", 70) . "\n\n";
		}

	}

	public static function searchExpensesDisplay(){
		$searchInput = UIDisplay::SearchInput();
		$expenses = Expense::searchExpensesByCategoryAndDate(
			'694427f5b6a6d',
			$searchInput['categoryName'],
			$searchInput['startDate'],
			$searchInput['endDate']
		);

		if (empty($expenses)) {
			echo "\nNo expenses found matching your search criteria.\n";
		} else {
			$searchDesc = [];
			if ($searchInput['categoryName']) {
				$searchDesc[] = "Category: '{$searchInput['categoryName']}'";
			}
			if ($searchInput['startDate']) {
				$searchDesc[] = "From: {$searchInput['startDate']}";
			}
			if ($searchInput['endDate']) {
				$searchDesc[] = "To: {$searchInput['endDate']}";
			}
			$searchText = empty($searchDesc) ? "All Expenses" : implode(" | ", $searchDesc);
			
			echo "\n" . str_repeat("=", 90) . "\n";
			echo str_pad("Search Results - $searchText", 90, " ", STR_PAD_BOTH) . "\n";
			echo str_repeat("=", 90) . "\n";
			echo sprintf("%-12s %-18s %-20s %-15s %-20s\n", 
				"Date", "Category", "Expense", "Amount", "Description");
			echo str_repeat("-", 90) . "\n";
			
			$totalAmount = 0;
			
			foreach ($expenses as $expense) {
				$totalAmount += $expense->getAmount();
				
				echo sprintf(
					"%-12s %-18s %-20s $%-14s %-20s\n",
					$expense->getDate()->format('Y-m-d'),
					substr($expense->getCategoryName(), 0, 17),
					substr($expense->getExpenseName(), 0, 19),
					number_format($expense->getAmount(), 2),
					substr($expense->getDescription(), 0, 19)
				);
			}
			
			echo str_repeat("=", 90) . "\n";
			echo sprintf("%-52s $%-14s\n", 
				"TOTAL (" . count($expenses) . " expenses)", 
				number_format($totalAmount, 2)
			);
			echo sprintf("%-52s $%-14s\n", 
				"AVERAGE", 
				number_format($totalAmount / count($expenses), 2)
			);
			echo str_repeat("=", 90) . "\n\n";
		}
	}
}

?>