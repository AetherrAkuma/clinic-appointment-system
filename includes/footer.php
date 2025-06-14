<?php
// includes/footer.php
// This file assumes $conn is available if db_connection.php was included in header.php
if (isset($conn)) {
    $conn->close();
}
?>

            </main>
        </div> <!-- This closes the flex-1 div that wraps sidebar and main content -->

        <!-- IMPORTANT: The footer must be inside the #app div to stick to the bottom -->
        <footer class="bg-gray-800 text-white py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center text-sm">
                <p>&copy; <?php echo date("Y"); ?> Clinic Management System. All rights reserved.</p>
                <div class="flex flex-wrap justify-center md:justify-end gap-x-4 gap-y-1 mt-3 md:mt-0">
                    <a href="/clinic-management/index.php" class="hover:text-purple-300 transition duration-300 ease-in-out">Home</a>
                    <a href="/clinic-management/about.php" class="hover:text-purple-300 transition duration-300 ease-in-out">About Us</a>
                </div>
            </div>
        </footer>
    </div> <!-- This now correctly closes the div id="app" -->

    <script>
        // Universal sidebar toggle for smaller screens
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const mainContent = document.getElementById('mainContent');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('translate-x-0');
                // Optional: Add overlay or shift main content for better mobile UX
                mainContent.classList.toggle('blur-sm'); // Example: blur main content
            });
        }

        if (closeSidebar) {
            closeSidebar.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                mainContent.classList.remove('blur-sm');
            });
        }

        // Close sidebar if clicked outside (simple overlay effect)
        mainContent.addEventListener('click', (event) => {
            if (!sidebar.classList.contains('-translate-x-full') && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                mainContent.classList.remove('blur-sm');
            }
        });

        // Optional: Close sidebar when a navigation link is clicked on mobile
        sidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) { // Assuming md breakpoint is 768px
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    mainContent.classList.remove('blur-sm');
                }
            });
        });
    </script>
</body>
</html>
