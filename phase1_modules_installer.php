<?php
require_once __DIR__ . "/config/database.php";

$tables = [

"client_profiles" => "
CREATE TABLE IF NOT EXISTS client_profiles (
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150),
 phone VARCHAR(50),
 whatsapp VARCHAR(50),
 email VARCHAR(150),
 location VARCHAR(150),
 national_id VARCHAR(80),
 package_name VARCHAR(100),
 status ENUM('active','suspended','expired','pending') DEFAULT 'pending',
 router_id INT NULL,
 ip_address VARCHAR(60),
 mac_address VARCHAR(80),
 expiry_date DATETIME NULL,
 notes TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"expiry_rules" => "
CREATE TABLE IF NOT EXISTS expiry_rules (
 id INT AUTO_INCREMENT PRIMARY KEY,
 client_id INT,
 action_type VARCHAR(80),
 grace_minutes INT DEFAULT 0,
 status VARCHAR(50) DEFAULT 'pending',
 executed_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"support_tickets" => "
CREATE TABLE IF NOT EXISTS support_tickets (
 id INT AUTO_INCREMENT PRIMARY KEY,
 client_id INT NULL,
 subject VARCHAR(200),
 description TEXT,
 priority ENUM('low','medium','high','critical') DEFAULT 'medium',
 status ENUM('open','assigned','resolved','closed') DEFAULT 'open',
 assigned_to VARCHAR(100),
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"network_alerts" => "
CREATE TABLE IF NOT EXISTS network_alerts (
 id INT AUTO_INCREMENT PRIMARY KEY,
 alert_type VARCHAR(100),
 title VARCHAR(200),
 message TEXT,
 severity ENUM('info','warning','critical') DEFAULT 'info',
 status ENUM('new','seen','resolved') DEFAULT 'new',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"expenses" => "
CREATE TABLE IF NOT EXISTS expenses (
 id INT AUTO_INCREMENT PRIMARY KEY,
 category VARCHAR(100),
 description TEXT,
 amount DECIMAL(10,2) DEFAULT 0,
 expense_date DATE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"installations" => "
CREATE TABLE IF NOT EXISTS installations (
 id INT AUTO_INCREMENT PRIMARY KEY,
 client_name VARCHAR(150),
 phone VARCHAR(50),
 location VARCHAR(150),
 package_name VARCHAR(100),
 installation_fee DECIMAL(10,2) DEFAULT 0,
 technician VARCHAR(100),
 status ENUM('pending','assigned','installed','cancelled') DEFAULT 'pending',
 notes TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"whatsapp_sms_logs" => "
CREATE TABLE IF NOT EXISTS whatsapp_sms_logs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 recipient VARCHAR(80),
 channel ENUM('sms','whatsapp') DEFAULT 'whatsapp',
 message TEXT,
 status VARCHAR(50) DEFAULT 'queued',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"admin_roles" => "
CREATE TABLE IF NOT EXISTS admin_roles (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100),
 role ENUM('super_admin','cashier','technician','noc_operator','sales_agent') DEFAULT 'noc_operator',
 username VARCHAR(100) UNIQUE,
 password VARCHAR(255),
 status ENUM('active','disabled') DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"router_backups" => "
CREATE TABLE IF NOT EXISTS router_backups (
 id INT AUTO_INCREMENT PRIMARY KEY,
 router_id INT NULL,
 backup_name VARCHAR(200),
 file_path VARCHAR(255),
 status VARCHAR(50) DEFAULT 'saved',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"coverage_zones" => "
CREATE TABLE IF NOT EXISTS coverage_zones (
 id INT AUTO_INCREMENT PRIMARY KEY,
 zone_name VARCHAR(150),
 location VARCHAR(150),
 ap_name VARCHAR(150),
 signal_notes TEXT,
 status ENUM('active','planned','maintenance') DEFAULT 'planned',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"client_portal_sessions" => "
CREATE TABLE IF NOT EXISTS client_portal_sessions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 client_id INT,
 login_token VARCHAR(255),
 last_login DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"traffic_analytics" => "
CREATE TABLE IF NOT EXISTS traffic_analytics (
 id INT AUTO_INCREMENT PRIMARY KEY,
 router_id INT NULL,
 client_ip VARCHAR(60),
 download_mbps DECIMAL(10,2) DEFAULT 0,
 upload_mbps DECIMAL(10,2) DEFAULT 0,
 recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"topology_nodes" => "
CREATE TABLE IF NOT EXISTS topology_nodes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 node_name VARCHAR(150),
 node_type ENUM('mikrotik','switch','ap','client','router') DEFAULT 'ap',
 parent_id INT NULL,
 ip_address VARCHAR(60),
 mac_address VARCHAR(80),
 status ENUM('online','offline','unknown') DEFAULT 'unknown',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)"
];

foreach($tables as $name => $sql){
    $pdo->exec($sql);
    echo "Created/checked table: $name\n";
}

echo "\nPHASE 1 MODULE DATABASE STRUCTURE INSTALLED SUCCESSFULLY ✅\n";
