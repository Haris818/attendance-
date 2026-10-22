<?php
require 'config.php'; require_login();
$error='';$success='';
$date=$_GET['date'] ?? date('Y-m-d');$class=trim($_GET['class'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) $date=date('Y-m-d');
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf(); $date=$_POST['attendance_date'] ?? date('Y-m-d');$class=trim($_POST['class_name'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) $error='Please select a valid date.';
    else {
        try {
            $pdo->beginTransaction();
            $stmt=$pdo->prepare('SELECT id FROM students WHERE class_name=?');$stmt->execute([$class]);$ids=$stmt->fetchAll(PDO::FETCH_COLUMN);
            $save=$pdo->prepare('INSERT INTO attendance (student_id,attendance_date,status,note) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status), note=VALUES(note)');
            foreach($ids as $id) {
                $status=$_POST['status'][$id] ?? 'Present';
                if (!in_array($status,['Present','Absent','Leave','Late'],true)) $status='Present';
                $note=trim($_POST['note'][$id] ?? '');
                $save->execute([$id,$date,$status,$note ?: null]);
            }
            $pdo->commit();$success='Attendance saved successfully.';
        } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack();$error='Could not save attendance. Please check your database and try again.'; }
    }
}
$classes=$pdo->query('SELECT DISTINCT class_name FROM students ORDER BY class_name')->fetchAll(PDO::FETCH_COLUMN);
if($class==='' && $classes)$class=$classes[0];
$students=[];
if($class!=='') {
    $stmt=$pdo->prepare('SELECT s.*,a.status,a.note FROM students s LEFT JOIN attendance a ON a.student_id=s.id AND a.attendance_date=? WHERE s.class_name=? ORDER BY s.roll_no');
    $stmt->execute([$date,$class]);$students=$stmt->fetchAll();
}
$page_title='Take Attendance';$active='attendance';require 'header.php';
?>
<?php if($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?><?php if($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<div class="panel"><div class="panel-heading"><div><h2>Daily Attendance Register</h2><p class="muted">Choose a class and date, then mark each student's status.</p></div></div>
<form method="get" class="filter-row attendance-filter"><label>Date<input type="date" name="date" value="<?= h($date) ?>" required></label><label>Class<select name="class" required><?php foreach($classes as $c): ?><option value="<?= h($c) ?>" <?= $class===$c?'selected':'' ?>><?= h($c) ?></option><?php endforeach; ?></select></label><button class="btn" type="submit">Load Students</button></form>
<?php if(!$classes): ?><div class="empty-state"><h3>No students yet</h3><p>Add students before taking attendance.</p><a class="btn" href="students.php">Add Students</a></div>
<?php elseif(!$students): ?><div class="empty-state"><h3>No students in this class</h3><p>Please select a class with registered students.</p></div>
<?php else: ?><form method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="attendance_date" value="<?= h($date) ?>"><input type="hidden" name="class_name" value="<?= h($class) ?>">
<div class="attendance-summary"><span><b><?= count($students) ?></b> students</span><span><?= h($class) ?></span><span><?= h(date('d M Y',strtotime($date))) ?></span></div>
<div class="table-wrap"><table><thead><tr><th>Roll No.</th><th>Student Name</th><th>Attendance Status</th><th>Note (optional)</th></tr></thead><tbody>
<?php foreach($students as $s): $current=$s['status'] ?: 'Present'; ?><tr><td><?= h($s['roll_no']) ?></td><td class="name-cell"><?= h($s['full_name']) ?></td><td><select name="status[<?= (int)$s['id'] ?>]" class="status-select"><?php foreach(['Present','Absent','Leave','Late'] as $st): ?><option value="<?= h($st) ?>" <?= $current===$st?'selected':'' ?>><?= h($st) ?></option><?php endforeach; ?></select></td><td><input class="note-input" name="note[<?= (int)$s['id'] ?>]" value="<?= h($s['note'] ?? '') ?>" placeholder="Optional note"></td></tr><?php endforeach; ?>
</tbody></table></div><div class="submit-row"><span class="muted small">Saving again updates existing records for this date.</span><button class="btn" type="submit">Save Attendance</button></div></form><?php endif; ?></div>
<?php require 'footer.php'; ?>
