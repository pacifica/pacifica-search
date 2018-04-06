(function ($, $$, undefined) {
    "use strict";
    if (undefined !== PacificaSearch.DomManager) {
        throw new Error("PacificaSearch.DomManager is already defined, did you include this file twice?");
    }

    /**
     * @singleton PacificaSearch.DomManager
     *
     * Container object for methods that retrieve elements from the page. In general, retrieval of DOM elements should
     * be done through these methods, to remove other classes' dependency on the DOM
     */
    PacificaSearch.DomManager = {
        /**
         * Retrieval methods associated with the faceted search filter
         */
        FacetedSearchFilter : {

            /**
             * Retrieves the inputs used to toggle filter options on and off for a given filter type
             * @param {string} type Must be one of the PacificaSearch.TYPE.* constants
             * @param {boolean=} selected Only return selected inputs
             * @returns {jQuery}
             */
            getInputsByType : function(type, selected) {
                var inputs = $$('fieldset[data-type="' + type + '"] input');
                if (selected) {
                    inputs = inputs.filter(':checked');
                }
                return inputs;
            },

            /**
             * Retrieves all of the inputs used to toggle filter options on and off
             * @returns {jQuery}
             */
            getAllInputs : function() {
                return $$('fieldset[data-type] input');
            },

            /**
             * Gets all of the types for which filter inputs are present on the page
             * @returns {string[]}
             */
            getAllTypes : function() {
                var types = [];
                $$('fieldset[data-type]').each(function () {
                    types.push($(this).attr('data-type'));
                });
                return types;
            }
        }

    };
})(jQuery, PacificaSearch.Utilities.assertElementExists);
