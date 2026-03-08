# PHP Native RBAC Admin Panel

Simple **Role Based Access Control (RBAC)** admin panel built with **PHP Native + MySQL + PDO**.

This project provides a lightweight **Admin Panel Starter Template** with dynamic menu management, permission control, and automatic CRUD generation.

---

# ✨ Features

- Authentication System
- Role Based Access Control (RBAC)
- Dynamic Sidebar Menu based on Permission
- Menu Management
- Permission Management
- Role Permission Mapping
- Menu Permission Mapping
- Automatic CRUD Generator
- Pagination
- Search
- Clean MVC Structure

---

# 🧱 Architecture

The system follows a simple **MVC pattern**.


/app
/controllers
/models
/views

/core
Controller.php
Model.php
Router.php

/helpers
auth.php
permission.php

/public
index.php
assets/

/config
database.php


---

# 🗄️ Database Structure (RBAC)

Main tables used by the RBAC system:


roles
permissions
menus
menu_permissions
role_permissions
users


RBAC Relationship Flow:


roles
↓
role_permissions
↓
permissions
↓
menu_permissions
↓
menus


Meaning:


Role → Permission → Menu


---

# 🔐 Permission Concept

Each menu automatically has **4 basic permissions**:


{menu}.view
{menu}.create
{menu}.update
{menu}.delete


Example for `users` menu:


users.view
users.create
users.update
users.delete


These permissions are stored in:


permissions


And assigned to roles via:


role_permissions


---

# 📋 Creating Menu with CRUD Generator

This system allows you to **create a menu and generate CRUD automatically** from a selected database table.

Navigate to:


Menu Management → Add Menu


Example interface:

![Menu Generator](docs/menu-generator.png)

---

# 📝 Menu Form Fields

| Field | Description |
|------|-------------|
| Menu Name | Menu name displayed in sidebar |
| Route | URL route |
| Icon | Sidebar icon |
| Parent Menu | If the menu is a submenu |
| Position After Menu | Menu order |

Example:


Menu Name : Product
Route : products
Icon : fas fa-box
Parent : Master


---

# ⚙️ Enable CRUD Generator

Activate the toggle:


Enable CRUD Generator


Then configure the generator.

| Field | Description |
|------|-------------|
| Controller Name | Controller class |
| Model Name | Model class |
| View Folder | View folder name |
| Database Table | Table used for CRUD |

Example configuration:


Controller : Product
Model : Product
View : products
Table : products


---

# 📂 Generated Files

After clicking **Save**, the system automatically generates:

### Controller


/app/controllers/ProductController.php


### Model


/app/models/ProductModel.php


### Views


/app/views/products

index.php
create.php
edit.php
form.php


---

# 🗃️ Database Records Created

When a menu is created, the system automatically inserts:

## 1️⃣ Menu

Stored in:


menus


---

## 2️⃣ Permissions

Automatically generated permissions:


products.view
products.create
products.update
products.delete


Stored in:


permissions


---

## 3️⃣ Menu Permission Mapping

Permission-to-menu relation stored in:


menu_permissions


Example:


menu_id | permission_id


---

## 4️⃣ Superadmin Access

The **Superadmin role automatically receives full access**:


view
create
update
delete


This ensures the new menu is always accessible to superadmin.

---

# 👥 Managing Role Permissions

Navigate to:


Roles


Select a role:


Superadmin
Admin
Staff


Then enable the permissions.

Example:


✔ products.view
✔ products.create
✔ products.update
✖ products.delete


These permissions are saved in:


role_permissions


---

# 🛡️ Using Permission in Controller

Use the helper function:

```php
require_permission('products.view');

Example:

public function index()
{
    require_permission('products.view');

    $products = $this->model->getAll();

    view('products/index', compact('products'));
}
CRUD Permission Examples
View
require_permission('products.view');
Create
require_permission('products.create');
Update
require_permission('products.update');
Delete
require_permission('products.delete');

If the user does not have permission, the system returns:

403 Forbidden
📑 Pagination

Pagination uses the following parameter:

?page=1

Example:

/products?page=2
🔎 Search

Search is automatically enabled for database columns with type:

varchar
text
char
⚙️ Requirements

Server:

PHP >= 8.0
MySQL >= 5.7
Apache / Nginx

PHP Extensions:

PDO
PDO MYSQL
🔒 Security

The system already implements:

PDO Prepared Statements

RBAC Authorization

Session Authentication

🚀 Future Development

Potential features for future development:

RBAC API

Button-level permissions

Activity Log

Multi-role users

Soft Delete

Import / Export

👨‍💻 Author

Developed by

Wahid Cyber