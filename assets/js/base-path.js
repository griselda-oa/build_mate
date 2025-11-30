/**
 * Base Path Helper for Build Mate
 * Dynamically detects the base path (/build_mate or /~username/build_mate)
 */
(function() {
    'use strict';
    
    // Cache the base path
    let basePath = null;
    
    /**
     * Get the base path for the application
     * @returns {string} Base path (e.g., '/build_mate' or '/~username/build_mate')
     */
    window.getBasePath = function() {
        if (basePath !== null) {
            return basePath;
        }
        
        // Try to get from a meta tag first (set by PHP)
        const metaTag = document.querySelector('meta[name="base-path"]');
        if (metaTag) {
            basePath = metaTag.getAttribute('content') || '/build_mate';
            return basePath;
        }
        
        // Fallback: detect from current URL
        const pathname = window.location.pathname;
        
        // Check for server path: /~username/build_mate
        const serverMatch = pathname.match(/^(\/~[^/]+\/build_mate)/);
        if (serverMatch) {
            basePath = serverMatch[1];
            return basePath;
        }
        
        // Check for localhost path: /build_mate
        if (pathname.indexOf('/build_mate') === 0) {
            basePath = '/build_mate';
            return basePath;
        }
        
        // Default fallback
        basePath = '/build_mate';
        return basePath;
    };
    
    /**
     * Build a URL with the base path
     * @param {string} path - Path to append (should start with /)
     * @returns {string} Full URL with base path
     */
    window.buildUrl = function(path) {
        const base = getBasePath();
        // Remove leading slash from path if present, then add it
        const cleanPath = path.startsWith('/') ? path : '/' + path;
        // Remove trailing slash from base if present
        const cleanBase = base.endsWith('/') ? base.slice(0, -1) : base;
        return cleanBase + cleanPath;
    };
})();

