<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$host = 'localhost'; 
$dbname = 'books3'; 
$user = 'mark'; 
$pass = 'mark';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle book search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT id, author, title, publisher, is_read FROM books WHERE title LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['author']) && isset($_POST['title']) && isset($_POST['publisher']) && isset($_POST['is_read'])) {
        // Insert new entry
        $author = htmlspecialchars($_POST['author']);
        $title = htmlspecialchars($_POST['title']);
        $publisher = htmlspecialchars($_POST['publisher']);
        $is_read = htmlspecialchars($_POST['is_read']);
        
        $insert_sql = 'INSERT INTO books (author, title, publisher, is_read) VALUES (:author, :title, :publisher, :is_read)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['author' => $author, 'title' => $title, 'publisher' => $publisher, 'is_read' => $is_read]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        
        $delete_sql = 'DELETE FROM books WHERE id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    } elseif (isset($_POST['edit_id'])) {
        $edit_id = (int) $_POST['edit_id'];
        $edit_sql = "UPDATE `books` SET `is_read` = 'yes' WHERE `books`.`id` = :id";
        $stmt_edit = $pdo->prepare($edit_sql);
        $stmt_edit->execute(['id' => $edit_id]);
    }
}

// Get all books for main table
$sql = 'SELECT id, author, title, publisher, is_read FROM books';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Betty's Personal Book Manager</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">Betty's Personal Book Manager</h1>
        <p class="hero-subtitle">"Track your book collection"</p>
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for a Book in Collection</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by Title:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                <th>ID</th>
                                <th>Author</th>
                                <th>Title</th>
                                <th>Publisher</th>
                                <th>Has Been Read?</th>
                                <th>Read Book</th>
                                <th>Remove Book From Collection</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['publisher']); ?></td>
                                <td><?php echo htmlspecialchars($row['is_read']); ?></td>
                                <td>
                                    <form action="index5.php" method="post" style="display:inline;">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <input type="submit" value="Read Book">
                                    </form>
                                </td>

                                <td>
                                    <form action="index5.php" method="post" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <input type="submit" value="Remove Book From Collection ">
                                    </form>
                                </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No books found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All Books in Collection</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author</th>
                    <th>Title</th>
                    <th>Publisher</th>
                    <th>Has Been Read?</th>
                    <th>Read Book</th>
                    <th>Remove Book From Collection</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['publisher']); ?></td>
                    <td><?php echo htmlspecialchars($row['is_read']); ?></td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Read Book">
                        </form>
                    </td>

                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Remove Book From Collection ">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form section with container -->
    <div class="form-container">
        <h2>Add a Book to your Collection</h2>
        <form action="index5.php" method="post">
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" required>
            <br><br>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <br><br>
            <label for="publisher">Publisher:</label>
            <input type="text" id="publisher" name="publisher" required>
            <br><br>
            <label for="is_read">Read?:</label>
            <input type="radio" id="yes" name="is_read" value="yes">
            <label for="yes">Yes</label>
            <input type="radio" id="no" name="is_read" value="no">
            <label for="no">No</label>
            <br><br>
            <input type="submit" value="Add Book to Collection">
        </form>
    </div>
</body>
</html>