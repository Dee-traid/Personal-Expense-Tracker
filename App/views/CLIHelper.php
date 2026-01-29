<?php 
namespace App\Views;
use DateTimeImmutable;

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

			$d = DateTimeImmutable::createFromFormat('Y-m-d', $input);
			if($d && $d->format('Y-m-d') === $input){
				return $input;
			}
			self::error(" Invalid date format, Use YYYY-MM-DD");
		}
	}

}

?>