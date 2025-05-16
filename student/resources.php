<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Check if resources table exists, if not create it
$check_table_query = "SHOW TABLES LIKE 'resources'";
$table_result = $conn->query($check_table_query);

if ($table_result->num_rows === 0) {
    // Create resources table
    $sql_file = file_get_contents('../database/resources_table.sql');
    
    // Split statements by semicolon
    $statements = explode(';', $sql_file);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $conn->query($statement);
        }
    }
}

// Get resources with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where_clause = "WHERE course_name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

// Count total resources
$count_query = "SELECT COUNT(*) as total FROM resources $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get resources for current page
$resources_query = "SELECT * FROM resources $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$resources_stmt = $conn->prepare($resources_query);

if (!empty($params)) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    $resources_stmt->bind_param($types, ...$params);
} else {
    $resources_stmt->bind_param("ii", $limit, $offset);
}

$resources_stmt->execute();
$resources_result = $resources_stmt->get_result();

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Now that all processing is done, include the header
require_once '../shared/header.php';
?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-book text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Learning Resources</h1>
                        <p class="text-lg text-gray-600">Find helpful resources for your studies.</p>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <form action="resources.php" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                            placeholder="Search resources..." 
                            class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="resources.php" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Resources Grid -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-indigo-600 p-4 text-white font-medium">
                    <h2 class="text-lg">Available Resources</h2>
                </div>
                
                <?php if ($resources_result->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        <?php while ($resource = $resources_result->fetch_assoc()): ?>
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-300">
                                <div class="h-40 bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center p-4">
                                    <?php if (!empty($resource['course_logo'])): ?>
                                        <img src="../uploads/<?php echo $resource['course_logo']; ?>" alt="<?php echo htmlspecialchars($resource['course_name']); ?>" class="max-h-full max-w-full object-contain">
                                    <?php else: ?>
                                        <i class="fas fa-book-open text-white text-5xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($resource['course_name']); ?></h3>
                                    <p class="text-sm text-gray-500 mb-4">Added: <?php echo date('M d, Y', strtotime($resource['created_at'])); ?></p>
                                    <a href="<?php echo htmlspecialchars($resource['link']); ?>" target="_blank" 
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-300 w-full justify-center">
                                        <i class="fas fa-external-link-alt mr-2"></i> Access Resource
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between">
                            <div>
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                                        Previous
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="text-gray-600">
                                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                            </div>
                            <div>
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="p-6 text-center">
                        <?php if (!empty($search)): ?>
                            <p class="text-gray-600">No resources found matching "<?php echo htmlspecialchars($search); ?>". Try a different search term.</p>
                        <?php else: ?>
                            <p class="text-gray-600">No resources available yet. Check back later!</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once '../shared/footer.php'; ?>
