STUDENT ATTENDANCE MANAGEMENT SYSTEM
====================================
Requirements:
- XAMPP with PHP 8+ and MySQL/MariaDB
- Any modern browser

INSTALLATION (Windows):
1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Copy the folder `student_attendance_system` into `C:\xampp\htdocs\`.
4. Open http://localhost/phpmyadmin
5. Click Import and import `database.sql` from this folder.
6. Open http://localhost/student_attendance_system/setup_admin.php once.
7. Login at http://localhost/student_attendance_system/login.php
   Username: admin
   Temporary password: ChangeMe123!
8. DELETE setup_admin.php after setup. Change the temporary password before using real student data.

FEATURES:
- Admin login
- Dashboard counts
- Add/edit/delete students and search/filter by class
- Daily attendance: Present, Absent, Leave, Late
- Attendance records can be updated for the same student/date
- Date range reports and print-friendly output
- Responsive layout

NOTES:
- Default XAMPP database credentials are user=root and blank password. If yours differ, edit config.php.
- Change the temporary password before using real student information.
- For internet/public hosting, use HTTPS, strong passwords, regular database backups, and remove setup_admin.php.
- Attendance percentage in reports is present / records marked during the selected date range. Dates without a saved attendance record are excluded.
