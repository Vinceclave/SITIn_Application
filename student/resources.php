<?php
// Include necessary files like database connection, session, etc.
include '../config/config.php';
include '../shared/header.php';

// Set the number of resources per page
$perPage = 6;

// Get the current page number from the query string, default to page 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Fetch resources from the database with pagination
$query = "SELECT * FROM resources ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $offset, $perPage);
$stmt->execute();
$result = $stmt->get_result();
$resources = $result->fetch_all(MYSQLI_ASSOC);

// Count the total number of resources for pagination
$queryCount = "SELECT COUNT(*) FROM resources";
$resultCount = $conn->query($queryCount);
$totalResources = $resultCount->fetch_row()[0];
$totalPages = ceil($totalResources / $perPage);
?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center space-x-3">
                    <i class="fas fa-book text-indigo-600"></i>
                    <span>Available Learning Resources</span>
                </h1>
                <p class="text-lg text-gray-600 mt-1">Explore the learning materials available for you.</p>
            </div>

            <!-- Resource List (as cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
                <?php foreach ($resources as $resource): ?>
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all">
                    <div class="text-center">
                        <img src="../uploads/logos/<?php echo htmlspecialchars($resource['course_logo']); ?>" alt="Logo" class="w-24 h-24 object-cover rounded-full mx-auto mb-4">
                        <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($resource['course_name']); ?></h3>
                        <p class="text-sm text-gray-500 mb-4">Uploaded: <?php echo date("M d, Y", strtotime($resource['created_at'])); ?></p>
                        <a href="<?php echo htmlspecialchars($resource['link']); ?>" class="text-indigo-600 hover:underline" target="_blank">
                            View Course
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($resources)): ?>
                <div class="col-span-4 text-center text-gray-500">
                    <i class="fas fa-info-circle mr-2"></i>No resources available at the moment.
                </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-6">
                <nav>
                    <ul class="flex space-x-4">
                        <!-- Previous page link -->
                        <li>
                            <a href="?page=<?php echo max(1, $page - 1); ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                &laquo; Previous
                            </a>
                        </li>

                        <!-- Page number links -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li>
                            <a href="?page=<?php echo $i; ?>" class="px-4 py-2 <?php echo $i == $page ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800'; ?> rounded-lg hover:bg-indigo-700 transition">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <!-- Next page link -->
                        <li>
                            <a href="?page=<?php echo min($totalPages, $page + 1); ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Next &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>
    </main>
</div>

<?php include '../shared/footer.php'; ?>
