<?php


class Expense{
	private string $id;
	private string $userId;
	private string $categoryName;
	private string $expenseName;
	private float $amount;
	private  DateTimeImmutable $date;
	private string  $description;
	private DateTimeImmutable $createdAt;
	private DateTimeImmutable $updatedAt;

	public function __construct
	(
		string $id,
		string $userId,
		string $categoryName,
		string $expenseName,
		float  $amount,
		DateTimeImmutable $date,
		string $description,
		DateTimeImmutable $createdAt,
		DateTimeImmutable $updatedAt
	)

	{
		$this->id = $id;
		$this->userId = $userId;
		$this->categoryName = $categoryName;
		$this->expenseName = $expenseName;
		$this->amount = $amount;
		$this->date = $date;
		$this->description = $description;
		$this->createdAt = $createdAt;
		$this->updatedAt = $updatedAt;
	}

	public function getID(){ return $this->id ;}
	public function getUserID(){ return $this->userId;}
	public function getCategoryName(){ return $this->categoryName;}
	public function getExpenseName(){ return $this->expenseName;}
	public function getAmount(){ return $this->amount;}
	public function getDate(){ return $this->date;}
	public function getDescription(){ return $this->description;}
	public function getCreatedAt(){ return $this->createdAt;}
	public function getUpdatedAt(){ return $this->updatedAt;}

	public static function mapToExpenseRow(array $row){
		$id = $row['id'] ?? "";
		$userId = $row['user_id'] ?? "";
		$categoryName = $row['category_name'] ?? "";
		$expenseName =$row['expense_name'] ?? "";
		$amount = (float)  $row['amount'] ?? "";
		$date = new DateTimeImmutable ($row['date']?? 'now');
		$description = $row['description'] ?? "";
		$createdAt = new DateTimeImmutable ($row['created_at']?? 'now');
		$updatedAt = new DateTimeImmutable ($row['updated_at'] ?? 'now');

		return new Expense($id, $userId, $categoryName, $expenseName,$amount,  $date, $description, $createdAt, $updatedAt);
	}

	public static function findOneByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = "SELECT * FROM Expenses WHERE id = :id";
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':id', $id);
		$stmt->execute();
		$Expense = null;

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return $Expense;
		}
		return self::mapToExpenseRow($row);
	}

}

?>