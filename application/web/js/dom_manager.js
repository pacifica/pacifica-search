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
         * Gets the input that allows the user to enter a text search query
         * @returns {jQuery}
         */
        getTextSearchInput : function () {
            return $$('#text_search');
        },

        /**
         * Gets the element(s) responsible for showing that the page is in its "loading" state
         * @returns {jQuery}
         */
        getLoadingAnimation : function () {
            return $$('.loading_blocker');
        },

        getTransactionCountContainer : function () {
            return $$('#transaction_count');
        },

        /**
         * Retrieval methods associated with the faceted search filter
         */
        FacetedSearchFilter : {

            /**
             * Make the faceted search sidebar visible
             */
            show : function () {
                this._getContainer().show();
            },

            /**
             * Hide the faceted search sidebar
             */
            hide : function () {
                this._getContainer().hide();
            },

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
             * Gets all of the types for which filter inputs are present on the page
             * @returns {string[]}
             */
            getAllTypes : function() {
                var types = [];
                $('fieldset[data-type]:visible').each(function () {
                    types.push($(this).attr('data-type'));
                });
                return types;
            },

            /**
             * Gets the set of all DOM elements that are containers for the various type-specific filters
             * @returns {jQuery}
             */
            getOptionContainersForAllTypes : function () {
                return $$('fieldset[data-type]');
            },

            /**
             * Gets the DOM element containing the available filter options for a type
             * @param {string} type
             * @returns {jQuery}
             */
            getOptionContainerForType : function (type) {
                return $$(this.getContainerForType(type).find('.option_container'));
            },

            /**
             * Gets the DOM element containing the currently selected filter options for a type
             * @param {string} type
             * @returns {jQuery}
             */
            getCurrentFilterContainerForType : function (type) {
                var container = this.getContainerForType(type);
                return $$(container.find('.currently_selected_options'));
            },

            /**
             * Gets the DOM element that contains a filter for a type
             * @param {string} type
             * @returns {jQuery}
             */
            getContainerForType : function (type) {
                return $$('fieldset[data-type="' + type + '"]');
            },

            /**
             * For any element inside a filter <fieldset> element, returns the type associated with that fieldset
             *
             * @param {jQuery|string} element
             * @returns string
             */
            getTypeByElement : function (element) {
                var fieldset = $$($(element).closest('fieldset'));
                return PacificaSearch.Utilities.assertAttributeExists(fieldset, 'data-type');
            },

            _getContainer : function () {
                return $$('#search_filter');
            },

            /**
             * Methods for the "load more records" subsection of the faceted search filter
             */
            MoreRecords : {
                /**
                 * Gets the DOM element that contains the "load more..." link for a faceted search type
                 * @param {string} type
                 * @returns {jQuery}
                 */
                getContainer : function (type) {
                    return $$(PacificaSearch.DomManager.FacetedSearchFilter.getContainerForType(type).find('.load_more_records_container'));
                },

                /**
                 * Gets the span containing the number of records currently loaded
                 * @param {string} type
                 * @returns {jQuery}
                 */
                getCurrentRecordCountElement : function (type) {
                    return $$(this.getContainer(type).find('.current_record_count'));
                },

                /**
                 * Gets the span containing the total number of records available
                 * @param {string} type
                 * @returns {jQuery}
                 */
                getTotalRecordCountElement : function (type) {
                    return $$(this.getContainer(type).find('.total_record_count'));
                },

                /**
                 * Gets the <a> element that loads another page of records when clicked
                 * @param {string} type
                 * @returns {jQuery}
                 */
                getLink : function (type) {
                    return $$(this.getContainer(type).find('.load_more_records_link'));
                }
            }
        }
    };
})(jQuery, PacificaSearch.Utilities.assertElementExists);
