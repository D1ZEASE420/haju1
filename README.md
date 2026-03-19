# My Web Application

## Overview

This project is a **web application** built with **Laravel (PHP)** and **Vue 3 with Inertia.js**.  
It includes the following features:

- Interactive **map with markers**
- **Weather search** functionality
- **Blog posts** with CRUD operations
- **Comments system**
- Authentication and user management via **Laravel Fortify**

---

## Prerequisites

Before running the project, make sure the following are installed:

1. **PHP >= 8.1**
2. **Composer**
3. **Node.js & npm**
4. **SQLite** (or MySQL/PostgreSQL if you change database)
5. A code editor (VSCode recommended)

---

## Clone the Repository

git clone 
cd haju1

Install Dependencies

PHP (Backend)
composer install

JavaScript (Fronten)
npm install

cp .env.example. env

Generate the app key:
php artisan key:generate

touch database/database.sqlite

npm run build        # For production
npm run dev          # For development with hot reload

