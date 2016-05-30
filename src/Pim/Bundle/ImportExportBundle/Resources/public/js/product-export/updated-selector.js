'use strict';
/**
 * Dynamic locales selector in the product export builder form
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['jquery', 'datepicker'],
    function ($, Datepicker) {
        return {
            $conditionTime: null,
            $exportedSince: null,

            /**
             * @param {string} container
             */
            init: function (container) {
                var $container = $(container);

                this.$conditionTime = $container.find('select');
                this.$exportedSince = $container.find('input');

                this._displayDateElement();

                this.$conditionTime.on('change', this._displayDateElement.bind(this));
                Datepicker.init($container);
            },

            /**
             * Display or hide the datepicker depending condition time value
             *
             * @private
             */
            _displayDateElement: function () {
                var $validationTooltip = this._findValidationTooltip();
                if ('since_date' == this.$conditionTime.val()) {
                    this.$exportedSince.show().prop('disabled', false);
                    $validationTooltip.show();
                } else {
                    this.$exportedSince.hide().prop('disabled', true);
                    $validationTooltip.hide();
                }
            },

            /**
             * Find the validation tooltip element
             *
             * @returns {jQuery}
             *
             * @private
             */
            _findValidationTooltip: function() {
                var $iconContainer = this.$exportedSince.next();
                if (!$iconContainer.length) {
                    return $();
                }

                var $validationTooltip = $iconContainer.find('.validation-tooltip');
                if (!$validationTooltip.length) {
                    return $();
                }

                return $validationTooltip;
            }
        };
    }
);
