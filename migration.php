<?php
require_once './config/db.php';

try {

  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

  /*
    |--------------------------------------------------------------------------
    | ROLES
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS roles (
      id int NOT NULL AUTO_INCREMENT,
      name varchar(100) NOT NULL,
      slug varchar(100) NOT NULL,
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY name (name),
      UNIQUE KEY slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

  /*
    |--------------------------------------------------------------------------
    | PERMISSIONS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS permissions (
      id int NOT NULL AUTO_INCREMENT,
      name varchar(150) NOT NULL,
      slug varchar(150) NOT NULL,
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

  /*
    |--------------------------------------------------------------------------
    | MENUS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS menus (
      id int NOT NULL AUTO_INCREMENT,
      name varchar(150) NOT NULL,
      route varchar(150) DEFAULT NULL,
      icon varchar(100) DEFAULT NULL,
      parent_id int DEFAULT NULL,
      order_number int DEFAULT '0',
      created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY parent_id (parent_id),
      CONSTRAINT menus_ibfk_1 FOREIGN KEY (parent_id) REFERENCES menus (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

  /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
      id smallint unsigned NOT NULL AUTO_INCREMENT,
      email varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
      name varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
      role_id int DEFAULT NULL,
      password varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      singkatan varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
      is_active tinyint(1) DEFAULT '1',
      refidunit bigint DEFAULT NULL,
      email_verified_at datetime DEFAULT NULL,
      api_token varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
      phone varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
      address varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
      date_of_birth date DEFAULT NULL,
      gender enum('male','female') COLLATE latin1_general_ci DEFAULT NULL,
      profile_photo varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
      remember_token varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
      deleted_at datetime DEFAULT NULL,
      created_at datetime DEFAULT CURRENT_TIMESTAMP,
      updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY idx_role_id (role_id),
      KEY idx_api_token (api_token),
      CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ");

  /*
    |--------------------------------------------------------------------------
    | ROLE PERMISSIONS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS role_permissions (
      role_id int NOT NULL,
      permission_id int NOT NULL,
      PRIMARY KEY (role_id,permission_id),
      KEY permission_id (permission_id),
      CONSTRAINT role_permissions_ibfk_1 FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
      CONSTRAINT role_permissions_ibfk_2 FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

  /*
    |--------------------------------------------------------------------------
    | MENU PERMISSIONS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS menu_permissions (
      menu_id int NOT NULL,
      permission_id int NOT NULL,
      PRIMARY KEY (menu_id,permission_id),
      KEY permission_id (permission_id),
      CONSTRAINT menu_permissions_ibfk_1 FOREIGN KEY (menu_id) REFERENCES menus (id) ON DELETE CASCADE,
      CONSTRAINT menu_permissions_ibfk_2 FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

  echo "Tables created<br>";

  /*
    |--------------------------------------------------------------------------
    | SEED ROLES
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    INSERT INTO roles (id,name,slug,created_at) VALUES
    (1,'SUPER ADMIN','super-admin','2026-02-16 05:06:56'),
    (2,'GUDANG','gudang','2026-02-16 05:06:56'),
    (3,'PENGADAAN','pengadaan','2026-02-16 05:06:56'),
    (4,'RUANG','ruang','2026-02-18 14:34:29'),
    (5,'MANAJER','manajer','2026-02-19 00:45:45')
    ON DUPLICATE KEY UPDATE name=name;
    ");

  /*
    |--------------------------------------------------------------------------
    | SEED PERMISSIONS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    INSERT INTO permissions (id,name,slug,created_at) VALUES
    (1,'View Dashboard','dashboard.view','2026-02-16 05:06:56'),
    (2,'View User','users.view','2026-03-05 17:42:54'),
    (3,'Create User','users.create','2026-03-05 17:42:54'),
    (4,'Edit User','users.edit','2026-03-05 17:42:54'),
    (5,'Delete User','users.delete','2026-03-05 17:42:54'),
    (6,'View Roles','roles.view','2026-02-16 05:06:57'),
    (7,'Create Roles','roles.create','2026-02-16 05:06:57'),
    (8,'Edit Roles','roles.edit','2026-02-16 05:06:57'),
    (9,'Delete Roles','roles.delete','2026-02-16 05:06:58'),
    (15,'Create Permissions','permissions.create','2026-02-16 05:06:58'),
    (16,'Delete Permissions','permissions.delete','2026-02-16 05:06:58'),
    (17,'VIew Menus','menus.view','2026-02-16 05:06:58'),
    (18,'Create Menus','menus.create','2026-02-16 05:06:58'),
    (19,'Edit Menus','menus.edit','2026-02-16 05:06:58'),
    (20,'Delete Menus','menus.delete','2026-02-16 05:06:58'),
    (213,'MASTER VIEW','master.view','2026-03-05 17:00:50'),
    (214,'SISTEM VIEW','sistem.view','2026-03-05 17:54:40')
    ON DUPLICATE KEY UPDATE name=name;
    ");

  /*
    |--------------------------------------------------------------------------
    | SEED MENUS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    INSERT INTO menus (id,name,route,icon,parent_id,order_number,created_at) VALUES
    (1,'Dashboard','dashboard','fas fa-home',NULL,1,'2026-02-16 05:06:59'),
    (2,'Users','users.index','fas fa-users',67,2,'2026-02-16 05:06:59'),
    (3,'Roles','roles.index','fas fa-address-card',67,3,'2026-02-16 05:06:59'),
    (4,'Permissions','permissions.index','fas fa-unlock',67,7,'2026-02-16 05:06:59'),
    (5,'Menus','menus.index','fas fa-grip',67,8,'2026-02-16 05:06:59'),
    (66,'Master','master','fas fa-screwdriver-wrench',NULL,8,'2026-03-05 17:00:50'),
    (67,'Sistem','sistem','fas fa-gears',NULL,2,'2026-03-05 17:54:40')
    ON DUPLICATE KEY UPDATE name=name;
    ");

  /*
    |--------------------------------------------------------------------------
    | ROLE PERMISSIONS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    INSERT INTO `role_permissions` VALUES
    (1,1),
    (2,1),
    (1,2),
    (1,3),
    (1,4),
    (1,5),
    (1,6),
    (1,7),
    (1,8),
    (1,9),
    (1,15),
    (1,16),
    (1,17),
    (1,18),
    (1,19),
    (1,20),
    (1,213),
    (1,214);
    ");

  /*
    |--------------------------------------------------------------------------
    | SEED MENUS
    |--------------------------------------------------------------------------
    */

  $pdo->exec("
    INSERT INTO `menu_permissions` VALUES
    (1,1),
    (2,2),
    (2,3),
    (2,4),
    (2,5),
    (3,6),
    (3,7),
    (3,8),
    (3,9),
    (4,15),
    (4,16),
    (5,17),
    (5,18),
    (5,19),
    (5,20),
    (66,213),
    (67,214);
    ");


  /*
|--------------------------------------------------------------------------
| SEED USER
|--------------------------------------------------------------------------
*/

  $password = password_hash('password', PASSWORD_BCRYPT);

  $stmt = $pdo->prepare("
    INSERT INTO users (role_id,name,email,password,email_verified_at)
    VALUES (1,'Super Admin','superadmin@test.com',?, NOW())
    ON DUPLICATE KEY UPDATE name=name
  ");

  $stmt->execute([$password]);

  $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

  echo "Seeder executed successfully.";
  $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
} catch (PDOException $e) {

  echo $e->getMessage();
}
