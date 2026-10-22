<?php
require 'config.php'; require_login();
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0); $roll = trim($_POST['roll_no'] ?? '');
            $name = trim($_POST['full_name'] ?? ''); $class = trim($_POST['class_name'] ?? '');
            $phone = trim($_POST['guardian_phone'] ?? '');
            if ($roll === '' || $name === '' || $class === '') throw new Exception('Roll number, student name and class are required.');
            if ($id) {
                $stmt=$pdo->prepare('UPDATE students SET roll_no=?,full_name=?,class_name=?,guardian_phone=? WHERE id=?');
                $stmt->execute([$roll,$name,$class,$phone ?: null,$id]); $success='Student updated successfully.';
            } else {
                $stmt=$pdo->prepare('INSERT INTO students (roll_no,full_name,class_name,guardian_phone) VALUES (?,?,?,?)');
                $stmt->execute([$roll,$name,$class,$phone ?: null]); $success='Student added successfully.';
            }
        } elseif ($action === 'delete') {
            $stmt=$pdo->prepare('DELETE FROM students WHERE id=?'); $stmt->execute([(int)$_POST['id']]); $success='Student deleted.';
        }
    } catch (PDOException $e) { $error = $e->getCode()==='23000' ? 'That roll number is already in use.' : 'Database error. Please try again.'; }
      catch (Exception $e) { $error=$e->getMessage(); }
}
$edit = null;
if (isset($_GET['edit'])) { $stmt=$pdo->prepare('SELECT * FROM students WHERE id=?'); $stmt->execute([(int)$_GET['edit']]); $edit=$stmt->fetch(); }
$q = trim($_GET['q'] ?? ''); $classFilter=trim($_GET['class'] ?? '');
$sql='SELECT * FROM students WHERE (full_name LIKE ? OR roll_no LIKE ?)'; $params=["%$q%","%$q%"];
if ($classFilter!=='') {$sql.=' AND class_name=?';$params[]=$classFilter;}
$sql.=' ORDER BY class_name, roll_no'; $stmt=$pdo->prepare($sql);$stmt->execute($params);$students=$stmt->fetchAll();
$classes=$pdo->query('SELECT DISTINCT class_name FROM students ORDER BY class_name')->fetchAll(PDO::FETCH_COLUMN);
$page_title='Students';$active='students';require 'header.php';
?>
<?php if ($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?><?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<div class="two-col"><div class="panel"><div class="panel-heading"><div><h2><?= $edit?'Edit Student':'Add New Student' ?></h2><p class="muted">Enter the student's details below.</p></div></div>
<form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
<label>Roll Number *<input name="roll_no" required value="<?= h($edit['roll_no'] ?? '') ?>" placeholder="e.g. 7-001"></label>
<label>Student Name *<input name="full_name" required value="<?= h($edit['full_name'] ?? '') ?>" placeholder="Full name"></label>
<label>Class / Grade *<input name="class_name" required value="<?= h($edit['class_name'] ?? '') ?>" placeholder="e.g. Class 7"></label>
<label>Guardian Phone<input name="guardian_phone" value="<?= h($edit['guardian_phone'] ?? '') ?>" placeholder="Optional"></label>
<div class="form-actions"><button class="btn" type="submit"><?= $edit?'Update Student':'Add Student' ?></button><?php if($edit): ?><a class="btn secondary" href="students.php">Cancel</a><?php endif; ?></div></form></div>
<div class="panel"><div class="panel-heading"><div><h2>Student Directory</h2><p class="muted"><?= count($students) ?> student(s) shown</p></div></div>
<form method="get" class="filter-row"><input name="q" value="<?= h($q) ?>" placeholder="Search name or roll no."><select name="class"><option value="">All classes</option><?php foreach($classes as $c): ?><option <?= $classFilter===$c?'selected':'' ?> value="<?= h($c) ?>"><?= h($c) ?></option><?php endforeach; ?></select><button class="btn" type="submit">Search</button></form>
<div class="table-wrap"><table><thead><tr><th>Roll No.</th><th>Name</th><th>Class</th><th>Actions</th></tr></thead><tbody>
<?php foreach($students as $s): ?><tr><td><?= h($s['roll_no']) ?></td><td class="name-cell"><?= h($s['full_name']) ?></td><td><?= h($s['class_name']) ?></td><td class="actions"><a href="students.php?edit=<?= (int)$s['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this student and their attendance records?')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button class="link-danger" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$students): ?><tr><td colspan="4" class="empty-cell">No students found.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php require 'footer.php'; ?>
