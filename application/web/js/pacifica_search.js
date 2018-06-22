(function ($, $$) {
    "use strict";

    var FilterManager = PacificaSearch.FilterManager;
    var DomMgr = PacificaSearch.DomManager;

    $(function () {
        $$('#search_filter  ')
            .on('change', 'input', function () {
                var selectedOption = $(this).closest('label');
                var selectedOptionType = DomMgr.FacetedSearchFilter.getTypeByElement(selectedOption);
                var filterContainer = DomMgr.FacetedSearchFilter.getCurrentFilterContainerForType(selectedOptionType);

                // Move the option into the "selected options" container, unless it's already there (which can
                // happen if you quickly click an option off then on again)
                if (this.checked && !filterContainer.find(selectedOption).length) {
                    selectedOption.detach();
                    filterContainer.append(selectedOption.clone());
                }

                FilterManager.updateBorderBetweenContainers(selectedOptionType);

                FilterManager.updateAvailableFilterOptions(function () {
                    filterContainer.find('input').not(':checked').closest('label').detach();
                    FilterManager.updateBorderBetweenContainers(selectedOptionType);
                });

                if($('#search_filter').find('input[type="checkbox"]:checked').length > 0){
                    $('.results_instructions').hide();
                }else{
                    $('#results_filetree').hide();
                    $('.results_instructions').show();
                }
            });
    });
})(jQuery, PacificaSearch.Utilities.assertElementExists);
