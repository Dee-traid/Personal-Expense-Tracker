<?php
namespace App\Views\Inputs;

use App\Models\User;
use App\Models\Expense;
use App\Views\CLIHelper;
use Exception;

class ExpenseInput{
    	public static function getExpenseInput(string $userId){
		$user = User::findOneByID($userId);
		if (!$user) {
            CLIHelper::error(" User not found");
            return null;
          }

		$expenseName = CLIHelper::validateInput(" Expense Name", 2, true);
		$amount = CLIHelper::getAmount(" Amount",  true);
		$date = CLIHelper::getDateInput(" Date");
		$description = CLIHelper::validateInput(" Description", 2, true);

		return [
			'expenseName' => $expenseName,
			'amount' => $amount,
			'date' => $date,
			'description' => $description
		];

	}

    public static function updateExpenseInput(string $id){
		$expense = Expense::findOneByID($id);
		if(!$expense){
			CLIHelper:: error(" Expense not found");
			return null;
		}
		$newExpenseName = CLIHelper::validateInput(" Expense Name [" . $expense->getExpenseName() ."]", 2 , true);
		$newAmount = CLIHelper::getAmount(" Amount [" . $expense->getAmount() . "]", true);
		$newDescription = CLIHelper::validateInput(" Description [" . $expense->getDescription() . "]", 2, true);

		return [
			'expenseName' => ($newExpenseName !== null) ? $newExpenseName : $expense->getExpenseName(),
			'amount' => ($newAmount !== "") ? $newAmount : $expense->getAmount(),
			'description' => ($newDescription !== null) ? : $expense->getDescription()
		];
	}
}
?>