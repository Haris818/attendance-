<?php
require 'config.php'; require_login();
$from=$_GET['from'] ?? date('Y-m-01');$to=$_GET['to'] ?? date('Y-m-d');$class=trim($_GET['class'] ?? '');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$from))$from=date('Y-m-01');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$to))$to=date('Y-m-d');
$classes=$pdo->query('SELECT DISTINCT class_name FROM students ORDER BY class_name')->fetchAll(PDO::FETCH_COLUMN);
$sql="SELECT s.id,s.roll_no,s.full_name,s.class_name,
SUM(a.status='Present') present_count,SUM(a.status='Absent') absent_count,
SUM(a.status='Leave') leave_count,SUM(a.status='Late') late_count,COUNT(a.id) marked_count
FROM students s LEFT JOIN attendance a ON a.student_id=s.id AND a.attendance_date BETWEEN ? AND ?
WHERE (?='' OR s.class_name=?) GROUP BY s.id ORDER BY s.class_name,s.roll_no";
$stmt=$pdo->prepare($sql);$stmt->execute([$from,$to,$class,$class]);$rows=$stmt->fetchAll();
$page_title='Attendance Reports';$active='reports';require 'header.php';
?>
<div class="panel"><div class="panel-heading"><div><h2>Attendance Report</h2><p class="muted">View attendance totals for a selected date range.</p></div><button class="btn secondary no-print" onclick="window.print()">Print Report</button></div>
<form method="get" class="filter-row report-filter"><label>From<input type="date" name="from" value="<?= h($from) ?>" required></label><label>To<input type="date" name="to" value="<?= h($to) ?>" required></label><label>Class<select name="class"><option value="">All classes</option><?php foreach($classes as $c): ?><option value="<?= h($c) ?>" <?= $class===$c?'selected':'' ?>><?= h($c) ?></option><?php endforeach; ?></select></label><button class="btn" type="submit">Generate</button></form>
<p class="muted report-range">Period: <?= h($from) ?> to <?= h($to) ?> · <?= count($rows) ?> student(s)</p>
<div class="table-wrap"><table><thead><tr><th>Roll No.</th><th>Student</th><th>Class</th><th>Present</th><th>Absent</th><th>Leave</th><th>Late</th><th>Marked</th><th>Attendance %</th></tr></thead><tbody>
<?php foreach($rows as $r): $present=(int)$r['present_count'];$marked=(int)$r['marked_count'];$pct=$marked?round($present/$marked*100):0; ?><tr><td><?= h($r['roll_no']) ?></td><td class="name-cell"><?= h($r['full_name']) ?></td><td><?= h($r['class_name']) ?></td><td><?= $present ?></td><td><?= (int)$r['absent_count'] ?></td><td><?= (int)$r['leave_count'] ?></td><td><?= (int)$r['late_count'] ?></td><td><?= $marked ?></td><td><span class="percent-pill"><?= $marked ? $pct.'%' : '—' ?></span></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="9" class="empty-cell">No students found for this filter.</td></tr><?php endif; ?>
</tbody></table></div><p class="muted small report-note">Attendance % = Present ÷ all marked attendance records × 100. Dates with no attendance record are not included.</p></div>
<?php require 'footer.php'; ?>
