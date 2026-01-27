<?php

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
	

?>
