<?php include("dbcon.php");

$createTableSql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actions VARCHAR(50) DEFAULT 'Edit | Delete'
)";

if (!$conn->query($createTableSql)) {
    die("Could not prepare the users table: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?status=error");
        exit;
    }

    $insert = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $insert->bind_param("ss", $name, $email);

    if ($insert->execute()) {
        header("Location: index.php?status=added");
        exit;
    }

    header("Location: index.php?status=error");
    exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
if (!$users) {
    die("Could not fetch users: " . $conn->error);
}

$alerts = [
    "added" => ["success", "User added successfully."],
    "updated" => ["success", "User updated successfully."],
    "deleted" => ["success", "User deleted successfully."],
    "error" => ["danger", "Unable to add user. Check the submitted name and email."],
];
$status = $_GET["status"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .user-layout {
            display: flex;
            align-items: flex-start;
            gap: 3rem;
        }

        .user-form-panel,
        .user-table-panel {
            flex: 1;
            min-width: 0;
        }

        @media (max-width: 991.98px) {
            .user-layout {
                flex-direction: column;
            }
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 0.25rem;
            padding: 0.375rem 11.25rem;
            font-size: 1rem;

        }
        .btn btn-sm btn-warning{
            background-color: #ff4907;
            border-color: #ff4907;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    
    </style>
</head>
<body>
    <main class="container py-5">
        <h1 class="text-center mb-4">User Management System</h1>

        <?php if (isset($alerts[$status])): ?>
            <div class="alert alert-<?= htmlspecialchars($alerts[$status][0], ENT_QUOTES, "UTF-8") ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($alerts[$status][1], ENT_QUOTES, "UTF-8") ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="user-layout">
            <div class="user-form-panel">
                <form method="POST" action="index.php" class="mb-4">
                    <div class="mb-2" style="max-width: 400px;">
                        <h2 class="h4 mb-3">Add New User</h2>
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Full Name" required>
                    </div>
                    <div class="mb-2" style="max-width: 400px;">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Address" required>
                    </div>
                    <button type="submit" class="btn-primary">Submit</button>
                </form>
            </div>

            <div class="user-table-panel">
                <h2 class="h4 mb-3">Registered Users</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Joined</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) $user["id"], ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($user["name"], ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($user["email"], ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($user["created_at"], ENT_QUOTES, "UTF-8") ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-warning">Edit</a><br>
                                        <a class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</html>