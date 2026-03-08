Master RBAC PHP Native

Sistem Role Based Access Control (RBAC) berbasis PHP Native + MySQL + PDO yang dilengkapi dengan CRUD Generator otomatis dan Dynamic Menu Permission.

Project ini dibuat sebagai starter template admin panel PHP Native yang memiliki sistem manajemen role, permission, dan menu yang fleksibel.

Features

Login Authentication

Role Based Access Control (RBAC)

Dynamic Sidebar Menu berdasarkan permission

Permission per menu (View, Create, Update, Delete)

CRUD Generator otomatis dari tabel database

Relasi tabel otomatis untuk form select

Pagination

Search otomatis

Clean MVC Structure (Controller, Model, View)

Folder Structure
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
Database Structure (RBAC)

Tabel utama RBAC:

roles
permissions
menus
menu_permissions
role_permissions
users

Relasi RBAC:

roles
   ↓
role_permissions
   ↓
permissions
   ↓
menu_permissions
   ↓
menus

Artinya:

Role → Permission → Menu
Konsep Permission

Setiap menu memiliki 4 permission dasar:

{menu}.view
{menu}.create
{menu}.update
{menu}.delete

Contoh:

Menu Users

users.view
users.create
users.update
users.delete

Permission ini akan dihubungkan ke:

menu_permissions

Kemudian diberikan ke role melalui:

role_permissions
Membuat Menu + CRUD Generator

Menu baru dapat dibuat sekaligus menghasilkan CRUD otomatis.

Masuk ke menu:

Menu Management → Tambah Menu

Contoh tampilan form:

Form Pembuatan Menu

Isi field berikut:

Field	Keterangan
Nama Menu	Nama menu yang tampil di sidebar
Route	URL route menu
Icon	Icon sidebar
Parent Menu	Jika menu merupakan submenu
Posisi Setelah Menu	Mengatur urutan menu

Contoh:

Nama Menu : Product
Route     : products
Icon      : fas fa-box
Parent    : Master
Mengaktifkan CRUD Generator

Aktifkan toggle:

Aktifkan CRUD Generator

Kemudian isi konfigurasi berikut.

Field	Keterangan
Nama Controller	Nama controller yang akan dibuat
Nama Model	Nama model
Nama Folder View	Folder view
Pilih Tabel Database	Tabel yang akan dijadikan CRUD

Contoh:

Controller : Product
Model      : Product
View       : products
Table      : products
File yang Dihasilkan Generator

Setelah klik Simpan, sistem akan otomatis membuat:

Controller
/app/controllers/ProductController.php
Model
/app/models/ProductModel.php
Views
/app/views/products/

   index.php
   create.php
   edit.php
   form.php
Data yang Dibuat di Database

Selain file CRUD, sistem juga otomatis membuat data RBAC.

1 Menu

Masuk ke tabel:

menus
2 Permissions

Sistem otomatis membuat:

products.view
products.create
products.update
products.delete

Disimpan pada tabel:

permissions
3 Menu Permission Mapping

Relasi antara menu dan permission:

menu_permissions

Contoh:

menu_id | permission_id
4 Superadmin Permission

Role Superadmin otomatis mendapatkan semua permission dari menu baru:

view
create
update
delete

Sehingga superadmin langsung dapat mengakses menu tersebut.

Mengatur Permission Role

Masuk ke menu:

Roles

Pilih role yang ingin diatur.

Contoh role:

Superadmin
Admin
Staff

Kemudian centang permission yang diizinkan.

Contoh:

[✓] products.view
[✓] products.create
[✓] products.update
[ ] products.delete

Data akan disimpan pada tabel:

role_permissions
Menggunakan Permission di Controller

Gunakan helper berikut untuk membatasi akses.

require_permission('products.view');

Contoh:

public function index()
{
    require_permission('products.view');

    $products = $this->model->getAll();

    view('products/index', compact('products'));
}
Contoh Permission CRUD
View
require_permission('products.view');
Create
require_permission('products.create');
Update
require_permission('products.update');
Delete
require_permission('products.delete');

Jika user tidak memiliki permission maka otomatis:

403 Forbidden
Dynamic Sidebar Menu

Sidebar menu akan muncul berdasarkan permission user.

Contoh:

Jika user hanya memiliki:

products.view

Menu Products tetap muncul tetapi user hanya dapat melihat data.

Jika user tidak memiliki permission, maka menu tidak akan tampil.

Pagination

Pagination menggunakan parameter:

?page=1

Contoh:

/products?page=2
Search

Search otomatis tersedia untuk tipe kolom:

varchar
text
char
Requirements

Server:

PHP >= 8.0
MySQL >= 5.7
Apache / Nginx

Extension:

PDO
PDO MYSQL
Security

Sistem sudah menggunakan:

PDO Prepared Statement

RBAC Authorization

Session Authentication

Future Development

Fitur yang dapat dikembangkan:

API RBAC

Permission per button

Activity Log

Multi Role User

Soft Delete

Import Export

Author

Developed by

Wahid Cyber
