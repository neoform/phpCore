<?php

    namespace neoform\user\status;

    /**
     * User Status DAO
     */
    class dao extends \neoform\entity\record\dao implements definition {

        const BY_NAME = 'by_name';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'id'   => self::TYPE_INTEGER,
            'name' => self::TYPE_STRING,
        ];

        /**
         * $var array $referenced_entities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referenced_entities = [];

        // READS

        /**
         * Get User Status ids by name
         *
         * @param string $name
         *
         * @return array of User Status ids
         */
        public function by_name($name) {
            return parent::_by_fields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ]
            );
        }

        /**
         * Get User Status id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of User Status ids
         */
        public function by_name_multi(array $name_arr) {
            $keys_arr = [];
            foreach ($name_arr as $k => $name) {
                $keys_arr[$k] = [ 'name' => (string) $name, ];
            }
            return parent::_by_fields_multi(
                self::BY_NAME,
                $keys_arr
            );
        }

        // WRITES

        /**
         * Insert User Status record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple User Status records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return collection
         */
        public function insert_multi(array $infos) {

            // Insert record
            return parent::_insert_multi($infos);
        }

        /**
         * Updates a User Status record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param model $user_status record to be updated
         * @param array $info data to write to the record
         *
         * @return model updated model
         */
        public function update(model $user_status, array $info) {

            // Update record
            return parent::_update($user_status, $info);
        }

        /**
         * Delete a User Status record
         *
         * @param model $user_status record to be deleted
         *
         * @return bool
         */
        public function delete(model $user_status) {

            // Delete record
            return parent::_delete($user_status);
        }

        /**
         * Delete multiple User Status records
         *
         * @param collection $user_status_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(collection $user_status_collection) {

            // Delete records
            return parent::_delete_multi($user_status_collection);
        }
    }
