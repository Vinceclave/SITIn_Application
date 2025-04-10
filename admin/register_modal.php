<div id="registerModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
        <h2 class="text-xl font-semibold mb-4">Add Student</h2>
        <form action="add_student.php" method="POST" class="space-y-4">
            <div>
                <label for="reg_idno" class="block text-sm font-medium text-gray-700">ID Number:</label>
                <input type="number" id="reg_idno" name="idno" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
                <div>
                    <label for="reg_lastname" class="block text-sm font-medium text-gray-700">Last Name:</label>
                    <input type="text" id="reg_lastname" name="lastname" class="w-full px-4 py-2 border rounded-lg" required>
                </div>
                <div>
                    <label for="reg_firstname" class="block text-sm font-medium text-gray-700">First Name:</label>
                    <input type="text" id="reg_firstname" name="firstname" class="w-full px-4 py-2 border rounded-lg" required>
                </div>
            </div>
            <div>
                <label for="reg_middlename" class="block text-sm font-medium text-gray-700">Middle Name:</label>
                <input type="text" id="reg_middlename" name="middlename" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
                <div>
                    <label for="reg_course" class="block text-sm font-medium text-gray-700">Course:</label>
                    <select id="reg_course" name="course" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select Course</option>
                        <option value="BSIT">Bachelor of Science in Information Technology (BSIT)</option>
                        <option value="BSCS">Bachelor of Science in Computer Science (BSCS)</option>
                        <option value="BSIS">Bachelor of Science in Information Systems (BSIS)</option>
                        <option value="BSBA">Bachelor of Science in Business Administration (BSBA)</option>
                        <option value="ACT">Bachelor of Science in Accountancy (ACT)</option>
                        <option value="BSED">Bachelor of Secondary Education (BSED)</option>
                        <option value="BSCJ">Bachelor of Science in Communication (BSCJ)</option>
                        <option value="BSHM">Bachelor of Science in Hospitality Management (BSHM)</option>
                        <option value="BSPsych">Bachelor of Science in Psychology (BSPsych)</option>
                    </select>
                </div>
                <div>
                    <label for="reg_year_level" class="block text-sm font-medium text-gray-700">Year Level:</label>
                    <select id="reg_year_level" name="year_level" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select year level</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <!-- include any additional year level options you provided earlier -->
                    </select>
                </div>
            </div>
            <div>
                <label for="reg_username" class="block text-sm font-medium text-gray-700">Username:</label>
                <input type="text" id="reg_username" name="username" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <div>
                <label for="reg_password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" id="reg_password" name="password" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('registerModal').classList.add('hidden')" class="px-4 py-2 bg-gray-400 text-white rounded-lg mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 transition">Register Student</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById("registerModal").classList.remove("hidden"); }
    function closeModal() { document.getElementById("registerModal").classList.add("hidden"); }
</script>
