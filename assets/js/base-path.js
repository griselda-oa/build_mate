/**
 * Base Path Helper - Dynamic URL Generation
 * Provides window.buildUrl() function for client-side URL generation
 */

(function() {
    'use strict';
    
    // Get base path from meta tag or detect from current location
    function getBasePath() {
        // Try to get from meta tag first
        const metaTag = document.querySelector('meta[name="base-path"]');
        if (metaTag && metaTag.content) {
            return metaTag.content;
        }
        
        // Fallback: detect from current location
        const path = window.location.pathname;
        if (path.includes('/build_mate/')) {
            return '/build_mate/';
        }
        
        // Default fallback
        return '/build_mate/';
    }
    
    // Cache base path
    const basePath = getBasePath();
    
    /**
     * Build URL with base path
     * @param {string} path - The path to append to base path
     * @returns {string} Full URL with base path
     */
    window.buildUrl = function(path) {
        // Remove leading slash from path if present
        path = path ? path.toString().replace(/^\/+/, '') : '';
        
        // Ensure base path ends with /
        const base = basePath.endsWith('/') ? basePath : basePath + '/';
        
        // If path is empty, just return base path
        if (!path) {
            return base;
        }
        
        // Return combined path
        return base + path;
    };
    
    // Also expose basePath for direct access if needed
    window.basePath = basePath;
    
    console.log('Base path initialized:', basePath);
})();

