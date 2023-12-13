# **ICL System**

> **NTUIM 112-1 Database Management Demo**
> 

## File Structure

- **`README`**: Provides setup instructions and information about the project.
- **`index.php`**: The home page of the ICL System that links to the user and admin interfaces. [to modify]
- **`composer.json`**: Defines the project's PHP dependencies and other metadata.
- **`composer.lock`**: Lock file to record the exact versions of dependencies installed.
- **`eloquent.php`**: Sets up the Eloquent ORM configuration and initializes the database connection.
- **`admin_ORM.php`**: Administrator interface for the ORM-based search. [to modify]
- **`user_ORM.php`**: User interface for the ORM-based search. [to modify]
- **`style.css`**: Contains the CSS styles for the project's frontend.

請先參考 index.php，會根據是否登入、登入的身分別，render 不同的畫面，檔案也連結好了

測試帳密
admin: [ ID=admin, PASSWORD=123 ]
student: [ ID=松浦明日香, PASSWORD=53 ]
school: [ ID=七賢國中, PASSWORD=523504 ]

- **`login.php`**: 登入，記錄使用者名稱（ID）、ID（password）、身分別（admin / student / school）
                   在其他需要登入才能使用的頁面，先寫 session_start()，並確認使用者是否登入，
                   再透過
                   $userID = $_SESSION['user_id'];
                   $password = $_SESSION['password'];
                   $identity = $_SESSION['identity'];
                   取得相應的變數。
                   根據 identity 檢查身分別是否 valid。
- **`logout.php`**: 登出，結束 session
- **`trips.php`**: 
- **`publicStatistics.php`**: 
- **`getSchoolsByCounty`**: 根據 public statistics 中 search a school 的 county 欄位，動態 fetch 位於該 county 的 schools
- **`adminStatistics.php`**: 

[to implement]
- **`adminStudents.php`**: admin 查詢 students
- **`student.php`**: student 的 my page

[to implement]
- **`adminSchools.php`**: admin 查詢 schools
- **`school.php`**: school 的 my page










## **Installation and Setup**

### **Step 1: System Requirements** 

- PHP 7.4+
- XAMPP
- Composer
- PostgreSQL

Please go through the following steps to set up your environment

### **Step 2: XAMPP Installation**

Download and install XAMPP from the [official website](https://www.apachefriends.org/index.html). If you do not plan to use MySQL, you may unselect it. 

### **Step 3: Composer Installation**

Install Composer from the [official website](https://getcomposer.org/download/).

### **Step 4: Project Deployment**

Unzip the provided project archive into the **`htdocs`** directory of XAMPP.

### **Step 5: Composer Dependencies**

Open your system console, navigate to the project folder, and execute **`composer install`** to install the required PHP packages, including Eloquent ORM. 

### **Step 6: Database Configuration** (skip this step if you have done so before)

Create a PostgreSQL database named **`ICL`**. Import the provided **`.sql`** file to populate your database with the necessary tables and data.

### **Step 7: Eloquent Configuration**

Configure the database connection in **`eloquent.php`** with your PostgreSQL credentials. Create **`db_password.txt`** and put your password there. 

### **Step 8: Installing PostgreSQL driver for PHP**

Go to your PHP directory (e.g., at **`C:\xampp\php`**) to edit php.ini using any plain text editor. Uncomment **`;extension=pdo_pgsql`** and **`;extension=pgsql'** by removing the semicolons. 









## **Running the Application**

### **Starting Apache**

After installation, start the Apache web server. To start Apache, go to the right directory (e.g., C:\xampp\apache\bin) to execute httpd.exe. If everything goes well, you may see the Apache homepage at **`http://localhost/`**.  

### **See the index page**

Access the system via **`http://localhost/your-project-folder/`** in your web browser.

[to modify]

### **User Interface**

Navigate to **`user.php`** for the User Search interface.

### **Admin Interface**

Navigate to **`admin.php`** for the Administrator Search interface.

[/to modify]


