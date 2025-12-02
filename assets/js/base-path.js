/**
 * Build URL with relative paths based on route depth
 * Gets route depth from meta tag set by PHP
 */
(function() {
    // Get route depth from meta tag
    function getRouteDepth() {
        const meta = document.querySelector('meta[name="route-depth"]');
        if (meta && meta.content) {
            return parseInt(meta.content, 10) || 0;
        }
        return 0;
    }
    
    // Generate relative path prefix based on depth
    function getRelativePrefix() {
        const depth = getRouteDepth();
        if (depth === 0) {
            return './';
        }
        return '../'.repeat(depth);
    }
    
    // Global route depth variable
    window.ROUTE_DEPTH = getRouteDepth();
    
    // Global relative prefix
    window.REL_PREFIX = getRelativePrefix();
    
    // Build URL function - generates relative URLs
    window.buildUrl = function(path) {
        // Remove leading slash from path if present
        path = path ? path.toString().replace(/^\/+/, '') : '';
        return window.REL_PREFIX + path;
    };
    
    // Log for debugging (remove in production)
    console.log('Route depth:', window.ROUTE_DEPTH, 'Prefix:', window.REL_PREFIX);
})();
