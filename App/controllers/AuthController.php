<?php
namespace App\Controllers;

use App\Models\User;
use App\Views\Inputs\AuthInput;
use App\Views\CLIHelper;
use App\Core\UtilityFunction;
use App\Core\DatabaseHelper;
use App\Views\UIDisplay;
use PDO;
use PDOException;
use DateTimeImmutable;

class AuthController{
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
				return AuthController::userLogin();
			}

			$id = uniqid();
			$timeStamp = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
			$input = AuthInput::getUserInput();
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

			return User::findOneByID($id);

		}catch(PDOException $e){
			CLIHelper::error(" Registration Failed" . $e->getMessage());
			return null;
		}

	}

	public static function userLogin(){
		$pdo = DatabaseHelper::getPDOInstance();
		$input= AuthInput::loginInput();
		if(!$input) return null;
		extract($input);
		$query = "SELECT * FROM Users WHERE email = :email OR phone_number = :phoneNo";
		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':email', $identifier);
			$stmt->bindparam(':phoneNo', $identifier);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			$confirm = UtilityFunction::confirm($prompt = "Login? (y/n): ");
			if($confirm){
				if($user && password_verify($password, $user['password'])){
					User::$loggedInUserID = $user['id'];
					CLIHelper::success(" Login successful");
						return User::mapToUserRow($user);
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
				return User::resetPassword();
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

    public static function viewUserProfile(string $userId, bool $sortByDescending = true){
		$pdo = DatabaseHelper::getPDOInstance();
		$sort = $sortByDescending ? "desc" : "asc";
		$query = " SELECT * FROM  Users WHERE id =:userId LIMIT 1";

		try {
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':userId', $userId);
			$stmt->execute();

			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$user){
				CLIHelper::error(" User not found ");
				return null;
			}

			UIDisplay::userDisplayTable($user);
			return User::mapToUserRow($user);
		} catch (PDOException $e) {
			 CLIHelper::error(" Unknown Error" . $e->getMessage());
		}
	}

	public static function updateUserDetails(string $userId) {
		$pdo = DatabaseHelper::getPDOInstance();
		$input = AuthInput::updateUserInput($userId);
		if(!$input) return null;
		extract($input);
		$timeStamp = (new DateTimeImmutable('now'))->format("Y-m-d H:i:s");
		$query = " UPDATE Users SET user_name = :userName , email = :email,  phone_number = :phoneNo, income = :income,  updated_at = :updatedAt WHERE  id = :userId";

		try{
			$stmt = $pdo->prepare($query);
			$stmt->bindparam(':userId', $userId);
			$stmt->bindparam(':userName', $userName);
			$stmt->bindparam(':email', $email);
			$stmt->bindparam(':phoneNo', $phoneNo);
			$stmt->bindparam(':income', $income);
			$stmt->bindparam(':updatedAt', $timeStamp);
			$stmt->execute();

			CLIHelper::success(" Updated sucessfully");

			if($stmt->rowCount() > 0){
				$user = User::findOneByID($userId);
				return $user;
			}

			return null;

		}catch(PDOException $e){
			 CLIHelper::error(" Unknown Error" . $e->getMessage());
		}

	}

	public static function deleteUserByID(string $userId){
		$pdo = DatabaseHelper::getPDOInstance();
		$user = User::findOneByID($userId);
		if(!$user) {
			CLIHelper::error(" User not found");
			return null;
		}

		$query = "DELETE FROM  Users WHERE id = :userId";
		$confirm = UtilityFunction::confirm("Do you want to delete? (y/n): ");
		try{
			if($confirm){
				$stmt = $pdo->prepare($query);
				$stmt->bindparam(':userId', $userId);
				$stmt->execute();
				
				if($stmt->rowCount()  > 0){
					CLIHelper::success("User '{$user->getUserName()}' deleted successfully.");
					return $user;
				}
				return null;
			}
			
		}catch(PDOException $e){
			CLIHelper::error("Delete failed: " . $e->getMessage());
       		 return null;
		}
	}

	public static function DeleteAllUser(){
		$pdo = DatabaseHelper::getPDOInstance();
		$confirm = UtilityFunction::confirm("Do you want to delete? (y/n): ");
		$confirm1= UtilityFunction::confirm("Confirm Deletion? (y/n): ");
		try{
			if($confirm && $confirm1 == true){
				$query = " DELETE FROM users "; 
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
?>
