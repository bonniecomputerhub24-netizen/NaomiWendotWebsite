    </div>
    <!-- End Main Container -->
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 px-6 md:ml-64">
        <div class="flex flex-col md:flex-row items-center justify-between gap-2 text-sm text-gray-600">
            <p class="font-inter">
                © <?php echo date('Y'); ?> <span class="font-semibold text-plum">Naomi Wendot</span> — Writer's Admin Panel
            </p>
            <p class="text-xs text-gray-500">
                Developed & Maintained by <a href="https://bonniecomputerhub.co.ke" target="_blank" class="text-gold hover:underline font-semibold">Bonnie Computer Hub</a>
            </p>
        </div>
    </footer>
    
    <!-- Mobile Menu Toggle Script -->
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleSidebar);
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }
        
        // Close sidebar on navigation (mobile)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    toggleSidebar();
                }
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-auto-dismiss');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
