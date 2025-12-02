/**
 * Build URL with dynamic base path
 * Gets base path from meta tag set by PHP
 */
(function() {
    // Get base path from meta tag
    function getBasePath() {
        const meta = document.querySelector('meta[name="base-path"]');
        if (meta && meta.content) {
            let path = meta.content;
            // Ensure it ends with /
            return path.endsWith('/') ? path : path + '/';
        }
        return '/';
    }
    
    // Global base path variable
    window.BASE_PATH = getBasePath();
    
    // Build URL function - use this instead of hardcoded paths
    window.buildUrl = function(path) {
        // Remove leading slash from path if present
        path = path ? path.toString().replace(/^\/+/, '') : '';
        return window.BASE_PATH + path;
    };
    
    // Log for debugging (remove in production)
    console.log('Base path set to:', window.BASE_PATH);
})();


