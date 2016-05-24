'use strict';

define(
    ['jquery', 'datepicker'],
    function ($, Datepicker) {

        return {
            /** @var {jQuery} */
            $conditionTime: null,
            /** @var {jQuery} */
            $exportedSince: null,

            /**
             * @param {string} container
             */
            init: function (container) {
                var $container = $(container);

                this.$conditionTime = $container.find('select');
                this.$exportedSince = $container.find('input');

                this.displayInput();
                this.listenTo();
                
                Datepicker.init($container);
            },

            /**
             * Listen to javascript events
             */
            listenTo: function () {
                this.$conditionTime.on('change', this.displayInput.bind(this));
            },

            /**
             * Display or hide the datepicker depending condition time value
             */
            displayInput: function () {
                if ('since_date' == this.$conditionTime.val()) {
                    this.$exportedSince.show();
                } else {
                    this.$exportedSince.hide();
                }
            }
        };
    }
);
