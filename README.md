Kebele Management System - Readme
Overview
The Kebele Management System is a web-based application designed to manage administrative tasks for a local kebele (sub-city) office. This system handles resident information, certificate generation, and administrative functions.

Project Structure
Kebele Management System/
├── assets/                 # Static resources (images, CSS, JS)
├── auth/                   # Authentication modules
│   ├── admin_login.php     # Administrator login
│   ├── login.php          # User login
│   └── registration.php   # User registration
├── certificates/           # Certificate management
├── dashboard/             # Main dashboard interface
├── resindet name/         # Resident management (typo in folder name)
│   ├── uploads/           # File upload directory
│   │   └── death.png      # Death certificate template
│   ├── delete.php         # Delete resident records
│   ├── edit.php           # Edit resident information
│   ├── main.php           # Main resident management page
│   └── print.php          # Print functionality
├── service/               # Service modules
├── view/                  # View pages for different certificates
│   ├── birthview.php      # Birth certificate view
│   ├── merrigeview.php    # Marriage certificate view (typo in filename)
│   ├── new_id_view.php    # New ID view
│   └── update_id_view.php # Update ID view
├── d.png                  # Dashboard icon/image
└── db_connection.php      # Database configuration
System Features
Authentication & Security
Admin Login: Secure administrator access

User Login: Resident login portal

Registration: New user account creation

Resident Management
Add/Edit Residents: Manage resident information

Delete Records: Remove resident data

File Uploads: Store documents and images

Print Functionality: Generate printable documents

Certificate Services
Birth Certificates: View and manage birth records

Marriage Certificates: Handle marriage documentation

ID Services: Process new and updated ID cards

Death Certificates: Manage death records

Dashboard
Centralized interface for system navigation

Quick access to all management functions

Technical Requirements
Web Server: Apache/Nginx

PHP: Version 7.0 or higher

Database: MySQL

Browser Support: Modern web browsers

Installation Instructions
Download the project files to your web server directory

Configure database settings in db_connection.php

Create the necessary database tables

Set permissions for the uploads/ directory (write access)

Access the system through your web browser

File Structure Notes
The folder resindet name appears to have a typo and should likely be resident_name

merrigeview.php contains a typo and should be marriageview.php

Ensure proper file permissions for uploads and temporary directories

Security Considerations
Keep db_connection.php secure with proper database credentials

Regularly update the system and dependencies

Implement proper input validation and sanitization

Use secure session management

Support
For technical support or issues with the Kebele Management System, please contact your system administrator or the development team.
