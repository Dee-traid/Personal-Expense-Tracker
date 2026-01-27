<?php
public static function getCategoryInput(string $userId){
		if (!User::findOneByID($userId)) {
            throw new Exception(" User not found");
            return null;
          }

		$categoryName = CLIHelper::validateInput(" Enter Category Name");
		$description = CLIHelper::validateInput(" Enter Category Description", 2, true);
		return [
			'categoryName' =>$categoryName, 
			'description' =>$description
		];
	}

    public static function categoryUpdateInput(string $id){
		$category = self::findOneByID($id);
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


?>