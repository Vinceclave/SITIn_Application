<div id="registerModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
        <h2 class="text-xl font-semibold mb-4">Register Student</h2>
        <form action="register_student.php" method="POST" class="space-y-4">
            <div>
                <label for="idno" class="block text-sm font-medium text-gray-700">ID Number:</label>
                <input type="number" id="idno" name="idno" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="grid gap-y-4 gap-x-6 grid-cols-1 md:grid-cols-3">
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700">First Name:</label>
                    <input type="text" id="firstname" name="firstname" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label for="middlename" class="block text-sm font-medium text-gray-700">Middle Name:</label>
                    <input type="text" id="middlename" name="middlename" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>
            <div class="grid gap-y-4 gap-x-6 grid-cols-1 md:grid-cols-2">
                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700">Course:</label>
                    <select id="course" name="course" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Select Course</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSIS">BSIS</option>
                        <option value="ACT">ACT</option>
                        <option value="BSED">BSED</option>
                        <option value="BSCJ">BSCJ</option>
                        <option value="BS Custom">BS Custom</option>
                        <option value="BSHM">BSHM</option>
                    </select>
                </div>
                <div>
                    <label for="year_level" class="block text-sm font-medium text-gray-700">Year Level:</label>
                    <select id="year_level" name="year_level" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Select Year Level</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
                <input type="text" id="username" name="username" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-400 text-white rounded-lg mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition">Register</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById("registerModal").classList.remove("hidden");
    }
    function closeModal() {
        document.getElementById("registerModal").classList.add("hidden");
    }
</script>
