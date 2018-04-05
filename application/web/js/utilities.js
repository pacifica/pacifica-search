(function ($, undefined) {
    "use strict";
    if (undefined !== PacificaSearch.Utilities) {
        throw new Error("PacificaSearch.Utilities is already defined, did you include this file twice?");
    }

    PacificaSearch.Utilities = {

        /**
         * Generate an error if a jQuery selector fails to find a match, otherwise return its result
         * @param selector
         * @returns {string}
         */
        assertElementExists: function (selector) {
            var result = $(selector);
            if (!result.length) {
                throw new Error("Selector '" + selector + "' did not match any element");
            }
            return result;
        },

        /**
         * Generate an error if the an attribute is not defined for an element (or the element doesn't exist), otherwise
         * returns that attribute's value.
         *
         * @param {jQuery|string} selector
         * @param {string} attribute
         * @return {string}
         */
        assertAttributeExists: function (selector, attribute) {
            var attrValue = this.assertElementExists(selector).attr(attribute);

            if (undefined === attrValue) {
                throw new Error("Attribute '" + attribute + "' not found");
            }

            return attrValue;
        },

        /**
         * Replace all instances of <find> with <replace> in <string>
         * @param {string} str
         * @param {string} find
         * @param {string} replace
         * @return {string}
         */
        replaceAll: function (str, find, replace) {
            // This solution is a bit slower than using a RegExp but doesn't require escaping special characters.
            // We can replace it with a RegExp-based solution if there is ever a need for this method to work faster.
            return str.split(find).join(replace);
        },

        /**
         * Returns a Promise that resolves when all of the passed Promises have resolved.
         * @param {promise|promise[]} promises
         */
        allResolved: function (promises) {
            if (!Array.isArray(promises)) {
                promises = [ promises ];
            }

            var deferred = $.Deferred();
            var numUnresolved = promises.length;

            if (numUnresolved === 0) {
                deferred.resolve();
            }

            promises.forEach(function (promise) {
                promise.then(function () {
                    numUnresolved--;
                    if (numUnresolved === 0) {
                        deferred.resolve();
                    }
                });
            });

            return deferred.promise();
        },

        /**
         * Renders a page-blocking loading animation that is removed when the passed promise(s) resolve(s)
         * @param {promise|promise[]} promises
         * @returns promise Promise resolves when the loading animation is no longer visible
         */
        showLoadingAnimationUntilResolved : function (promises) {
            var loadingAnimation = PacificaSearch.DomManager.getLoadingAnimation();

            // The "active" class triggers a transition, which is why we combine showing and adding a class instead of
            // just using "show()"
            loadingAnimation.show().addClass('active');

            return this.allResolved(promises).then(function () {
                loadingAnimation.removeClass('active', function () {
                    loadingAnimation.hide();
                });
            });
        }
    };

    Object.keys(PacificaSearch.Utilities).forEach(function (key) {
        if (typeof(PacificaSearch.Utilities[key]) === 'function') {
            PacificaSearch.Utilities[key] = PacificaSearch.Utilities[key].bind(PacificaSearch.Utilities);
        }
    });
})(jQuery);
