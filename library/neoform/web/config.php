<?php

    namespace neoform\web;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'web';
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // User agent
                'user_agent' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws neoform\config\exception
         */
        public function validate() {

            if (empty($this->config['user_agent'])) {
                throw new neoform\config\exception('"user_agent" must be set');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }