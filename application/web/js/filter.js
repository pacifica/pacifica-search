(function () {
    "use strict";

    /**
     * Represents the collection of filter options that the user has entered in the UI.
     */
    PacificaSearch.Filter = function () {
        this._filter = {
            /** @value {string} */
            text : null,

            /** @value {Number[]} */
            instrument_type : [],

            /** @value {Number[]} */
            instrument : [],

            /** @value {Number[]} */
            institution : [],

            /** @value {Number[]} */
            user : [],

            /** @value {Number[]} */
            proposal : []
        };
    };

    /**
     * A validation wrapper that confirms the requested property is defined before setting its value
     *
     * @param {string} property Must be one of this class's properties
     * @param {number[]} ids
     */
    PacificaSearch.Filter.prototype.set = function (property, ids) {
        if (undefined === this._filter[property]) {
            throw new Error("'" + property + "' is not a valid filter field");
        }
        this._filter[property] = ids;
    };

    /**
     * @param {string} text
     */
    PacificaSearch.Filter.prototype.setText = function (text) {
        this._filter.text = text;
    };

    /**
     * Gets an object representation of the Filter, suitable for submitting to the REST API
     * @returns {Object}
     */
    PacificaSearch.Filter.prototype.toObj = function () {
        return this._filter;
    }
})();