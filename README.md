# Construction Management System - Deployment Guide

This project is now configured for local development (XAMPP) and cloud deployment (Railway).

## 1. Push Code to GitHub

Since the automated Git command failed, please run these manually in your **Command Prompt** or **PowerShell** inside the `f:\XAMPP SQL\New folder\htdocs\construction_app` folder:

1. **Initialize Git**:
   ```bash
   git init
   ```
2. **Add Files**:
   ```bash
   git add .
   ```
3. **Commit**:
   ```bash
   git commit -m "Initial commit - Cloud Ready"
   ```
4. **Create a Remote**:
   Go to [GitHub](https://github.com/new) and create a repository named `construction-app`.
5. **Push**:
   ```bash
   git remote add origin https://github.com/ryukh-atsuto/construction-app.git
   git branch -M main
   git push -u origin main
   ```

## 2. Deploy to Railway

1. **Create Account**: Log in to [Railway.app](https://railway.app/).
2. **New Project**: Click "New Project" -> "Deploy from GitHub repo" -> Select `construction-app`.
3. **Add Database**:
   - In your Railway project, click "Add Service" -> "Database" -> "MySQL".
4. **Link Variables**:
   - Railway automatically provides `MYSQLHOST`, `MYSQLUSER`, `MYSQLPASSWORD`, etc., when you add a MySQL service.
   - The code in `config/db.php` is already designed to pick these up automatically!
5. **Run Migrations**:
   - Once deployed, visit `your-railway-url.up.railway.app/config/db_migration_v3.php` once to set up the tables in the cloud database.

## 3. Local Development
Your local setup in XAMPP still works! The code will detect that it's running on `localhost` and use your local settings.
