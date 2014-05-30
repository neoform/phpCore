<?php

    namespace neoform\sql;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'sql';
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // SQL charset (encoding)
                'encoding' => 'utf8',

                // the connection name that is use when all else fails to [required]
                'default_pool_read'  => null,
                'default_pool_write' => null,

                // Server pools
                'pools' => [],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws neoform\config\exception
         */
        public function validate() {

            if (empty($this->config['default_pool_read'])) {
                throw new neoform\config\exception('"default_pool_read" must be set');
            }

            if (empty($this->config['default_pool_write'])) {
                throw new neoform\config\exception('"default_pool_write" must be set');
            }

            if (empty($this->config['pools']) || ! is_array($this->config['pools']) || ! count($this->config['pools'])) {
                throw new neoform\config\exception('"pools" must contain at least one server');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }