<?php

    namespace neoform\web;

    class lib {

        /**
         * Get the contents of a URL
         *
         * @param string      $url
         * @param array       $post
         * @param string|null $bind_to_ip
         *
         * @return string
         * @throws exception
         */
        public static function wget($url, array $post=null, $bind_to_ip=null) {

            if (! self::valid_url($url)) {
                throw new exception('Invalid URL');
            }

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_USERAGENT, \neoform\config::instance()['web']['user_agent']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);

            if ($bind_to_ip) {
                curl_setopt($curl, CURLOPT_INTERFACE, $bind_to_ip);
            }

            if ($post) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
            }

            $contents = curl_exec($curl);
            $info     = curl_getinfo($curl);

            if ($info['http_code'] === 200) {
                return $contents;
            }

            throw new exception("Server returned HTTP/{$info['http_code']}", (int) $info['http_code']);
        }

        /**
         * Get just the header of a URL
         *
         * @param string      $url
         * @param array       $post
         * @param string|null $bind_to_ip
         *
         * @return array
         * @throws exception
         */
        public static function wget_info($url, array $post=null, $bind_to_ip=null) {

            if (! self::valid_url($url)) {
                throw new exception('Invalid URL');
            }

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_USERAGENT, \neoform\config::instance()['web']['user_agent']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);

            curl_setopt($curl, CURLOPT_HEADER, 1); // get the header
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

            if ($bind_to_ip) {
                curl_setopt($curl, CURLOPT_INTERFACE, $bind_to_ip);
            }

            if ($post) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
            }

            curl_exec($curl);
            $info = curl_getinfo($curl);

            if ($info['http_code'] === 200) {
                return $info;
            }

            throw new exception("Server returned HTTP/{$info['http_code']}", (int) $info['http_code']);
        }

        /**
         * Get the header and body of a URL
         *
         * @param string      $url
         * @param array       $post
         * @param string|null $bind_to_ip
         *
         * @return array
         * @throws exception
         */
        public static function wget_full($url, array $post=null, $bind_to_ip=null) {

            if (! self::valid_url($url)) {
                throw new exception('Invalid URL');
            }

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_USERAGENT, \neoform\config::instance()['web']['user_agent']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);

            if ($bind_to_ip) {
                curl_setopt($curl, CURLOPT_INTERFACE, $bind_to_ip);
            }

            if ($post) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
            }

            $body = curl_exec($curl);
            $head = curl_getinfo($curl);

            if ($head['http_code'] === 200) {
                return [
                    'head' => $head,
                    'body' => $body,
                ];
            }

            throw new exception("Server returned HTTP/{$head['http_code']}", (int) $head['http_code']);
        }

        /**
         * Tests if a URL is valid to be fetched or not
         *
         * @param string $url
         *
         * @return bool
         */
        public static function valid_url($url) {
            if (! $info = parse_url($url)) {
                return false;
            }

            if (empty($info['host']) || $info['host'] === 'localhost') {
                return false;
            }

            if (empty($info['scheme']) || ! ($info['scheme'] === 'http' || $info['scheme'] === 'https')) {
                return false;
            }

            return true;
        }
    }
