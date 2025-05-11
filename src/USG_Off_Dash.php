<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');

        if (successAlert) {
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(successAlert);
                alert.close();
            }, 5000);
        }

        if (errorAlert) {
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(errorAlert);
                alert.close();
            }, 5000);
        }

        // Handle URL hash changes and parameters
        function handleNavigation() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                showSection(hash);
                // Scroll to top of the section
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Only show dashboard if no hash is present
                showSection('dashboardSection');
            }
        }

        // Initialize all collapse elements
        var collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
        var collapseList = collapseElementList.map(function (collapseEl) {
            return new bootstrap.Collapse(collapseEl, {
                toggle: false
            });
        });

        // Set initial state of collapse elements based on URL hash
        const hash = window.location.hash.substring(1);
        if (hash) {
            // If we're navigating to a specific section, collapse all menus except the one containing the target
            collapseList.forEach(collapse => {
                const collapseId = collapse._element.id;
                const targetSection = document.querySelector(`[data-section="${hash}"]`);
                if (targetSection && !collapse._element.contains(targetSection)) {
                    collapse.hide();
                }
            });
        } else {
            // If no hash, collapse all menus except dashboard
            collapseList.forEach(collapse => {
                collapse.hide();
            });
        }
    });
</script> 