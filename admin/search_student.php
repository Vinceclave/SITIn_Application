    <?php
    require_once '../config/config.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idno'])) {
        $studentIDNO = trim($_POST['idno']); // Get IDNO input

        // Query to fetch student first & last name
        $stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE idno = ?");
        $stmt->bind_param("s", $studentIDNO);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($student = $result->fetch_assoc()) {
            $fullName = $student['firstname'] . ' ' . $student['lastname'];

            // Query to fetch remaining sessions from student_session table
            $stmt2 = $conn->prepare("SELECT session FROM student_session WHERE idno = ?");
            $stmt2->bind_param("s", $studentIDNO);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            if ($session = $result2->fetch_assoc()) {
                $remainingSessions = $session['session'];
            } else {
                $remainingSessions = "No data"; // If no session record found
            }

            // Return JSON response
            echo json_encode([
                "status" => "found",
                "full_name" => $fullName,
                "remaining_sessions" => $remainingSessions
            ]);
        } else {
            echo json_encode(["status" => "not_found"]);
        }
    }
    ?>
