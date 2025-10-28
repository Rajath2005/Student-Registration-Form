# 🎓 Student Registration Form

A responsive student registration form built with HTML, CSS, and JavaScript for the frontend, and PHP with a MySQL database for the backend. Features client-side and server-side validation, making it ideal for web development courses or academic projects.

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white)

## ✨ Features

- ✅ **Form Validation** - Client-side (JavaScript) and server-side (PHP) validation
- 📱 **Responsive Design** - Works on both desktop and mobile devices
- 🗄️ **Backend Integration** - PHP processes data and stores in MySQL database
- ⚠️ **Error Handling** - Clear feedback for invalid inputs
- 🎯 **Dynamic Result Display** - Success message after submission

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black) ![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=flat-square&logo=jquery&logoColor=white) |
| **Backend** | ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white) | 
| **Database** | ![MySQL](https://img.shields.io/badge/MySQL-005C84?style=flat-square&logo=mysql&logoColor=white) | 

## 📁 Project Structure

```
Student-Registration-Form/
│
├── index.html          # Main registration form
├── style.css           # UI styling
├── script.js           # Form validation logic
├── submit.php          # PHP backend to handle form submission
├── db_connect.php      # Database connection file
└── README.md           # Project documentation
```

## 🚀 Setup & Installation

### 1. Local Setup (XAMPP)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Rajath2005/Student-Registration-Form.git
   ```

2. **Move to htdocs:**
   `C:\xampp\htdocs\Student-Registration-Form`

3. **Start Apache and MySQL** from XAMPP Control Panel

4. **Create Database:**
   - Visit `http://localhost/phpmyadmin`
   - Create database `student_db`

5. **Configure Database** in `db_connect.php`:
   ```php
   <?php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "student_db";
   ?>
   ```

6. **Access the project:**
   `http://localhost/Student-Registration-Form`

### 2. Online Hosting

1. **Upload files** to your web hosting file manager
2. **Create MySQL database** in hosting control panel
3. **Update credentials** in `db_connect.php`
4. **Access via your domain**

## 📸 Preview

![Student Registration Form Preview](assets/preview.png)

## 🔮 Future Enhancements

- 📧 Email notifications on registration
- 👨‍💼 Admin panel for student management
- 📊 CSV export functionality
- 🛡️ reCAPTCHA integration

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/NewFeature`)
3. Commit changes (`git commit -m "Added new feature"`)
4. Push to branch (`git push origin feature/NewFeature`)
5. Open a Pull Request

## 👨‍💻 Author

**Rajath Kiran**  
[![GitHub](https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white)](https://github.com/Rajath2005)

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

⭐ Star this repo if you found it helpful!
