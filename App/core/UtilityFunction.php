<?php
namespace App\Core;

class UtilityFunction{
    public static function confirm($prompt = "Continue? (y/n): "){
	    while (true) {
	        $ans = strtolower(readLine($prompt));
	        if (in_array($ans, ['y', 'yes'], true)) return true;
	        if (in_array($ans, ['n', 'no'], true)) return false;
		        echo "Please answer y or n.\n";
	    }
	} 

    Public static function pauseForUser(){
        echo "\n";
        readline("Press Enter to continue...");
    }

	public static function pause() {
		echo "\nPress [Enter] to continue...";
		fgets(STDIN);
	}

	public static function clearScreen() {
		if (PHP_OS_FAMILY === 'Windows') {
			passthru('cls');
		} else {
			passthru('clear');
		}
	}

}
    
?>