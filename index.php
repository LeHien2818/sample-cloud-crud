<?php
// Database connection
$host = "[VM2_INTERNAL_IP]"; // Replace with VM 2's internal IP, e.g., 10.128.0.3
$user = "root"; // Or use 'webuser' if you kept that setup
$password = "123456"; // Replace with your MySQL root password
$database = "crud_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create
if (isset($_POST['create'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $sql = "INSERT INTO users (name, email) VALUES ('$name', '$email')";
    $conn->query($sql);
}

// Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $sql = "UPDATE users SET name='$name', email='$email' WHERE id='$id'";
    $conn->query($sql);
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE id='$id'";
    $conn->query($sql);
}

// Read (Get all users)
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <title>CRUD Application</title>
    <style>
        table { border-collapse: collapse; width: 50%; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>CRUD Application</h1>

    <!-- Create Form -->
    <h2>Add New User</h2>
    <form method="post">
        Name: <input type="text" name="name" required><br><br>
        Email: <input type="email" name="email" required><br><br>
        <input type="submit" name="create" value="Add User">
    </form>

    <!-- Users Table (Read) -->
    <h2>Users List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td>
                    <!-- Update Button (Triggers Form Below) -->
                    <a href="?edit=<?php echo $row['id']; ?>">Edit</a>
                    <!-- Delete Button -->
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Update Form (Appears when Edit is clicked) -->
    <?php
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $edit_result = $conn->query("SELECT * FROM users WHERE id='$id'");
        $edit_row = $edit_result->fetch_assoc();
    ?>
        <h2>Update User</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
            Name: <input type="text" name="name" value="<?php echo $edit_row['name']; ?>" required><br><br>
            Email: <input type="email" name="email" value="<?php echo $edit_row['email']; ?>" required><br><br>
            <input type="submit" name="update" value="Update User">
        </form>
    <?php } ?>

</body>
</html>

<?php
$conn->close();
?>