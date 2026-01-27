<?php

public static function addCategory(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$input = self::getCategoryInput($userId);
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

	public static function ViewAllCategories(string $userId, bool $sortByDescending = true){
		$pdo = DatabaseHelper::getPDOInstance();
		$sort = $sortByDescending ? "desc" : "asc";
		$query = " SELECT * FROM  Categories WHERE user_id = :userId ORDER BY created_at $sort";
		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();

			$categories = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$categories[] = self::mapToCategoryRow($row);
			}
			return $categories;
		}catch (PDOException $e) {
			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}
	}

	public static function updateCategoryDetails(string $id) {
		$pdo = DatabaseHelper::getPDOInstance();
		$input = self::categoryUpdateInput($id);
		if(!$input) return null;
		extract($input);
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");

		$query = " UPDATE Categories SET category_name = :categoryName , description = :description,    updatedAt = :updatedAt WHERE  id = :id";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':description', $description);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Updated successfully");

			if($stmt->rowCount() > 0){
				$category = self::findOneByID($id);
				return $category;
			}

		}catch(PDOException $e){
			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}

	}

	public static function deleteCategoryByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$category = self::findOneByID($id);
		if(!$category) {
			CLIHelper::error("Category with ID '$id' not found.");
			return null;
		}

		$query = "DELETE FROM  Categories WHERE id = :id";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->execute();
			
			if($stmt->rowCount()  > 0){
				CLIHelper::success(" Category '{$category->getCategoryName()}' deleted successfully.");
				return $category;
			}
			return null;
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function DeleteAllCategory(){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = AppManager::confirm("Do you want to delete? (y/n): ");
		$confirm1= AppManager::confirm("Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM Categories"; 
				$stmt = $pdo->prepare($query);
				$stmt->execute();

				if($stmt->rowCount() > 0){
					CLIHelper:: success(" Deleted sucessfully");
				}
			}else{
				CLIHelper:: error(" Deletion Cancelled");
			}

		}catch(PDOException $e){
			
			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}
	}	


?>