<?php

class Budget{
	private string $id;
	private string $userId;
	private string $categoryName;
	private float $amount;
	private DateTimeImmutable $startDate;
	private DateTimeImmutable $endDate;
	private DateTimeImmutable $createdAt;
	private DateTimeImmutable $updatedAt;

	public function __construct
	(
		string $id,
		string $userId,
		string $categoryName,
		float $amount,
		DateTimeImmutable $startDate,
		DateTimeImmutable $endDate,
		DateTimeImmutable $createdAt,
		DateTimeImmutable $updatedAt
	)

	{
		$this->id = $id;
		$this->userId = $userId;
		$this->categoryName = $categoryName;
		$this->amount = $amount;
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		$this->createdAt = $createdAt;
		$this->updatedAt = $updatedAt;
	}


	public function getID(){ return $this->id;}
	public function getUserID(){return $this->userId;}
	public function getCategoryName(){ return $this->categoryName;}
	public function getAmount(){ return $this->amount;}
	public function getStartDate(){ return $this->startDate;}
	public function getEndDate(){ return $this->endDate;}
	public function getCreatedAt(){ return $this->createdAt;}
	public function getUpdatedAt(){ return $this->updatedAt;}


	public static function mapToBudgetRow(array $row){
		$id = $row['id'] ?? "";
		$userId = $row['user_id'] ?? "";
		$categoryName= $row['category_name'] ?? "";
		$amount= (float)$row['amount'] ?? 0;
		$startDate = new DateTimeImmutable($row['start_date'] ?? '');
		$endDate = new DateTimeImmutable($row['end_date'] ?? '');
		$createdAt = new DateTimeImmutable($row['created_at'] ?? 'now');
		$updatedAt = new DateTimeImmutable($row['updated_at'] ?? 'now');

		return new Budget($id, $userId, $categoryName, $amount, $startDate, $endDate, $createdAt, $updatedAt);
	}


	public static function findOneByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = "SELECT * FROM Budget WHERE id = :id";
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':id', $id);
		$stmt->execute();
		$budget = null;

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return $budget;
		}
		return self::mapToBudgetRow($row);
	}

	public static function findOneByUserID(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = "SELECT * FROM Budget WHERE user_id= :userId";
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':userId', $userId);
		$stmt->execute();
		$budget = null;
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return $budget;
		}
		return self::mapToBudgetRow($row);
	}
}

?>