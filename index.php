<?php
$dataFile = 'data.json';
$tasks = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

// Tambah tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $tasks[] = [
        'id' => uniqid(),
        'text' => htmlspecialchars($_POST['task']),
        'done' => false
    ];
    file_put_contents($dataFile, json_encode($tasks, JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit();
}

// Toggle selesai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'toggle') {
    foreach ($tasks as &$task) {
        if ($task['id'] === $_POST['id']) {
            $task['done'] = $_POST['done'] === '1';
        }
    }
    file_put_contents($dataFile, json_encode($tasks, JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit();
}

// Hapus
if (isset($_GET['delete'])) {
    $tasks = array_filter($tasks, fn($t) => $t['id'] !== $_GET['delete']);
    file_put_contents($dataFile, json_encode(array_values($tasks), JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit();
}

// Edit
$editTask = null;
if (isset($_GET['edit'])) {
    foreach ($tasks as $t) {
        if ($t['id'] === $_GET['edit']) {
            $editTask = $t;
            break;
        }
    }
}

// Update teks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    foreach ($tasks as &$t) {
        if ($t['id'] === $_POST['id']) {
            $t['text'] = htmlspecialchars($_POST['task']);
        }
    }
    file_put_contents($dataFile, json_encode($tasks, JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit();
}

// Filter tampilan
$filter = $_GET['filter'] ?? 'all';
$filteredTasks = match ($filter) {
    'done' => array_filter($tasks, fn($t) => $t['done']),
    'undone' => array_filter($tasks, fn($t) => !$t['done']),
    default => $tasks
};
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List Interaktif</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav>
        <div class="nav-title">📝 To-Do App Interaktif </div>
        <ul>
            <li><a href="index.php" <?= $filter === 'all' ? 'class="active"' : '' ?>>Semua</a></li>
            <li><a href="?filter=done" <?= $filter === 'done' ? 'class="active"' : '' ?>>Selesai</a></li>
            <li><a href="?filter=undone" <?= $filter === 'undone' ? 'class="active"' : '' ?>>Belum Selesai</a></li>
        </ul>
    </nav>

    <main>
        <!-- Form -->
        <?php if ($editTask): ?>
            <form method="POST" class="form-edit">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $editTask['id'] ?>">
                <input type="text" name="task" value="<?= $editTask['text'] ?>" required>
                <button type="submit">Simpan</button>
                <a href="index.php">Batal</a>
            </form>
        <?php else: ?>
            <form method="POST" class="form-create">
                <input type="hidden" name="action" value="create">
                <input type="text" name="task" placeholder="Tugas baru..." required>
                <button type="submit">Tambah</button>
            </form>
        <?php endif; ?>

        <!-- Daftar Tugas -->
        <div class="task-list">
            <?php foreach ($filteredTasks as $task): ?>
                <div class="task-item <?= $task['done'] ? 'done' : '' ?>">
                    <form method="POST" class="checkbox-form">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <input type="hidden" name="done" value="<?= $task['done'] ? '0' : '1' ?>">
                        <input type="checkbox" onchange="this.form.submit()" <?= $task['done'] ? 'checked' : '' ?>>
                        <span><?= $task['text'] ?></span>
                    </form>
                    <div class="actions">
                        <a href="?edit=<?= $task['id'] ?>">Edit</a>
                        <a href="?delete=<?= $task['id'] ?>" class="delete">Hapus</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>
