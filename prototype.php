<?php

class DatabaseHelper{
	private static $pdo = null;

	public static function getPDOInstance(){
		if (self::$pdo != null) {
			return self::$pdo;
		}

		$host = 'localhost';
		$port = '5432';
		$user = 'postgres';
		$password = 'Traid101';
		$database = 'expense_tracker';

		$dsn = "pgsql:host=$host;port=$port;dbname=$database;";
		try{
			self::$pdo = new PDO($dsn, $user, $password);
			self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch(PDOException $e){
			CLIHelper:: error(" Connection Failed: " . $e->getMessage());
		}
		return self::$pdo;
	}
}

class CLIHelper{
	public static function getInput(string $prompt) {
		echo $prompt . ": ";
		$input = trim(readline());
		return $input;
	}

	public static function validateInput($prompt, $minLength = 2, $required = false ){
		while (true) {
			$input = self::getInput($prompt);
			if(empty($input) && $required) return "";

			if(empty($input) || strlen($input) < $minLength){
				self::error(' Field should not be empty and must contain more that $minLength character');
				continue;
			}
			return $input;
		}
	}

	public static function validateEmail(string $prompt, $required = false){
		while(true){
			$input = self::getInput($prompt);
			if(empty($input) && $required) return "";

			if(!filter_var($input, FILTER_VALIDATE_EMAIL)){
				self::error(' Invalid email input');
				continue;
			}
			return $input;
		}
	}

	public static function validatePhoneNumber(string $prompt, $required = false){
		while (true) {
			$input = self::getInput($prompt);
			if(empty($input) && $required) return "";

			if(!preg_match('/^\+?[0-9]{10,15}$/', $input)){
				self::error("Invalid phone. Use 10-15 digits (e.g. +2348000000000)");
				continue;
			}
			return $input;
		}
	}

	public static function validatePassword(string $prompt){
		while (true) {
			$input = self::getInput($prompt);
			if(!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $input)){
				self::error(" Password must contain atleast 8 characters, 1 letter and 1 number");
				continue;
			}
			return $input;
		}
	}

	public static function passwordHash(){
		while(true){
			$password1 = self::validatePassword(' Enter your password');
			$password2 = self::validatePassword(' Confirm your password');

			if($password1 !== $password2){
				self::error(' Password does not match, Try again');
				continue;
			}
			$hashedPassword = password_hash($password1, PASSWORD_DEFAULT);
			return $hashedPassword;
		}
	}

	public static function error($message){
		echo "\033[31mError: $message\033[0m" . PHP_EOL;
	}

	public static function success($message){
		echo "\033[32m$message\033[0m" . PHP_EOL;
	}

	public static function getAmount(string $prompt, $required = false){
		while(true){
			$input = self::getInput($prompt);
			if(empty($input) && $required) return "";

			if(is_numeric($input) && (float)$input >= 0){
				return (float)$input;
			}
			self::error(" | Invalid input, Enter a postive number. ");
		}

	}

	public static function getDateInput(string $prompt, bool $required = false, string $default = 'now'): string{
		while(true){
			$input = self::getInput( "$prompt (YYYY-MM-DD) [Default: $default] ");
			if(empty($input)) return (new DateTimeImmutable ($default))->format('Y-m-d');

			$d = DateTime::createFromFormat('Y-m-d', $input);
			if($d && $d->format('Y-m-d') === $input){
				return $input;
			}
			self::error(" Invalid date format, Use YYYY-MM-DD");
		}
	}

}

class AppManager{
	public static function confirm($prompt = "Continue? (y/n): "){
	    while (true) {
	        $ans = strtolower(readLine($prompt));
	        if (in_array($ans, ['y', 'yes'], true)) return true;
	        if (in_array($ans, ['n', 'no'], true)) return false;
		        echo "Please answer y or n.\n";
	    }
	} 
}

class User{
	private string $id;
	private string $userName;
	private string $email;
	private string $phoneNo;
	private string $password;
	private float $income;
	private DateTimeImmutable $createdAt;
	private DateTimeImmutable $updatedAt;

	private static ?string $userLoggedInID = null;
	
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
	
	public static function mapToUsersRow(array $row){
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
		return self::mapToUsersRow($row);
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
		return self::mapToUsersRow($row);
	}

	public static function getUserInput(){
		$userName = CLIHelper::validateInput(" Enter your Username");
		$email = CLIHelper::validateEmail(" Enter your email");
		$income = CLIHelper::getAmount(" Enter your Income");
		$phoneNo = CLIHelper::validatePhoneNumber(" Enter your Phone number");
		$password = CLIHelper::passwordHash(" Create Password");

		return [
			'userName' =>$userName, 
			'email' =>$email, 
			'phoneNo' =>$phoneNo,
			'password' =>$password, 
			'income' =>$income
		];
	}

	public static function userRegistration(){
		$pdo = DatabaseHelper::getPDOInstance();
		
		try{
			$identifier = CLIHelper::validateInput(" Enter your email/ password");
			$stmt = $pdo->prepare(" SELECT * FROM Users WHERE email = :email OR  phone_number = :phoneNo;");
			$stmt->bindparam(':email', $identifier);
			$stmt->bindparam(':phoneNo', $identifier);
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);			
			if(!empty($row)){
				CLIHelper::error (" User exists. Please Login");
				return self::userLogin();
			}

			$id = uniqid();
			$timeStamp = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
			$input = self::getUserInput();
			if(!$input) return null;
			extract($input);

			$query = " INSERT INTO Users(id, user_name, email, phone_number, password, income, created_at, updated_at) 
			 VALUES (:id, :userName, :email, :phoneNo, :password, :income, :createdAt, :updatedAt)";

			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':userName', $userName);
			$stmt->bindparam(':email', $email);
			$stmt->bindparam(':phoneNo', $phoneNo);
			$stmt->bindparam(':password', $password);
			$stmt->bindparam(':income', $income);
			$stmt->bindparam(':createdAt', $timeStamp);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Registration successful");

			return self::findOneByID($id);

		}catch(PDOException $e){
			CLIHelper::error(" Registration Failed" . $e->getMessage());
			return null;
		}

	}

	public static function loginInput(){
		$identifier = CLIHelper::validateInput(" Enter Email or Phone Number");
		$password = CLIHelper::validatePassword(" Enter password");

		return [
        'identifier' => $identifier,
        'password'   => $password
    	];
	}

	public static function userLogin(){
		$pdo = DatabaseHelper::getPDOInstance();

		$input= self::loginInput();
		if(!$input) return null;
		extract($input);

		$query = "SELECT * FROM Users WHERE email = :email OR phone_number = :phoneNo";

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':email', $identifier);
			$stmt->bindparam(':phoneNo', $identifier);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			$confirm = AppManager::confirm($prompt = "Login? (y/n): ");
			if($confirm){
				if($user && password_verify($password, $user['password'])){
					self::$userLoggedInID = $user['id'];
					CLIHelper::success(" Login successful");
						return self::mapToUsersRow($user);
				}else{
					CLIHelper::error(" Login Failed, Invalid Credentials");
					return null;
				}
			}

		}	catch(PDOException $e){
				CLIHelper::error(" Login Failed" . $e->getMessage());
		}

	}

	public  static function resetPassword(){
		$pdo = DatabaseHelper::getPDOInstance();

		$identifier = CLIHelper::validateInput(" Enter your Email or Phone Number");
		$query = " SELECT * FROM  Users WHERE email = :identifier OR phone_number = :identifier";
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':identifier', $identifier);
		$stmt->execute();

		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$user) {
			CliHelper::error(" User does not exist");
			self::resetPassword();
			return false;
		}
		$pass_token = (string)random_int(100000, 999999);
		sleep(2);
		$startTime = time();
		CLIHelper:: success(" Recovery code: " . $pass_token);

		while(true){
			if ((time() - $startTime) > 60) {
				CLIHelper::error(" Time expired, Generate a new one ");
				return self::resetPassword();
			}

			$userInput = CliHelper::getInput(" Enter the 6-digit recovery code");
			if($userInput !== $pass_token){
				CLIHelper::error( " Invalid Token , Try again");
				continue;
			}
			$newPass = CLIHelper::validatePassword(" Enter a new password");
			$confirmPass = CLIHelper::validatePassword(" Confirm new password");

			if($newPass !== $confirmPass){
				 CLIHelper::error(" Password does not match");
				continue;
			}
			break;
		}				

		$passHash = password_hash($newPass, PASSWORD_DEFAULT);
		$id = $user['id'];
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");

		try{
			$updateQuery = " UPDATE Users SET password = :password, updated_at = :updatedAt WHERE id = :id";

			$updateStmt = $pdo->prepare($updateQuery);
			$updateStmt->bindparam(':id', $id);
			$updateStmt->bindparam(':password', $passHash);
			$updateStmt->bindparam(':updatedAt', $timeStamp);
			$updateStmt->execute();


			CliHelper::success("Password Updated successfully");
			return true;
		}catch(PDOException $e){
			CliHelper::error(" Unknown error: " . $e->getMessage());
			return false;
		}
		
	}

	public static function ViewAllUsers(string $id, bool $sortByDescending = true){
		$pdo = DatabaseHelper::getPDOInstance();
		$sort = $sortByDescending ? "desc" : "asc";
		$query = " SELECT * FROM  Users ORDER BY created_at  $sort";

		try {
			$stmt = $pdo->prepare($query);
			$stmt->execute();

			$users = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$users[] = self::mapToUsersRow($row);
			}

			return $users;
		} catch (PDOException $e) {
			 CLIHelper::error(" Unknown Error" . $e->getMessage());
		}
	}

	public static function updateUserInput(string $id){
		$user = self::findOneByID($id);
	    if (!$user) {
	        CLIHelper::error("User not found");
	        return null;
	    }
		
		$newUserName = CLIHelper::validateInput(" Name [". $user->getUserName() . "]", 2, true);
		$newEmail = CLIHelper::validateEmail(" Email [". $user->getEmail() . "]", true);
		$newPhoneNo  = CLIHelper::validatePhoneNumber(" Phone number [" . $user->getPhoneNo() . "]", true);
		$newIncome   = CLIHelper::getAmount("Income [" . $user->getIncome() . "]", true);

		return[
			'userName'=>($newUserName !== "") ? $newUserName : $user->getUserName(),
		    'email'   => ($newEmail !== "")  ? $newEmail    : $user->getEmail(),
		    'phoneNo'  =>($newPhoneNo !== "")   ? $newPhoneNo    : $user->getPhoneNo(),
		    'income'  => ($newIncome !== "") ? $newIncome   : $user->getIncome()
		];
	}

	public static function updateUserDetails(string $id) {
		$pdo = DatabaseHelper::getPDOInstance();
		$input = self::updateUserInput($id);
		if(!$input) return null;
		extract($input);
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");

		$query = " UPDATE Users SET user_name = :userName , email = :email,  phone_number = :phoneNo, income = :income,  updated_at = :updatedAt WHERE  id = :id";

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':userName', $userName);
			$stmt->bindparam(':email', $email);
			$stmt->bindparam(':phoneNo', $phoneNo);
			$stmt->bindparam(':income', $income);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Updated sucessfully");

			if($stmt->rowCount() > 0){
				$user = self::findOneByID($id);
				return $user;
			}

		}catch(PDOException $e){
			 CLIHelper::error(" Unknown Error" . $e->getMessage());
		}

	}

	public static function deleteUserByID( string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$user = self::findOneByID($id);
		if(!$user) {
			CLIHelper::error(" User not found");
			return null;
		}

		$query = "DELETE FROM  Users WHERE id = :id";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->execute();
			
			if($stmt->rowCount()  > 0){
				CLIHelper::success("User '{$user->getUserName()}' deleted successfully.");
				return $user;
			}
			return null;
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function DeleteAllUser(){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = AppManager::confirm("Do you want to delete? (y/n): ");
		$confirm1= AppManager::confirm("Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM Users "; 
				$stmt = $pdo->prepare($query);
				$stmt->execute();

				if($stmt->rowCount() > 0){
					CLIHelper::success(" Deleted sucessfully");
				}
			}else{
				CLIHelper::error(" Deletion Cancelled" );
			}

		}catch(PDOException $e){
			 CLIHelper::error(" Unknown Error" . $e->getMessage());
		}
	}
	
}


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
		$query = "SELECT * FROM Categories WHERE id = :id";
		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':id', $id);
		$stmt->execute();
		$category = null;

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return $category;
		}

		return self::mapToCategoryRow($row);
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
		return self::mapToCategoryRow($row);
	}

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

}


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


	public static function getExpenseInput(string $userId){
		$user = User::findOneByID($userId);
		if (!$user) {
            CLIHelper::error(" User not found");
            return null;
          }

		$categoryName = CLIHelper::validateInput(" Category Name", 2 , true);
		$expenseName = CLIHelper::validateInput(" Expense Name", 2, true);
		$amount = CLIHelper::getAmount(" Amount",  true);
		$date = CLIHelper::getDateInput(" Date");
		$description = CLIHelper::validateInput("Description", 2, true);

		return [
			'categoryName' => $categoryName,
			'expenseName' => $expenseName,
			'amount' => $amount,
			'date' => $date,
			'description' => $description
		];

	}

	public static function addExpense(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$input = self::getExpenseInput($userId);
		extract($input);

		if(!$input) return null;

		$id = uniqid();
		$timeStamp = (new DateTimeImmutable('Now'))->format("Y-m-d H:i:s");
      
		try{
			$query = " INSERT INTO Expenses (id, user_id, category_name, expense_name, amount, date, description, created_at, updated_at) VALUES (:id, :userId, :categoryName, :expenseName, :amount, :date, :description, :createdAt, :updatedAt)";

			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':userId', $userId);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':expenseName', $expenseName);
			$stmt->bindparam(':amount', $amount);
			$stmt->bindparam(':date', $date);
			$stmt->bindparam(':description', $description);
			$stmt->bindparam(':createdAt', $timeStamp);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper:: success(" Added successfully");
			return self::findOneByID($id);

		}catch(PDOException $e){
			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}
	}

	public static function ViewAllExpenses(string $userId, bool $sortByDescending = true){
		$pdo = DatabaseHelper::getPDOInstance();
		$sort = $sortByDescending ? "desc" : "asc";
		$query = " SELECT * FROM  Expenses WHERE user_id = :userId ORDER BY created_at $sort";
		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();

			$expenses = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$expenses[] = self::mapToExpenseRow($row);
			}
			return $expenses;
		} catch (PDOException $e) {
			CLIHelper::error(" Unknown Error: " . $e->getMessage());
		}
	}

	public static function updateExpenseInput(string $id){
		$expense = self::findOneByID($id);
		if(!$expense){
			CLIHelper:: error(" Expense not found");
			return null;
		}
		$newCategoryName = CLIHelper::validateInput(" Category Name [" . $expense->getCategoryName() . "]", 2, true);
		$newExpenseName = CLIHelper::validateInput(" Expense Name [" . $expense->getExpenseName() ."]", 2 , true);
		$newAmount = CLIHelper::validateInput(" Amount [" . $expense->getAmount() . "]");
		$newDescription = CLIHelper::validateInput(" Description [" . $expense->getDescription() . "]", 2, true);

		return [
			'categoryName' => ($newCategoryName !== null) ? $newCategoryName : $expense->getCategoryName(),
			'expenseName' => ($newExpenseName !== null) ? $newExpenseName : $expense->getExpenseName(),
			'amount' => ($newAmount !== null) ? $newAmount : $expense->getAmount(),
			'description' => ($newDescription !== null) ? : $expense->getDescription()
		];
	}

	public static function updateExpense(string $id) {
		$pdo = DatabaseHelper::getPDOInstance();
		$input = self::updateExpenseInput($id);
		if (!$input)  return ;
		extract($input);
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");

		$query = " UPDATE Expenses SET category_name = :categoryName, expense_name = :expenseName, amount = :amount,  description = :description, updated_at = :updatedAt WHERE  id = :id";

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':expenseName', $expenseName);
			$stmt->bindparam(':amount', $amount);
			$stmt->bindparam(':description', $description);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Updated successfully");

			if($stmt->rowCount() > 0){
				$expense = self::findOneByID($id);
				return $expense;
			}

		}catch(PDOException $e){
			CLIHelper:: error(" Unknown Error" . $e->getMessage());
			return null;
		}

	}

	public static function deleteExpenseByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$expense = self::findOneByID($id);
		if(!$expense) {
			CLIHelper::error(" Expense with ID '$id' not found.");
			return null;
		}

		$query = "DELETE FROM  Expenses  WHERE id = :id";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->execute();
			
			if($stmt->rowCount()  > 0){
				CLIHelper::success(" Delete successful");
				return $expense;
			}
			return null;
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function DeleteAllExpenses(){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = AppManager::confirm("Do you want to delete? (y/n): ");
		$confirm1= AppManager::confirm("Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM Expenses "; 
				$stmt = $pdo->prepare($query);
				$stmt->execute();

				if($stmt->rowCount() > 0){
					CLIHelper::success(" Deleted sucessfully");
				}
			}else{
				CLIHelper:: error(" Deletion Cancelled");
			}

		}catch(PDOException $e){
			
			CLIHelper:: error(" Unknown Error(" . $e->getMessage());
		}

	}

	public static function selectPeriod() {
		echo "\nSelect Period:\n";
		echo "1. Today\n";
		echo "2. This Month\n";
		echo "3. This Year\n";
		echo "4. All Time\n";
		echo "Enter choice (1-4): ";
		
		$choice = trim(fgets(STDIN));
		
		return match($choice) {
			'1' => 'day',
			'2' => 'month',
			'3' => 'year',
			'4' => 'all',
			default => 'all'
		};
}

	public static function getExpenditureReport(string $userId, string $period) {
		$pdo = DatabaseHelper::getPDOInstance();

		switch ($period) {
			case 'day':
				$select = "created_at::date as label";
				$groupBy = "created_at::date";
				break;
			case 'month':
				$select = "TO_CHAR(created_at, 'YYYY-MM') as label";
				$groupBy = "TO_CHAR(created_at, 'YYYY-MM')";
				break;
			case 'year':
				$select = "EXTRACT(YEAR FROM created_at) as label";
				$groupBy = "EXTRACT(YEAR FROM created_at)";
				break;
			case 'all':
			$select = "TO_CHAR(created_at, 'YYYY-MM') as label";
			$groupBy = "TO_CHAR(created_at, 'YYYY-MM')";
			break;
			default:
				throw new Exception("Invalid period selected.");
		}

		$query = "SELECT $select, SUM(amount) as total FROM Expenses WHERE user_id = :userId GROUP BY $groupBy ORDER BY label DESC";

		$stmt = $pdo->prepare($query);
		$stmt->bindParam(':userId', $userId);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getExpenseStats(string $userId, string $period){
		$pdo = DatabaseHelper::getPDOInstance();
		$query = " SELECT 
						AVG(amount) as average_expense, 
						MAX(amount) as highest_expense, 
						MIN(amount) as lowest_expense, 
						SUM(amount) as total_expense,
						(SELECT category_name FROM Expenses WHERE user_id = :userId AND amount = (SELECT MIN(amount) FROM Expenses WHERE user_id = :userId)LIMIT 1) as lowest_category,
						(SELECT category_name FROM Expenses WHERE user_id = :userId AND amount = (SELECT MAX(amount) FROM Expenses WHERE user_id = :userId)LIMIT 1) as highest_category
						FROM  Expenses WHERE user_id = :userId ";

		switch (strtolower($period)) {
			case 'date':
				$query .= " AND Date (created_at) = 'CURRENT_DATE' ";
				break;
			case 'month':
				$query .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE) AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)";
				break;
			case 'year':
				$query .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
				break;
			case 'all':
			default:
				break;
		}
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();

			$expenses = $stmt->fetch(PDO::FETCH_ASSOC);
			return $expenses;
		}catch(PDOException $e){
			CLIHelper::error("Stats Error: " . $e->getMessage());
			return null;
		}
		
	}

	public static function filterExpenses(string $userId, string $period){
		$pdo = DatabaseHelper::getPDOInstance();

		$query = "SELECT * FROM Expenses WHERE user_id = :userId"; 
		switch (strtolower($period)) {
			case 'day':
				$query  .= " AND Date(created_at) = CURRENT_DATE";
				break;
			case 'month':
				$query  .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE) AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)";
				break;
			case 'year':
				$query .= " AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)";
				break;
			case 'all':
				break;
			default:
				CLIHelper::error(" Invalid Input (Day,Month,Year)");
				break;
		}

		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':userId', $userId);
		$stmt->execute();
		$expenses = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$expenses[] = self::mapToExpenseRow($row);
		}
		return $expenses;
	}

	public static function searchExpensesByCategoryAndDate(string $userId, ?string $categoryName = null, ?string $startDate = null, ?string $endDate = null) {
	$pdo = DatabaseHelper::getPDOInstance();
	$query = "SELECT * FROM Expenses WHERE user_id = :userId";
	$params = [':userId' => $userId];
	
	
	if ($categoryName !== null && $categoryName !== '') {
		$query .= " AND LOWER(category_name) LIKE LOWER(:categoryName)";
		$params[':categoryName'] = "%$categoryName%";
	}
	
	if ($startDate !== null && $startDate !== '') {
		$query .= " AND date >= :startDate";
		$params[':startDate'] = $startDate;
	}
	
	if ($endDate !== null && $endDate !== '') {
		$query .= " AND date <= :endDate";
		$params[':endDate'] = $endDate;
	}
	
	$query .= " ORDER BY date DESC, created_at DESC";
	
	try {
		$stmt = $pdo->prepare($query);
		$stmt->execute($params);
		
		$expenses = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$expenses[] = self::mapToExpenseRow($row);
		}
		return $expenses;
	} catch (PDOException $e) {
		CLIHelper::error("Search Error: " . $e->getMessage());
		return [];
	}
}

	// Refactor to  expense report by datr,cat name and amoount
	// public static function expenseReportByPeriod(string $userId, ?string $period = 'month'){
	// 	$pdo = DatabaseHelper::getPDOInstance();
	// 	$format = ($period === 'day') ? 'YYYY-MM-DD' : (($period === 'year') ? 'YYYY' : 'YYYY-MM');
	// 	$query  = "  SELECT TO_CHAR(date,  '$format') as period, SUM(amount) as total FROM Expenses WHERE user_id = :userId AND category_name = :categoryName GROUP BY period ORDER BY  period ";
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->bindparam('userId', $userId);
	// 	$stmt->bindparam('categoryName', $categoryName);
	// 	$stmt->execute();

	// 	$report = fetchAll(PDO::FETCH_ASSOC);
	// 	return $report;
	// }

}

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

	public static function budgetInput(string $userId){
			$user = User::findOneByID($userId);
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

	public static function addBudget( string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$id = uniqid();
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");

		$input = self::budgetInput($userId);
		if (!$input) return null;
		extract($input);

		$query = " INSERT INTO Budget (id, user_id, category_name, amount, start_date, end_date, created_at, updated_at) VALUES (:id, :userId, :categoryName, :amount, :startDate, :endDate, :createdAt, :updatedAt)";

        if (!Category::findOneByCategoryName($categoryName)) {
            throw new Exception("Category not found");
        }

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':userId', $userId);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':amount', $amount);
			$stmt->bindparam(':startDate', $startDate);
			$stmt->bindparam(':endDate', $endDate);
			$stmt->bindparam(':createdAt', $timeStamp);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Added successful");
			return self::findOneByID($id);

		}catch(PDOException $e){
			CLIHelper::error(" Unknown Error(" . $e->getMessage());
		}

	}

	public static function ViewAllBudgets(string $userId, bool $sortByDescending = true){
		$pdo = DatabaseHelper::getPDOInstance();
		$sort = $sortByDescending ? "desc" : "asc";
		$query = " SELECT * FROM  Budget WHERE user_id = :userId ORDER BY created_at $sort";
		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->execute();

			$budgets = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$budgets[] = self::mapToBudgetRow($row);
			}
			return $budgets;
	
		} catch (PDOException $e) {
			CLIHelper:: error(" Uknown Error" . $e->getMessage());
		}
	}

	public static function updateBudgetInput($id){
		$budget = self::findOneByID($id);
		if(!$budget){
			CLIHelper::error(" Budget not found");
			return null;
		}

		$newCategoryName =CLIHelper::validateInput(" Category Name [" . $budget->getCategoryName() . "]", 2 , true);
		$newAmount = CLIHelper::getAmount(" Amount [" .  $budget->getAmount() . "]" ,  true);
		$newStartDate = CLIHelper::getDateInput(" Start Date [" . $budget->getStartDate()->format('Y-m-d') . "]");
		$newEndDate = CLIHelper::getDateInput(" End Date [" . $budget->getEndDate()->format('Y-m-d') . "]" );


		return [
			'categoryName' => ($newCategoryName !== "") ? $newCategoryName : $budget->getCategoryName(),
			'amount' => ($newAmount !== "") ? $newAmount : $budget->getAmount(),
			'startDate' => ($newStartDate !== "") ? $newStartDate : $budget->getStartDate()->format('Y-m-d'),
			'endDate' => ($newEndDate !== "") ? $newEndDate : $budget->getEndDate()->format('Y-m-d')

		];

	}

	public static function updateBudgetDetails(string $id) {
		$pdo = DatabaseHelper::getPDOInstance();
		$input = self::updateBudgetInput($id);
		if(!$input) return null;
		extract($input);	
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");

		$query = "UPDATE Budget SET category_name = :categoryName, amount = :amount, start_date = :startDate, end_date = :endDate, updated_at = :updatedAt WHERE id = :id";

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->bindparam(':categoryName', $categoryName);
			$stmt->bindparam(':amount', $amount);
			$stmt->bindparam(':startDate', $startDate);
			$stmt->bindparam(':endDate', $endDate);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

				if($stmt->rowCount() > 0){
					CLIHelper::success(" Update successful");
					return self::findOneByID($id);
				}else{
					CLIHelper::error(" No row Updated");  
					return null;
				}
		}catch(PDOException $e){
			CLIHelper::error(" Unknown Error" . $e->getMessage());
		}

	}

	public static function deleteBudgetByID(string $id){
		$pdo = DatabaseHelper::getPDOInstance();
		$budget = self::findOneByID($id);
		if(!$budget) {
			CLIHelper::error(" Budget with ID '$id' not found.");
			return null;
		}

		$query = "DELETE FROM  Budget WHERE id = :id";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':id', $id);
			$stmt->execute();
			
			if($stmt->rowCount()  > 0){
				CLIHelper::success(" Budget '{$budget->getCategoryName()}' deleted successfully.");
				return $budget;
			}
			return null;
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function DeleteAllBudgets(){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = AppManager::confirm("Do you want to delete? (y/n): ");
		$confirm1= AppManager::confirm("Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM Budget "; 
				$stmt = $pdo->prepare($query);
				$stmt->execute();

				if($stmt->rowCount() > 0){
					CLIHelper::success(" Deleted sucessfully");
				}
			}else{
				CLIHelper::error(" Deletion Cancelled");
			}

		}catch(PDOException $e){
			
			CLIHelper::error(" Unknown Error(" . $e->getMessage());
		}
		
	}

	public static function budgetCheck(string $userId, string $categoryName): bool{
		$pdo = DatabaseHelper::getPDOInstance();

		$query = "SELECT amount FROM Budget WHERE user_id  = :userId AND  category_name = :categoryName LIMIT 1";

		$stmt = $pdo->prepare($query);
		$stmt->bindparam(':userId', $userId);
		$stmt->bindparam(':categoryName', $categoryName);
		$stmt->execute();

		$budget = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$budget) return false;

		$queryE = " SELECT SUM(amount) as total_spent  FROM Expenses WHERE user_id = :userId AND category_name = :categoryName ";
		$stmtE = $pdo->prepare($queryE);
		$stmtE->bindparam(':userId', $userId);
		$stmtE->bindparam(':categoryName', $categoryName);
		$stmtE->execute();
		$expense = $stmtE->fetch(PDO::FETCH_ASSOC);
		if(!$expense) return false;

		$totalSpent = (float)($expense['total_spent'] ?? 0 );
		$budgetAmount = (float)$budget['amount'];
		if($totalSpent > $budgetAmount){
			CLIHelper::error(" You have exceeded your budget for $categoryName" . (" Amout spent: $totalSpent, , Budget: $budgetAmount"));
		}
		return false;

	}
}


// $expense =Expense::deleteExpenseByID('6954c2989b57f');
// var_dump($expense);

//echo "=== FILTER EXPENSES ===\n";
$period = Expense::selectPeriod();
$expenses = Expense::filterExpenses('694427f5b6a6d', $period);

if (empty($expenses)) {
    echo "No expenses found for this period.\n";
} else {
    $periodTitle = match($period) {
        'day' => 'Today',
        'month' => 'This Month',
        'year' => 'This Year',
        'all' => 'All Time',
        default => ucfirst($period)
    };
    
    echo "\n" . str_repeat("=", 90) . "\n";
    echo str_pad("Filtered Expenses - $periodTitle", 90, " ", STR_PAD_BOTH) . "\n";
    echo str_repeat("=", 90) . "\n";
    echo sprintf("%-12s %-18s %-20s %-15s %-20s\n", 
        "Date", "Category", "Expense", "Amount", "Description");
    echo str_repeat("-", 90) . "\n";
    
    $totalAmount = 0;
    
    foreach ($expenses as $expense) {
        $totalAmount += $expense->getAmount();
        
        echo sprintf(
            "%-12s %-18s %-20s $%-14s %-20s\n",
            $expense->getDate()->format('Y-m-d'),
            substr($expense->getCategoryName(), 0, 17),
            substr($expense->getExpenseName(), 0, 19),
            number_format($expense->getAmount(), 2),
            substr($expense->getDescription(), 0, 19)
        );
    }
    
    echo str_repeat("=", 90) . "\n";
    echo sprintf("%-52s $%-14s\n", 
        "TOTAL (" . count($expenses) . " expenses)", 
        number_format($totalAmount, 2)
    );
    echo sprintf("%-52s $%-14s\n", 
        "AVERAGE", 
        number_format($totalAmount / count($expenses), 2)
    );
    echo str_repeat("=", 90) . "\n\n";
}


// $period = Expense::selectPeriod();
// $report = Expense::getExpenditureReport('694427f5b6a6d', $period);

// if (empty($report)) {
//     echo "No expenses found for this period.\n";
// } else {
//     $periodLabel = match($period) {
//         'day' => 'Daily Breakdown',
//         'month' => 'Monthly Breakdown',
//         'year' => 'Yearly Breakdown',
//         default => 'Expense Report'
//     };
    
//     $columnHeader = match($period) {
//         'day' => 'Date',
//         'month' => 'Month',
//         'year' => 'Year',
//         default => 'Period'
//     };
    
//     echo "\n" . str_repeat("=", 70) . "\n";
//     echo str_pad("Expenditure Report - $periodLabel", 70, " ", STR_PAD_BOTH) . "\n";
//     echo str_repeat("=", 70) . "\n";
//     echo sprintf("%-30s %20s %15s\n", $columnHeader, "Amount", "% of Total");
//     echo str_repeat("-", 70) . "\n";
    
//     $grandTotal = array_sum(array_column($report, 'total'));
    
//     foreach ($report as $row) {
//         $label = $row['label'];
//         $total = $row['total'];
//         $percentage = ($grandTotal > 0) ? ($total / $grandTotal) * 100 : 0;
        
//         echo sprintf(
//             "%-30s %19s %14s%%\n",
//             $label,
//             '$' . number_format($total, 2),
//             number_format($percentage, 1)
//         );
//     }
    
//     echo str_repeat("-", 70) . "\n";
    
//     $summaryText = match($period) {
//         'day' => count($report) . " day(s)",
//         'month' => count($report) . " month(s)",
//         'year' => count($report) . " year(s)",
//         default => count($report) . " period(s)"
//     };
    
//     echo sprintf("%-30s %19s %15s\n", 
//         "TOTAL ($summaryText)", 
//         '$' . number_format($grandTotal, 2),
//         "100.0%"
//     );
    
//     $average = $grandTotal / count($report);
//     $avgText = match($period) {
//         'day' => 'Average per day',
//         'month' => 'Average per month',
//         'year' => 'Average per year',
//         default => 'Average per period'
//     };
    
//     echo sprintf("%-30s %19s\n", 
//         $avgText, 
//         '$' . number_format($average, 2)
//     );
    
//     echo str_repeat("=", 70) . "\n\n";
// }


// $period = Expense::selectPeriod();
// $stats = Expense::getExpenseStats('694427f5b6a6d', $period);

// if (!$stats || $stats['total_expense'] == 0) {
//     echo "No expense statistics available for this period.\n";
// } else {
//     $periodTitle = match($period) {
//         'day' => 'Today',
//         'month' => 'This Month',
//         'year' => 'This Year',
//         'all' => 'All Time',
//         default => ucfirst($period)
//     };
    
//     echo "\n" . str_repeat("=", 70) . "\n";
//     echo str_pad("Expense Statistics - $periodTitle", 70, " ", STR_PAD_BOTH) . "\n";
//     echo str_repeat("=", 70) . "\n\n";
    
//     // Total and Average
//     echo sprintf("%-40s %25s\n", "Total Expenses:", '$' . number_format($stats['total_expense'], 2));
//     echo sprintf("%-40s %25s\n", "Average Expense:", '$' . number_format($stats['average_expense'], 2));
//     echo str_repeat("-", 70) . "\n";
    
//     // Highest
//     echo sprintf("%-40s %25s\n", "Highest Expense:", '$' . number_format($stats['highest_expense'], 2));
//     echo sprintf("%-40s %25s\n", "  Category:", $stats['highest_category'] ?? 'N/A');
//     echo str_repeat("-", 70) . "\n";
    
//     // Lowest
//     echo sprintf("%-40s %25s\n", "Lowest Expense:", '$' . number_format($stats['lowest_expense'], 2));
//     echo sprintf("%-40s %25s\n", "  Category:", $stats['lowest_category'] ?? 'N/A');
    
//     echo str_repeat("=", 70) . "\n\n";
// }

// $expenses =  Expense::getExpenseStats('6950fd9de6ca7');


//Expense::addExpense('69504f6401bdf');
 
// Expense::addExpense('6950fd9de6ca7');
// $budget =Budget::deleteBudgetByID('6955037f2a4b4');
// var_dump($budget);

 // $budget =Budget::budgetCheck('6945624a79073', 'Drinks');
 // var_dump($budget);

  //User::resetPassword();
//User::userRegistration();
//User::userLogin();
// $user = Expense::ViewAllExpenses("694847bcae5171.52643173");
// $user = Budget::AddBudget('6945624a79073', 'Drinks', 10000.0, );
// 	var_dump($user);

//$expense = Expense::AddExpense('6945624a79073','Food ', 'Dee-Traid', 3000.0, 'He purchased a bag of rice');
		//var_dump($expense);
//Category::AddCategory();
	

// $user = User::findOneByID('6945624a79073');

// var_dump($user);
// $users = User::findOneByID('694427f5b6a6d');
// $user = User::updateUserDetails($users->getID(), 'Kelvin', null, '09133332256', 0 );
// var_dump($user);

// 	$user = User::updateUserDetails('6951f8aec306f');
// var_dump($user);



//$debit = Expense::findOneByID('694a84d47a5379');

// if ($debit) {
//     $expense = Expense::updateExpenseDetails($debit->getID(), 'Transportation', null, 200000.0, 'Christmas Spending');
//     var_dump($expense);
// } else {
//     CLIHelper:: "Expense not found.";
// }
// $cart = Category::findOneByID('694ac50e13960');
// $category = Category::updateCategoryDetails($cart->getID(), ' Transportation',  null);
// var_dump($category);

// $user = User::password_recovery('098877864688');
// var_dump($user);
//Category::updateCategoryDetails('695215de6980a');
//Category::addCategory("695061294e465");

// Expense::addExpense('694427f5b6a6d');

//Expense::updateExpense('69529a3b984b0');

//Budget::AddBudget('6950fd9de6ca7');
 //$budget = Budget::updateBudgetInput('695501d4c1df0');
//var_dump($budget);
	//CLIHelper::getInput(" Enter name");
	//CLIHelper::validateEmail(' Enter email');
	//CLIHelper::getDateInput(' Enter date');

	//CLIHelper::getAmount(" Enter amount");
	//CLIHelper::validateInput("Enter email");

	//$user = User::resetPassword();

	//var_dump($user);
	// $stats = Expense::getExpenseStats('6945624a79073');
	// CLIHelper:: " Average spendings: " . $stat(s['average_expense']);

 // User::updateUserDetails();

//  $users = User::ViewAllUsers('6951f8aec306f');
// $index = 1;
// // $users = [];
//  foreach ($users as $user) {
//  	echo " User Name: " . $user->getUserName() . PHP_EOL;
//  	echo " Email: " .  $user->getEmail() . PHP_EOL;
//  	$index++;
//  }

//  $categories= Category::ViewAllCategories("6945624a79073");
// $index = 1;
// if(empty($categories))return null;
//  foreach ($categories as $cart) {
//  	echo " Category Name: " . $cart->getCategoryName() . PHP_EOL;
//  	echo " Description: " .  $cart->getDescription() . PHP_EOL;
//  	$index++;
//  }

//  $expenses= Expense::ViewAllExpenses("6945624a79073");
// $index = 1;
// if(empty($expenses))return null;
//  foreach ($expenses as $expense) {
//  	echo " Expense Name: " . $expense->getExpenseName() . PHP_EOL;
//  	echo " Amount: " .  $expense->getAmount() . PHP_EOL;
//  	$index++;
//  }

//  $budgets= Budget::ViewAllBudgets("6950fd9de6ca7");
// $index = 1;
// if(empty($budgets))return null;
//  foreach ($budgets as $budget) {
//  	echo " Category Name: " . $budget->getCategoryName() . PHP_EOL;
//  	echo " Amount: " .  $budget->getAmount() . PHP_EOL;
//  	$index++;
//  }

//Category::addCategory('6950fd9de6ca7');
/* Users ID - ('6945624a79073', "6945624a79073"
"69504f6401bdf"
"695061294e465"
"6950fd9de6ca7") */

?>