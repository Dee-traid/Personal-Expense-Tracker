<?php
class ExpenseInputView{
    	public static function getExpenseInput(string $userId){
		$user = User::findOneByID($userId);
		if (!$user) {
            CLIHelper::error(" User not found");
            return null;
          }

		$categoryName = CLIHelper::validateInput(" Category Name", 2 , true);
		$expenseName = CLIHelper::validateInput(" Expense Name", 2, true);
		$amount = CLIHelper::getAmount(" Amount",  true);
		$date = CLIHelper::getDateInput(" Date");
		$description = CLIHelper::validateInput("Description", 2, true);

		return [
			'categoryName' => $categoryName,
			'expenseName' => $expenseName,
			'amount' => $amount,
			'date' => $date,
			'description' => $description
		];

	}

    public static function updateExpenseInput(string $id){
		$expense = self::findOneByID($id);
		if(!$expense){
			CLIHelper:: error(" Expense not found");
			return null;
		}
		$newCategoryName = CLIHelper::validateInput(" Category Name [" . $expense->getCategoryName() . "]", 2, true);
		$newExpenseName = CLIHelper::validateInput(" Expense Name [" . $expense->getExpenseName() ."]", 2 , true);
		$newAmount = CLIHelper::validateInput(" Amount [" . $expense->getAmount() . "]");
		$newDescription = CLIHelper::validateInput(" Description [" . $expense->getDescription() . "]", 2, true);

		return [
			'categoryName' => ($newCategoryName !== null) ? $newCategoryName : $expense->getCategoryName(),
			'expenseName' => ($newExpenseName !== null) ? $newExpenseName : $expense->getExpenseName(),
			'amount' => ($newAmount !== null) ? $newAmount : $expense->getAmount(),
			'description' => ($newDescription !== null) ? : $expense->getDescription()
		];
	}
}
?>