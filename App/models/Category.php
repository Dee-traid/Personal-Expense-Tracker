<?php
namespace App\Models;

use App\Core\DatabaseHelper;
use DateTimeImmutable;
use PDO;
use PDOException;

class Category{
	private string $id;
	private string $categoryName;
	private string $description;
	private DateTimeImmutable $createdAt;
	private DateTimeImmutable $updatedAt;


	public function __construct(
		string $id,
		string $categoryName,
		string $description,
		DateTimeImmutable $createdAt,
		DateTimeImmutable $updatedAt
	)

	{
		$this->id = $id;
		$this->categoryName = $categoryName;
		$this->description = $description;
		$this->createdAt = $createdAt;
		$this->updatedAt = $updatedAt;
	}

	public function getID(){return $this->id;}
	public function getCategoryName(){return $this->categoryName;}
	public function getDescription(){return $this->description;}
	public function getCreatedAt(){return $this->createdAt;}
	public function getUpdatedAt(){return $this->updatedAt;}


	public static function mapToCategoryRow(array $row){
		$id = $row['id'] ?? "";
		$categoryName = $row['category_name'] ?? "";
		$description = $row['description'] ?? "";
		$createdAt = new DateTimeImmutable ($row['created_at']?? 'now');
		$updatedAt = new DateTimeImmutable ($row['updated_at'] ?? 'now');

		return new Category($id, $categoryName, $description, $createdAt, $updatedAt);
	}

	public static function findOneByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = "SELECT * FROM Categories WHERE id = :id LIMIT 1";
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':id', $id);
		$stmt->execute();
		$category = null;

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return $category;
		}

		return Category::mapToCategoryRow($row);
	}

	public static function findOneByCategoryName(string $categoryName){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = "SELECT * FROM Categories WHERE category_name = :categoryName";
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':categoryName', $categoryName);
		$stmt->execute();
		$Category = null;

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return $Category;
		}
		return Category::mapToCategoryRow($row);
	}

}


?>