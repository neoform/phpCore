<?php

    /**
     * Postgres record_dao driver
     */
    class record_driver_pgsql implements record_driver {

        /**
         * Parse the table name into a properly escaped table string
         *
         * @param string $table
         *
         * @return string
         */
        protected static function table($table) {
            if (strpos($table, '.') !== false) {
                $table = explode('.', $table);
                return "$table[0]\".\"$table[1]";
            } else {
                return $table;
            }
        }

        public static function by_pk($self, $pk) {

            $info = core::sql('slave')->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"" . $self::PRIMARY_KEY . "\" = ?
            ");

            $info->bindValue(1, $pk, sql_pdo::pdo_binding($self::castings(), $self::PRIMARY_KEY));
            $info->execute();

            if (! ($info = $info->fetch())) {
                $exception = $self::ENTITY_NAME . '_exception';
                throw new $exception('That ' . $self::NAME . ' doesn\'t exist');
            }

            sql_pdo::unbinary($info);

            return $info;
        }

        public static function by_pks($self, array $pks) {

            $infos_rs = core::sql('slave')->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"" . $self::PRIMARY_KEY . "\" IN (" . join(',', array_fill(0, count($pks), '?')) . ")
            ");

            $pdo_binding = sql_pdo::pdo_binding($self::castings(), $self::PRIMARY_KEY);
            foreach (array_values($pks) as $i => $pk) {
                $infos_rs->bindValue($i + 1, $pk, $pdo_binding);
            }
            $infos_rs->execute();

            $infos = [];
            foreach ($infos_rs->fetchAll() as $info) {
                $k = array_search($info[$self::PRIMARY_KEY], $pks);
                if ($k !== false) {
                    $infos[$k] = $info;
                }
            }

            sql_pdo::unbinary($infos);

            return $infos;
        }

        public static function all($self, $pk, array $keys=null) {
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
                    if (is_array($v) && count($v)) {
                        foreach ($v as $arr_v) {
                            $vals[] = $arr_v;
                        }
                        $where[] = "\"$k\" IN(" . join(',', array_fill(0, count($v), '?')) . ")";
                    } else {
                        if ($v === null) {
                            $where[] = "\"$k\" IS NULL";
                        } else {
                            $vals[$k] = $v;
                            $where[]  = "\"$k\" = ?";
                        }
                    }
                }
            }

            $info = core::sql('slave')->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
                ORDER BY \"$pk\" ASC
            ");

            sql_pdo::bind_by_casting(
                $info,
                $self::castings(),
                $vals
            );

            $info->execute();

            $infos = [];
            foreach ($info->fetchAll() as $info) {
                $infos[$info[$pk]] = $info;
            }

            sql_pdo::unbinary($infos);

            return $infos;
        }

        public static function by_fields($self, array $keys, $pk) {

            $where = [];
            $vals  = [];

            if (count($keys)) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"$k\" IS NULL";
                    } else {
                        $vals[$k] = $v;
                        $where[]  = "\"$k\" = ?";
                    }
                }
            }

            $rs = core::sql('slave')->prepare("
                SELECT \"$pk\"
                FROM \"" . self::table($self::TABLE) . "\"
                " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
            ");

            sql_pdo::bind_by_casting(
                $rs,
                $self::castings(),
                $vals
            );

            $rs->execute();

            $rs = $rs->fetchAll();
            $pks = [];
            foreach ($rs as $row) {
                $pks[] = $row[$pk];
            }

            sql_pdo::unbinary($pks);

            return $pks;
        }

        public static function by_fields_multi($self, array $keys_arr, $pk) {
            $key_fields     = array_keys(current($keys_arr));
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $where          = [];

            foreach ($keys_arr as $k => $keys) {
                $w = [];
                $reverse_lookup[join(':', $keys)] = $k;
                $return[$k] = [];
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $w[] = "\"$k\" IS NULL";
                    } else {
                        $vals[$k] = $v;
                        $w[]      = "\"$k\" = ?";
                    }
                }
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = core::sql('slave')->prepare("
                SELECT
                    \"$pk\",
                    CONCAT(" . join(", ':', ", $key_fields) . ") \"__cache_key__\"
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(' OR ', $where) . "
            ");

            sql_pdo::bind_by_casting(
                $rs,
                $self::castings(),
                $vals
            );

            $rs->execute();

            $rows = $rs->fetchAll();
            foreach ($rows as $row) {
                $return[
                $reverse_lookup[$row['__cache_key__']]
                ][] = $row[$pk];
            }

            sql_pdo::unbinary($return);

            return $return;
        }

        public static function by_fields_select($self, array $select_fields, array $keys) {
            $where = [];
            $vals  = [];

            if (count($keys)) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"$k\" IS NULL";
                    } else {
                        $vals[$k] = $v;
                        $where[]  = "\"$k\" = ?";
                    }
                }
            }

            $rs = core::sql('slave')->prepare("
                SELECT " . join(',', $select_fields) . "
                FROM \"" . self::table($self::TABLE) . "\"
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
            ");

            sql_pdo::bind_by_casting(
                $rs,
                $self::castings(),
                $vals
            );

            $rs->execute();

            $rs = $rs->fetchAll();
            $return = [];
            if (count($select_fields) === 1) {
                $field = current($select_fields);
                foreach ($rs as $row) {
                    $return[] = $row[$field];
                }
            } else {
                $return = $rs;
            }

            sql_pdo::unbinary($return);

            return $return;
        }

        public static function insert($self, array $info, $autoincrement, $replace) {
            $insert_fields = [];
            foreach (array_keys($info) as $key) {
                $insert_fields[] = "\"$key\"";
            }

            $insert = core::sql('master')->prepare("
                INSERT INTO
                    \"" . self::table($self::TABLE) . "\"
                    ( " . join(', ', $insert_fields) . " )
                    VALUES
                    ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                    " . ($autoincrement ? "RETURNING \"". $self::PRIMARY_KEY . "\"" : '') . "
            ");

            sql_pdo::bind_by_casting(
                $insert,
                $self::castings(),
                $info
            );

            $insert->execute();

            if ($autoincrement) {
                $info[$self::PRIMARY_KEY] = $insert->fetch()[$self::PRIMARY_KEY];
            }

            return $info;
        }

        public static function inserts($self, array $infos, $keys_match, $autoincrement, $replace) {

            if ($keys_match) {
                $insert_fields = [];

                foreach (array_keys(current($infos)) as $k) {
                    $insert_fields[] = "\"$k\"";
                }

                // If the table is auto increment, we cannot lump all inserts into one query
                // since we need the returned IDs for cache-busting and to return a model
                if ($autoincrement) {
                    $sql = core::sql('master');
                    $sql->beginTransaction();
                    $pk = $self::PRIMARY_KEY;

                    $insert = $sql->prepare("
                        INSERT INTO
                            \"" . self::table($self::TABLE) . "\"
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                            RETURNING \"$pk\"
                    ");

                    foreach ($infos as $info) {

                        sql_pdo::bind_by_casting(
                            $insert,
                            $self::castings(),
                            $info
                        );

                        $insert->execute();

                        if ($autoincrement) {
                            $info[$self::PRIMARY_KEY] = $insert->fetch()[$pk];
                        }
                    }

                    $sql->commit();
                } else {
                    // this might explode if $keys_match was a lie
                    $insert_vals = new splFixedArray(count($insert_fields) * count($infos));
                    foreach ($infos as $info) {
                        foreach ($info as $v) {
                            $insert_vals[] = $v;
                        }
                    }

                    $inserts = core::sql('master')->prepare("
                        INSERT INTO
                            \"" . self::table($self::TABLE) . "\"
                            ( " . implode(', ', $insert_fields) . " )
                            VALUES
                            " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insert_fields), '?')) . ')')) . "
                    ");

                    sql_pdo::bind_by_casting(
                        $inserts,
                        $self::castings(),
                        $insert_vals
                    );

                    $inserts->execute();
                }
            } else {
                $sql   = core::sql('master');
                $table = self::table($self::TABLE);

                $sql->beginTransaction();

                foreach ($infos as $info) {
                    $insert_fields = [];

                    foreach (array_keys($info) as $key) {
                        $insert_fields[] = "\"$key\"";
                    }

                    $insert = $sql->prepare("
                        INSERT INTO
                            \"$table\"
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($info), '?')) . " )
                            " . ($autoincrement ? "RETURNING \"". $self::PRIMARY_KEY . "\"" : '') . "
                    ");

                    sql_pdo::bind_by_casting(
                        $insert,
                        $self::castings(),
                        $info
                    );

                    $insert->execute();

                    if ($autoincrement) {
                        $info[$self::PRIMARY_KEY] = $insert->fetch()[$self::PRIMARY_KEY];
                    }
                }

                $sql->commit();
            }

            return $infos;
        }

        public static function update($self, $pk, record_model $model, array $info) {
            $sql = core::sql('master');

            $update_fields = [];
            foreach (array_keys($info) as $key) {
                $update_fields[] = "\"$key\" = :$key";
            }
            $update = $sql->prepare("
                UPDATE \"" . self::table($self::TABLE) . "\"
                SET " . implode(", \n", $update_fields) . "
                WHERE \"$pk\" = :$pk
            ");

            $info[$pk] = $model->$pk;

            sql_pdo::bind_by_casting(
                $update,
                $self::castings(),
                $info,
                true
            );

            $update->execute();
        }

        public static function delete($self, $pk, record_model $model) {
            $delete = core::sql('master')->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"$pk\" = ?
            ");
            $delete->bindValue(1, $model->$pk, sql_pdo::pdo_binding($self::castings(), $self::PRIMARY_KEY));
            $delete->execute();
        }

        public static function deletes($self, $pk, record_collection $collection) {
            $pks = $collection->field($pk);
            $delete = core::sql('master')->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"$pk\" IN (" . join(',', array_fill(0, count($collection), '?')) . ")
            ");

            $pdo_binding = sql_pdo::pdo_binding($self::castings(), $self::PRIMARY_KEY);
            $i = 0;
            foreach ($pks as $pk) {
                $delete->bindValue($i++, $pk, $pdo_binding);
            }
            $delete->execute();
        }
    }