<?php
namespace App\Views\Inputs;

use App\Models\User;
use App\Views\CLIHelper;
use Exception;


class AuthInput{
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

    
    public static function loginInput(){
		$identifier = CLIHelper::validateInput(" Enter Email or Phone Number");
		$password = CLIHelper::validatePassword(" Enter password");

		return [
        'identifier' => $identifier,
        'password'   => $password
    	];
	}

    public static function updateUserInput(string $id){
		$user = User::findOneByID($id);
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

}

?>