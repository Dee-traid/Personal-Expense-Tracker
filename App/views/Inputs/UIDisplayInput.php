<?php
class UIDisplayInput{
    public static function selectPeriod() {
		echo "\nSelect Period:\n";
		echo "1. Today\n";
		echo "2. This Month\n";
		echo "3. This Year\n";
		echo "4. All Time\n";
		$choice = CLIHelper::getInput("Enter choice (1-4)");
		return match($choice) {
			'1' => 'day',
			'2' => 'month',
			'3' => 'year',
			'4' => 'all',
			default => 'all'
		};
	}

	public static function searchInput() {
		echo "\n=== SEARCH EXPENSES ===\n";	
		$input = CLIHelper::getInput(" Search by Category Name and/or Date Range (YYYY-MM-DD to YYYY-MM-DD) ");	
		if (empty($input)) {
			return null;
		}
		
		$categoryName = null;
		$startDate = null;
		$endDate = null;
		
		$dateRange = preg_match('/(\d{4}-\d{2}-\d{2})\s+to\s+(\d{4}-\d{2}-\d{2})/i', $input, $dateMatches);
		switch (true) {
			case $dateRange && strlen($input) > strlen($dateMatches[0]):
				$categoryName = trim(str_replace($dateMatches[0], '', $input));
				$startDate = $dateMatches[1];
				$endDate = $dateMatches[2];
				break;
				
			case $dateRange:
				$startDate = $dateMatches[1];
				$endDate = $dateMatches[2];
				break;
				
			default:
				$categoryName = $input;
				break;
		}
		
		return [
			'categoryName' => $categoryName,
			'startDate' => $startDate,
			'endDate' => $endDate
		];
	}
}

?>