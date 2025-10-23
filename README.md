# 🏛️ Kebele Management System

> A comprehensive web-based administrative solution for local kebele offices

---

## 📋 Overview

The **Kebele Management System** is a robust web application designed to streamline administrative tasks for local kebele (sub-city) offices. This system efficiently handles resident information management, certificate generation, and various administrative functions through an intuitive interface.

---

## 🗂️ Project Structure

---

## 🔍 Key Directory Overview

| Directory | Purpose | Key Files |
|-----------|---------|-----------|
| **🔐 auth/** | User authentication | Login, registration |
| **👥 resindet name/** | Resident management | Edit, delete, main |
| **📜 view/** | Certificate views | Birth, marriage, ID |
| **📁 uploads/** | File storage | Document templates |
| **🗃️ Root** | Core configuration | Database connection |

---

## ⚠️ Important Notes

- **Typo Alert**: `resindet name` → should be `resident_name`
- **Spelling Fix**: `merrigeview.php` → should be `marriageview.php`
- **Security**: `db_connection.php` contains sensitive database credentials
- **Storage**: `uploads/` directory requires write permissions

---

<div align="center">

**🏗️ Organized • 🔐 Secure • ⚡ Efficient**

</div>


---

## ⚡ System Features

### 🔐 Authentication & Security
- **👨‍💼 Admin Login** - Secure administrator access portal
- **👤 User Login** - Resident login interface
- **📝 Registration** - New user account creation system

### 👥 Resident Management
- **➕ Add/Edit Residents** - Comprehensive resident information management
- **🗑️ Delete Records** - Safe removal of resident data
- **📤 File Uploads** - Secure document and image storage
- **🖨️ Print Functionality** - Professional document generation

### 📜 Certificate Services
- **👶 Birth Certificates** - Complete birth records management
- **💑 Marriage Certificates** - Marriage documentation handling
- **🆔 ID Services** - New and updated ID card processing
- **💀 Death Certificates** - Death records management

### 🎯 Dashboard
- **📊 Centralized Interface** - Unified system navigation
- **⚡ Quick Access** - Instant access to all management functions

---

## 🛠️ Technical Requirements

| Component | Requirement |
|-----------|-------------|
| **🌐 Web Server** | Apache/Nginx |
| **🐘 PHP** | Version 7.0 or higher |
| **🗄️ Database** | MySQL |
| **🔧 Browser Support** | Modern web browsers |

---

## 🚀 Installation Instructions

### Step-by-Step Setup:

1. **📥 Download Project**
   ```bash
   # Clone or download project files to web server directory

  // Edit db_connection.php with your database credentials
  2. ⚙️ Database Configuration
$host = 'localhost';
$user = 'your_username';
$pass = 'your_password';
$db   = 'kebele_db';
3. 🗃️ Database Setup
-- Create necessary database tables
CREATE DATABASE kebele_db;
-- Import provided SQL schema
4.📁 Directory Permissions
# Set write permissions for uploads directory
chmod 755 uploads/
