<?php
require 'config.php'; require_login();
$page_title = 'Dashboard'; $active = 'dashboard';
$today = date('Y-m-d');
$total = (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$stmt = $pdo->prepare("SELECT status, COUNT(*) total FROM attendance WHERE attendance_date=? GROUP BY status");
$stmt->execute([$today]); $counts = ['Present'=>0,'Absent'=>0,'Leave'=>0,'Late'=>0];
foreach ($stmt as $r) $counts[$r['status']] = (int)$r['total'];
$marked = array_sum($counts);
$recent = $pdo->prepare('SELECT s.roll_no,s.full_name,s.class_name,a.status,a.attendance_date FROM attendance a JOIN students s ON s.id=a.student_id WHERE a.attendance_date=? ORDER BY s.class_name,s.roll_no LIMIT 8');
$recent->execute([$today]);
require 'header.php';
?>
<div class="welcome"><div><p class="eyebrow light">TODAY · <?= h(date('d M Y')) ?></p><h2>Good day, <?= h($_SESSION['admin_username']) ?> 👋</h2><p>Here is your school's attendance overview.</p></div><a class="btn white-btn" href="take_attendance.php">+ Take Attendance</a></div>
<div class="stats-grid">
<div class="stat-card"><div class="stat-top"><span>Total Students</span><span class="stat-icon blue">♙</span></div><div class="stat-number"><?= $total ?></div><div class="stat-foot">Registered students</div></div>
<div class="stat-card"><div class="stat-top"><span>Present Today</span><span class="stat-icon green">✓</span></div><div class="stat-number"><?= $counts['Present'] ?></div><div class="stat-foot">Marked present</div></div>
<div class="stat-card"><div class="stat-top"><span>Absent Today</span><span class="stat-icon red">×</span></div><div class="stat-number"><?= $counts['Absent'] ?></div><div class="stat-foot">Marked absent</div></div>
<div class="stat-card"><div class="stat-top"><span>Attendance Marked</span><span class="stat-icon purple">▤</span></div><div class="stat-number"><?= $marked ?> <small>/ <?= $total ?></small></div><div class="stat-foot"><?= $total ? round($marked/$total*100) : 0 ?>% of students</div></div>
</div>
<div class="panel"><div class="panel-heading"><div><h2>Today's Attendance</h2><p class="muted">Latest records for <?= h(date('d M Y')) ?></p></div><a class="text-link" href="reports.php">View reports →</a></div>
<?php if (!$recent->rowCount()): ?><div class="empty-state"><div class="empty-icon">✓</div><h3>No attendance marked yet</h3><p>Start by recording attendance for a class.</p><a class="btn" href="take_attendance.php">Take Attendance</a></div>
<?php else: ?><div class="table-wrap"><table><thead><tr><th>Roll No.</th><th>Student</th><th>Class</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach ($recent as $r): ?><tr><td><?= h($r['roll_no']) ?></td><td class="name-cell"><?= h($r['full_name']) ?></td><td><?= h($r['class_name']) ?></td><td><span class="status <?= strtolower($r['status']) ?>"><?= h($r['status']) ?></span></td><td><?= h($r['attendance_date']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div>
<?php require 'footer.php'; ?>
