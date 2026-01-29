<?php
namespace App\Views\Inputs;

use App\Models\Category;
use App\Models\User;
use App\Views\CLIHelper;
use Exception;

class CategoryInput{
	public static function getCategoryInput(string $categoryId){
		if (!User::findOneByID($categoryId)) {
            CLIHelper::error(" Category not found");
            return null;
        }

		$categoryName = CLIHelper::validateInput(" Enter Category Name");
		$description = CLIHelper::validateInput(" Enter Category Description", 2, true);
		return [
			'categoryName' =>$categoryName, 
			'description' =>$description
		];
	}

    public static function categoryUpdateInput(string $categoryId){
		$category = Category::findOneByID($categoryId);
	    if (!$category) {
	        CLIHelper::error("User not found");
	        return null;
	    }

		$newCategoryName = CLIHelper::validateInput(" Category Name [" . $category->getCategoryName() . " ]", 2 , true);
		$newDescription = CLIHelper::validateInput(" Description [" . $category->getDescription() . "]", 2 , true);

		return[

			'categoryName' => !empty($newCategoryName) ? $newCategoryName : $category->getCategoryName(),
			'description' => !empty($newDescription) ? $newDescription : $category->getDescription()
		];
	}
}

?>