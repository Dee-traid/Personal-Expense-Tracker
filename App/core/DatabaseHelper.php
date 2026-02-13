<?php
	namespace App\Core;

	use PDO;
	use PDOException;
	use App\Views\CLIHelper;
	
class DatabaseHelper{
	private static $pdo = null;

	public static function getPDOInstance(){
		if (self::$pdo != null) {
			return self::$pdo;
		}
		$host     = $_ENV['DB_HOST'] ?? 'localhost';
        $port     = $_ENV['DB_PORT'] ?? '5432';
        $user     = $_ENV['DB_USER'] ?? 'postgres';
        $password = $_ENV['DB_PASS'] ?? '';
        $database = $_ENV['DB_NAME'] ?? 'expense_tracker';

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

?>