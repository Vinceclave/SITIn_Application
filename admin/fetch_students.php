<?php
require_once '../config/config.php';

$name = $_GET['name'] ?? '';
$course = $_GET['course'] ?? '';

$sql = "SELECT id, idno, lastname, firstname, course FROM users WHERE role = 'Student'";
$params = [];

if ($name) {
    $sql .= " AND (firstname LIKE ? OR lastname LIKE ?)";
    $params[] = "%$name%";
    $params[] = "%$name%";
}

if ($course) {
    $sql .= " AND course LIKE ?";
    $params[] = "%$course%";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<tr class='border-b'>
            <td class='p-2'>{$row['idno']}</td>
            <td class='p-2'>{$row['lastname']}</td>
            <td class='p-2'>{$row['firstname']}</td>
            <td class='p-2'>{$row['course']}</td>
            <td class='p-2'>
                <button onclick=\"editStudent({$row['id']}, '{$row['lastname']}', '{$row['firstname']}', '{$row['course']}')\" 
                    class='text-green-500 hover:underline'>Edit</button> | 
                <a href='delete_student.php?id={$row['id']}' class='text-red-500 hover:underline' onclick='return confirm(\"Are you sure?\")'>Delete</a>
            </td>
         </tr>";
}

$stmt->close();
$conn->close();
?>

<script>
    function editStudent(id, lastname, firstname, course) {
        const form = document.getElementById("editStudentForm");
        form.id.value = id;
        form.lastname.value = lastname;
        form.firstname.value = firstname;
        form.course.value = course;
        toggleModal("editModal");
    }
</script>
