(function ($, $$, undefined) {
    "use strict";
    if (undefined !== PacificaSearch.FilterManager) {
        throw new Error("PacificaSearch.FilterManager is already defined, did you include this file twice?");
    }

    var DomManager = PacificaSearch.DomManager;
    var Filter = PacificaSearch.Filter;
    var Utilities = PacificaSearch.Utilities;

    /**
     * @singleton PacificaSearch.FilterManager
     *
     * Container object for methods related to Pacifica Search's faceted search filter
     */
    PacificaSearch.FilterManager = {

        lastTextSearch: '',

        /**
         * Gets an instance of Filter representing the currently selected Filter options
         *
         * @returns {PacificaSearch.Filter}
         */
        getFilter : function () {
            var filter = new Filter();

            DomManager.FacetedSearchFilter.getAllTypes().forEach(function (type) {
                var selectedFilterIds = DomManager.FacetedSearchFilter.getInputsByType(type, true).map(function () {
                    return Utilities.assertAttributeExists(this, 'data-id');
                }).get();
                filter.set(type, selectedFilterIds);
            });

            filter.setText(PacificaSearch.DomManager.getTextSearchInput().val());

            return filter;
        },

        /**
         * Adds a single option to the filter sidebar
         * @param {object} instance The object is in the same form as the results returned by the
         * filters/{type}/pages/{page} REST resource
         * @param {string} type
         */
        addInstanceToType : function (instance, type) {
            var inputId = type + '_' + instance.id;
            var input = $('<input type="checkbox">').attr('id', inputId).attr('data-id', instance.id);
            var label = $('<label>').attr('for', inputId).append(input).append(instance.name + " (" + instance.transaction_count + ")");

            DomManager.FacetedSearchFilter.getOptionContainerForType(type).append(label);
        },

        clearExistingFilters : function() {
            // Remove all the existing facets to search on
            // Clear the text search filter
            // return the state of the application to the base state.
            var containers = PacificaSearch.DomManager.FacetedSearchFilter.getOptionContainersForAllTypes();
            containers.each(function(index, container) {
                if($(container).find('.currently_selected_options').children().length !== 0) {

                }
                $(container).find('input').each(function(inputIndex, input) {
                    input.checked = false;
                });
                // console.log(index);
                // console.log($(container).find('input'), index);
            });
        },

        /**
         * Makes the border separating the upper and lower sections of a filter (the selected and unselected options,
         * respectively) visible or hidden depending on whether both sections contain elements.
         * @param {string} type
         */
        updateBorderBetweenContainers : function (type) {
            var filterContainer = DomManager.FacetedSearchFilter.getCurrentFilterContainerForType(type);
            var optionContainer = DomManager.FacetedSearchFilter.getOptionContainerForType(type);

            // Hide the border separating the selected from the unselected items if either is empty
            if(filterContainer.find('input').length === 0 || optionContainer.find('input').length === 0) {
                filterContainer.css('border-bottom', 'none');
            } else {
                filterContainer.css('border-bottom', '2px solid #888');
            }
        },

        /**
         * Updates the options available in the faceted search filter based on what other values are already selected
         *
         * @param {function} callback Invoked after the AJAX call returns. This is implemented as a callback rather than
         *   the standard of returning a Promise because debounced functions can't return anything.
         */
        updateAvailableFilterOptions : function(callback) {
            var filterObj = this.getFilter().toObj();
            if(this.lastTextSearch !== filterObj.text) {
                this.clearExistingFilters();
                this.lastTextSearch = filterObj.text;
            }
            PacificaSearch.Utilities.showLoadingAnimationUntilResolved(
                PacificaSearch.Utilities.postJson(
                    '/filters/pages',
                    filterObj,
                    function (result) {
                        DomManager.FacetedSearchFilter.show();
                        DomManager.getTransactionCountContainer().html("Search matched <strong>" + result.transaction_count + "</strong> transactions. Select options below to further refine your results.");
                        PacificaSearch.FilterManager.injectFilterResultIntoFacetedSearchContainers(result.filter_pages);

                        if (undefined !== callback) {
                            callback();
                        }
                    }
                )
            );
        },

        injectFilterResultIntoFacetedSearchContainers : function (result) {
            var self = this;
            Object.getOwnPropertyNames(result).forEach(function (type) {
                // For results that are empty or only have a single result (which makes filtering on that result
                // meaningless) we don't inject the contents or make the filter section visible
                var typeContainer = DomManager.FacetedSearchFilter.getContainerForType(type);

                // If only a single filter item or no items are available for selection, and the filter has no selected
                // values, then there's no reason to show it.
                var areOptionsSelected = DomManager.FacetedSearchFilter.getCurrentFilterContainerForType(type).find(':checked').length > 0;
                if (result[type].total_hits < 1 && !areOptionsSelected) {
                    typeContainer.hide();
                } else {
                    if(result[type].total_hits === 1) {
                        // debugger;
                    }
                    typeContainer.show();

                    // Remove all of the options currently being shown
                    DomManager.FacetedSearchFilter.getOptionContainerForType(type).empty();

                    // debugger;
                    var instances = result[type].instances.sort(function(a,b) {return b.transaction_count - a.transaction_count});
                    instances.forEach(function (instance) {
                        self.addInstanceToType(instance, type);
                    });
                    self.updateMoreRecordsContainer(type, instances.length, result[type].total_hits);
                }
            });
        },

        /**
         * Show or hide the "load more records" section that is rendered at the bottom of the search container for facet
         * types that only partially loaded due to having more records than our page size. Also update the numbers shown
         * in that section.
         *
         * @param {string} type
         * @param {int} currentRecordCount
         * @param {int} totalRecordCount
         */
        updateMoreRecordsContainer : function (type, currentRecordCount, totalRecordCount) {
            Utilities.assertInteger(currentRecordCount, totalRecordCount);

            DomManager.FacetedSearchFilter.MoreRecords.getCurrentRecordCountElement(type).text(currentRecordCount);
            DomManager.FacetedSearchFilter.MoreRecords.getTotalRecordCountElement(type).text(totalRecordCount);

            var moreRecordsContainer = DomManager.FacetedSearchFilter.MoreRecords.getContainer(type);
            if (totalRecordCount > currentRecordCount) {
                moreRecordsContainer.show();
            } else {
                moreRecordsContainer.hide();
            }
        },

        /**
         * Load another page of results
         * @param {string} type
         */
        loadMoreRecords : function (type) {
            var self = this;
            var link = DomManager.FacetedSearchFilter.MoreRecords.getLink(type);
            var currentPage = Utilities.assertAttributeExists(link, 'data-page');
            var newPage = parseInt(currentPage) + 1;
            var filterObj = this.getFilter().toObj();

            PacificaSearch.Utilities.showLoadingAnimationUntilResolved(
                PacificaSearch.Utilities.postJson(
                    '/filters/' + type + '/pages/' + newPage,
                    filterObj,
                    function (result) {
                        result.instances.forEach(function (instance) {
                            self.addInstanceToType(instance, type);
                        });
                        self.updateMoreRecordsContainer(
                            type,
                            parseInt(DomManager.FacetedSearchFilter.MoreRecords.getCurrentRecordCountElement(type).text()) + result.instances.length,
                            result.total_hits
                        );

                        link.attr('data-page', newPage);
                    }
                )
            );
        }
    };
})(jQuery, PacificaSearch.Utilities.assertElementExists);
