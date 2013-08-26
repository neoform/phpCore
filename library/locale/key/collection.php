<?php

    /**
     * Locale Key collection
     */
    class locale_key_collection extends entity_record_collection implements locale_key_definition {

        /**
         * Preload the Locale Key Message models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return locale_key_message_collection
         */
        public function locale_key_message_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'locale_key_message',
                'by_key',
                'locale_key_message_collection',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return locale_collection
         */
        public function locale_collection() {
            return $this->_preload_many_to_many(
                'locale_key_message',
                'by_key',
                'locale',
                'locale_collection'
            );
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return locale_collection
         */
        public function locale_collection1() {
            return $this->_preload_one_to_one(
                'locale',
                'locale',
                'locale'
            );
        }

        /**
         * Preload the Locale Namespace models in this collection
         *
         * @return locale_namespace_collection
         */
        public function locale_namespace_collection() {
            return $this->_preload_one_to_one(
                'locale_namespace',
                'namespace_id',
                'locale_namespace'
            );
        }
    }
