<?php

class EmailConfig {
    // SMTP Configuration
    public static function getHost() { return $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com'; }
    public static function getPort() { return $_ENV['SMTP_PORT'] ?? 465; }
    public static function getUsername() { return $_ENV['SMTP_USER'] ?? ''; }
    public static function getPassword() { return $_ENV['SMTP_PASS'] ?? ''; }
    public static function getEncryption() { return $_ENV['SMTP_ENCRYPTION'] ?? 'ssl'; }
    
    // Sender Information
    public static function getFromEmail() { return $_ENV['FROM_EMAIL'] ?? ''; }
    public static function getFromName() { return $_ENV['FROM_NAME'] ?? 'Expense Tracker'; }
    
    const CHARSET = 'UTF-8';
}