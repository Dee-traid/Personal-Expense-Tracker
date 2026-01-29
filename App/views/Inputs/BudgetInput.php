<?
namespace App\Views\Inputs;

use App\Models\Budget;
use App\Models\User;
use App\View\CLIHelper;
use Exception;

class BudgetInput{
	public static function budgetInput(string $userId){
		$user = Budget::findOneByID($userId);
		if (!$user) {
		throw new Exception(" User not found");
		return null;
        }
		
        $categoryName = CLIHelper::validateInput(" Category Name", 2, true);
        $amount = CLIHelper::getAmount(" Amount ", true);
        $startDate = CLIHelper::getDateInput(" Start Date");
        $endDate = CLIHelper::getDateInput(" End Date");
        return [
        	'userId'=> $userId,
        	'categoryName' => $categoryName,
        	'amount' => $amount,
        	'startDate'  => $startDate,
        	'endDate' => $endDate 
        ];

	}

	public static function updateBudgetInput($id){
		$budget = Budget::findOneByID($id);
		if(!$budget){
			CLIHelper::error(" Budget not found");
			return null;
		}

		$newCategoryName =CLIHelper::validateInput(" Category Name [" . $budget->getCategoryName() . "]", 2 , true);
		$newAmount = CLIHelper::getAmount(" Amount [" .  $budget->getAmount() . "]",true);
		$newStartDate = CLIHelper::getDateInput(" Start Date [" . $budget->getStartDate()->format('Y-m-d') . "]");
		$newEndDate = CLIHelper::getDateInput(" End Date [" . $budget->getEndDate()->format('Y-m-d') . "]" );


		return [
			'categoryName' => ($newCategoryName !== "") ? $newCategoryName : $budget->getCategoryName(),
			'amount' => ($newAmount !== "") ? $newAmount : $budget->getAmount(),
			'startDate' => ($newStartDate !== "") ? $newStartDate : $budget->getStartDate()->format('Y-m-d'),
			'endDate' => ($newEndDate !== "") ? $newEndDate : $budget->getEndDate()->format('Y-m-d')

		];

	}
}


?>