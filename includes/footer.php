<?php
// includes/footer.php
// No PHP logic here, just HTML for the footer
?>
            </main>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white p-4 text-center mt-auto shadow-inner">
            <div class="container mx-auto">
                <p>&copy; <?php echo date("Y"); ?> ClinicConnect. All rights reserved.</p>
                <p class="text-sm text-gray-400">Designed with care for a healthier you.</p>
            </div>
        </footer>
    </div>
</body>
</html>
<?php
// Close the database connection at the very end of the request
if (isset($conn)) {
    $conn->close();
}
?>
