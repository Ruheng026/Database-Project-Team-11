# Database-Project-Team-11

# **ICL System**

> **NTUIM 112-1 Database Management Demo**
> 

## File Structure

- **`README.md`**: Provides setup instructions and information about the project.

- **`composer.json`**: Defines the project's PHP dependencies and other metadata.
- **`composer.lock`**: Lock file to record the exact versions of dependencies installed.
- **`eloquent.php`**: Sets up the Eloquent ORM configuration and initializes the database connection.
- **`style.css`**: Contains the CSS styles for the project's frontend.
- **`db_password.txt`**: Please create a **`db_password.txt`** and put your password in it.
- **`.gitignore`**

- **`ICL_backup.sql`**: The backup file for the database.
- **`sql queries`**: The sql queries that our system will use.

- **`index.php`**: The home page of the ICL System that links to the user and admin interfaces.
- **`login.php`**: 登入，開始 session，記錄使用者名稱（ID）、ID（password）、身分別（admin / student / school）
- **`logout.php`**: 登出，結束 session，回到 `index.php`
- **`publicStatistics.php`**: ICL 公開的資訊，包含按國籍排序參與者數量、合作的學校、學校參與的學期與 session 資料等。
                              使用者無論是否登入都能查看本頁
- **`getSchoolsByCounty`**: 根據 public statistics 中 search a school 的 county 欄位，動態 fetch 位於該 county 的 schools
- **`adminStatistics.php`**: 僅 ICL admin 可以查看的頁面，用於搜尋 overall attendance rate、attendance rate 最低的學生、no show rate 最高的 trips、課程資訊等
- **`adminTrips.php`**: 僅 ICL admin 可以查看的頁面，用於搜尋 trips 的資料，包含日期、拜訪的學校、參與的學生等

- **`adminStudents.php`**: admin 查詢 students
- **`adminSearchStudents.php`**: admin 查詢 students
- **`student.php`**: student 的 My Page，可以查看並編輯個人資訊
- **`editStudent.php`**: admin 或 student 編輯 student 個人資訊

- **`adminSchools.php`**: admin 查詢 schools
- **`adminSearchStudents.php`**: admin 查詢 schools
- **`school.php`**: school 的 My Page，可以查看並編輯學校資訊
- **`editSchool.php`**: admin 或 school 編輯學校資訊










## **Installation and Setup (和上課範例相同)**

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

Go to your PHP directory (e.g., at **`C:\xampp\php`**) to edit php.ini using any plain text editor. Uncomment **`;extension=pdo_pgsql`** and **`;extension=pgsql`** by removing the semicolons. 









## **Running the Application**

### **Starting Apache**

After installation, start the Apache web server. To start Apache, go to the right directory (e.g., C:\xampp\apache\bin) to execute httpd.exe. If everything goes well, you may see the Apache homepage at **`http://localhost/`**.  

### **See the index page**

Access the system via **`http://localhost/your-project-folder/`** in your web browser.

### **測試帳號與密碼**
- **admin**: [ ID=admin, PASSWORD=123 ]
- **student**: [ ID=松浦明日香, PASSWORD=53 ]
- **school**: [ ID=湖埔國小, PASSWORD=714618 ]

### **Before Log In**

- Navigate to **`publicStatistics.php`** by clicking the **`Public Statistics`** button for the Public Search interface.
- Navigate to **`login.php`** by clicking the **`Login`** button.

### **After Log In**

#### **All Interface**

- Navigate to **`publicStatistics.php`** by clicking the **`Public Statistics`** button for the Public Search interface.
- Navigate to **`logout.php`** and then **`index.php`** by clicking the **`Log Out`** button.
- If not already at **`index.php`**, navigate to **`index.php`** by clicking the **`Home`** button.

#### **Student Interface**

Navigate to **`student.php`** by clicking the **`My Page`** button for the Student Search interface.

#### **School Interface**

Navigate to **`school.php`** by clicking the **`My Page`** button for the School Search interface.

#### **Admin Interface**

- Navigate to **`adminStudents.php`** by clicking the **`Admin Students`** button for the Administrator Student Search interface.
- Navigate to **`adminSchools.php`** by clicking the **`Admin Schools`** button for the Administrator School Search interface.
- Navigate to **`adminTrips.php`** by clicking the **`Admin Trips`** button for the Administrator Trip Search interface.
- Navigate to **`adminStatistics.php`** by clicking the **`Admin Statistics`** button for the Administrator Search interface.



