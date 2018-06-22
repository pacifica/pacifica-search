/**
 * namespace.js - Defines namespace objects and global constants for the Pacifica Search project
 */
(function (undefined) {
    if (undefined !== window.PacificaSearch) {
        throw new Error('PacificaSearch is already defined, did you include this file twice?');
    }

    window.PacificaSearch = {
        /**
         * How many items to load initially into each faceted search type, and how many to load on each click of the
         * "more" link.
         */
        FACETED_SEARCH_ITEMS_PER_PAGE : 3
    };
})();