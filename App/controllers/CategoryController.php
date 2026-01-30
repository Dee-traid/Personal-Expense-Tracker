<?php

namespace App\Controllers;

use App\Models\Category;
use App\Views\Inputs\CategoryInput;
use App\Views\CLIHelper;
use App\Core\DatabaseHelper;
use App\Views\UIDisplay;
use App\Core\UtilityFunction;
use PDO;
use PDOException;
use DateTimeImmutable;

class CategoryController{

	public static function addCategory(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$input = CategoryInput::getCategoryInput($userId);
		extract($input); 
		$id = uniqid();
		$timeStamp = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'); 
		$query = "INSERT INTO categories (id, user_id, category_name,description,created_at,updated_at) VALUES (:id, :userId,  :categoryName, :description, :createdAt, :updatedAt)";
		
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':userId', $userId);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':description', $description);
			$stmt->bindparam(':createdAt', $timeStamp);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Added sucessfully");
		}catch(PDOException $e){

			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}	
	}

	public static function viewAllCategories(string $userId, bool $sortByDescending = true){
		$pdo = DatabaseHelper::getPDOInstance();
		$sort = $sortByDescending ? "desc" : "asc";
		$query = " SELECT * FROM  Categories WHERE user_id = :userId ORDER BY created_at $sort";
		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();

			$categories = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$categories[] = Category::mapToCategoryRow($row);
			}
			
			UIDisplay::displayCategoryTable($categories);
			return $categories;
		}catch (PDOException $e) {
			CLIHelper:: error(" Unknown Error" . $e->getMessage());
		}
	}

	public static function updateCategoryDetails(string $userId) {
		$pdo = DatabaseHelper::getPDOInstance();
		$categories = self::viewAllCategories($userId);
		if (empty($categories)) {
			CLIHelper::error(" No category found.");
			return null;
		}

		$choice = (int)CLIHelper::getInput("Enter the S/N to select the category");
    	$Index = $choice - 1; 

		if (!isset($categories[$Index])) {
			CLIHelper::error("Invalid selection.");
			return null;
   		}
		$selectedCategory = $categories[$Index];
    	$categoryId = $selectedCategory->getId();

		$input = CategoryInput::categoryUpdateInput($categoryId);
		if(!$input) return null;
		extract($input);
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");

		$query = " UPDATE Categories SET category_name = :categoryName , description = :description, updated_at = :updatedAt WHERE  id = :categoryId";
		$confirm = UtilityFunction::confirm(" Do you want to Update? ");
		try{
			if($confirm){
				$stmt = $pdo->prepare($query);
				$stmt->bindparam(':categoryId', $categoryId);
				$stmt->bindparam(':categoryName', $categoryName);
				$stmt->bindparam(':description', $description);
				$stmt->bindparam(':updatedAt', $timeStamp);
				$stmt->execute();

				CLIHelper::success(" Updated successfully");

				if($stmt->rowCount() > 0){
					$category = Category::findOneByID($id);
					return $category;
				}
			}
			
		}catch(PDOException $e){
			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}

	}

	public static function deleteCategoryByID(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$categories = self::viewAllCategories($userId);
		if (empty($categories)) {
			CLIHelper::error(" No category found.");
			return null;
		}

		$choice = (int)CLIHelper::getInput("Enter the S/N to select the category");
    	$Index = $choice - 1; 

		if (!isset($categories[$Index])) {
			CLIHelper::error("Invalid selection.");
			return null;
   		}
		$selectedCategory = $categories[$Index];
    	$categoryId = $selectedCategory->getId();

		$query = "DELETE FROM  Categories WHERE id = :categoryId";
		$confirm = UtilityFunction::confirm("Do you want to delete? (y/n): ");
		try{
			if($confirm){
				$stmt = $pdo->prepare($query);
				$stmt->bindparam(':categoryId', $categoryId);
				$stmt->execute();
				
				if($stmt->rowCount()  > 0){
					CLIHelper::success(" Category '{$selectedCategory->getCategoryName()}' deleted successfully.");
					return $selectedCategory;
				}
				return null;
			}
			
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function deleteAllCategory($userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = UtilityFunction::confirm(" Do you want to delete? (y/n): ");
		$confirm1= UtilityFunction::confirm(" Dangerous operation!!!!,  Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM Categories WHERE user_id = :userId"; 
				$stmt = $pdo->prepare($query);
				$stmt->bindParam(':userId', $userId);
				$stmt->execute();

				if($stmt->rowCount() > 0){
					CLIHelper:: success(" Deleted sucessfully");
				}
			}else{
				CLIHelper:: error(" Deletion Cancelled");
			}

		}catch(PDOException $e){
			
			CLIHelper:: error(" Unknown Error" . $e->getMessage());
		}
	}	

}

?>