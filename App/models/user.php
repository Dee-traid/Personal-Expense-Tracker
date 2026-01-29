<?php
namespace App\Models;

use App\Core\DatabaseHelper;
use DateTimeImmutable;
use PDO;
use PDOException;

class User{
	public static ?string $loggedInUserID = null;
	private string $id;
	private string $userName;
	private string $email;
	private string $phoneNo;
	private string $password;
	private float $income;
	private DateTimeImmutable $createdAt;
	private DateTimeImmutable $updatedAt;

	
	
	public function __construct(
		string $id,
		string $userName, 
		string $email, 
		string $phoneNo,
		string $password,
		float $income,
		DateTimeImmutable $createdAt,
		DateTimeImmutable $updatedAt
	)
	{
		$this->id = $id;
		$this->userName = $userName;
		$this->email = $email;
		$this->phoneNo = $phoneNo;
		$this->password = $password;
		$this->income = $income;
		$this->createdAt= $createdAt;
		$this->updatedAt= $updatedAt;
	}

	public function getID(){ return $this->id;}
	public function getUserName(){ return $this->userName;}
	public function getEmail(){ return $this->email;}
	public function getPhoneNo(){ return $this->phoneNo;}
	public function getPassword(){ return $this->password;}
	public function getIncome(){ return $this->income;}
	public function getCreatedAt(){ return $this->createdAt;}
	public function getUpdatedAt(){ return $this->updatedAt;}
	
	public static function mapToUserRow(array $row){
		$id = $row['id'] ?? "";
		$userName = $row['user_name'] ?? "";
		$email = $row['email'] ?? "";
		$phoneNo = $row['phone_number'] ?? "";
		$password = $row ['password'] ?? "";
		$income = (float)($row['income'] ?? 0);
		$createdAt = new DateTimeImmutable($row['created_at'] ?? 'now');
		$updatedAt = new DateTimeImmutable($row['updated_at'] ?? 'now');

		return new User($id, $userName, $email, $phoneNo, $password, $income, $createdAt, $updatedAt);
	}

	public static function findOneByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = " SELECT * FROM Users WHERE id = :id";
		$user = null;
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':id', $id);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return $user;
		}
		return User::mapToUserRow($row);
	}

	public static function findOneByUserName(string $userName){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = " SELECT * FROM Users WHERE user_name = :userName";
		$user = null;
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':userName', $userName);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return $user;
		}
		return User::mapToUserRow($row);
	}

}
	
?>