Project Name: USSD Inventory Management Application
Project Overview
This USSD-based application is a lightweight inventory management system that allows an admin to manage stock using a mobile phone no internet required. It is ideal for basic product tracking through a simple menu-driven interface.
Purpose
The goal of this project is to allow an administrator to:
•	Register into the system
•	Access the main menu after registration
•	Add new products to the system
•	Record stock coming in (Product In) by entering product Name, entering quantity, and price
•	Record stock going out (Product Out) by entering product name, entering quantity and price. 
•	View all stored products, product in and product out with their details
Key Features
1.	Admin Registration
o	The admin must register before accessing any other features.
o	Upon successful registration, an SMS is sent to confirm that the registration was successful.
2.	Main Menu Options
Once registered, the admin can access the following:
o	Add Product: Add a new product name to the system.
o	Product In: Enter product name, and then enter quantity.
o	Product Out: Enter product name and record outgoing quantity.
o	View Products: Display all added products with their Updated Quantity.

3.	Add Product
o	Admin inputs the product name only.
o	The product is saved and becomes available for selection during Product In and Product Out operations.
4.	Product In
o	Admin must Enter Product Name
o	Admin then inputs:
	Quantity of stock received
o	The system records the stock-in operation.
5.	Product Out
o	Admin must Enter Product Name and provides quantity being taken out of stock.
o	This helps maintain an accurate inventory.
6.	View Products
o	Displays all product names along with stock quantities.
 Technologies Used
•	Backend: PHP
•	Database: MySQL
•	Platform: XAMPP (Local Server)
•	Interface: USSD logic via PHP (index.php and menu.php)
How It Works
1.	User dials a USSD code (e.g., *123#).
2.	index.php processes the request.
3.	menu.php navigates the user through menu options.
4.	The admin selects an action:
o	Add Product → Inputs name only.
o	Product In → Enter Product Name → Enter quantity.
o	Product Out →Enter Product Name → Enter quantity.
o	View Products → Views list of all products.
5.	Each action interacts with the database and returns a simple text response.

Follow these steps to download and run the USSD Inventory Management Application on a local machine:
Prerequisites
Before running the project, make sure the following tools are installed:
•	XAMPP – for running Apache and MySQL locally
•	A web browser
•	A USSD simulator (optional, for better testing experience)
🔽 Installation Steps
1.	Download the Project Files
o	Use project folder directly OR
o	Or use hosted on GitHub clone the repository using:
git clone [https://github.com/yourusername/ussd-inventory-app.git](https://github.com/samuell-k/inventory_management_ussd_based)
2.	Move Project Folder into XAMPP
o	Copy the entire min-ussd folder into:
o	C:\xampp\htdocs\
3.	Start XAMPP
o	Open XAMPP Control Panel
o	Start Apache and MySQL
4.	Import the Database
o	Open your browser and go to:
http://localhost/phpmyadmin
o	Create a new database (e.g., ussd_inventory)
o	Import the SQL file provided (ussd_inventory.sql) into this database
5.	Access the Application
o	In your browser, go to:
        http://localhost/min-ussd/index.php
o	From here, simulate a USSD session by entering inputs as if navigating a mobile menu.
Optional: Use a USSD Testing Tool
To simulate USSD more accurately, you can use:
•	Africa's Talking Sandbox
•	Postman


Developer’s Name:
•	ISHIMIRWE GIHOZO Cynthia
•	KAMANZI Samuel
