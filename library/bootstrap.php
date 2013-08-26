<?php

    /*
     * Make a website - standard/simple bootstrap
     */
    class bootstrap {

        protected $router_path;
        protected $get;
        protected $post;
        protected $files;
        protected $server;
        protected $cookies;

        protected $body;

        /**
         * Create bootstrap application
         *
         * @param string $router_path
         * @param array $get
         * @param array $post
         * @param array $files
         * @param array $server
         * @param array $cookies
         */
        public function __construct($router_path, array $get=[], array $post=[], array $files=[], array $server=[], array $cookies=[]) {
            $this->router_path = (string) $router_path;
            $this->get         = $get;
            $this->post        = $post;
            $this->files       = $files;
            $this->server      = $server;
            $this->cookies     = $cookies;
        }

        /**
         * Executes the bootstrap
         *    When buffer output is turned off, all content and headers generated by views are sent straight to the browser
         *
         * @return bootstrap
         */
        public function execute() {
            try {
                core::http(
                    $this->router_path,
                    $this->get,
                    $this->post,
                    $this->files,
                    $this->server,
                    $this->cookies
                )->execute();

            // Error Exception
            } catch (error_exception $e) {
                core::output()->error($e->message(), $e->description());

            } catch (user_status_exception $e) {
                auth_api::logout(core::auth());
                core::output()->redirect();

            // Model Exception
            } catch (entity_exception $e) {
                core::output()->error($e->message(), $e->description());

            // Force user to login exception
            } catch (redirect_login_exception $e) {
                if ($e->url() !== null) {
                    core::http_flash()->set('login_bounce', $e->url());
                }
                if ($e->message() !== null) {
                    core::http_flash()->set('login_message', $e->message());
                }
                if (core::output()->output_type() === output_instance::JSON) {
                    $json = new render_json;
                    $json->status = 'login';
                    $json->render();
                } else {
                    core::output()->redirect('account/login');
                }

            // Force user to enter a captcha exception
            } catch (captcha_exception $e) {
                $json = new render_json;
                $json->status = 'captcha';
                $json->render();
            }

            return $this;
        }

        /**
         * Returns an array of HTTP headers
         *
         * @return array of headers
         */
        public function get_headers() {
            return core::output()->get_headers();
        }

        /**
         * Send headers to output buffer
         *
         * @return bootstrap
         */
        public function send_headers() {
            core::output()->send_headers();
            return $this;
        }

        /**
         * Returns the generated body
         *
         * @return string
         */
        public function body() {
            return (string) core::output()->body();
        }

        /**
         * Returns the generated body
         *
         * @return string
         */
        public function __tostring() {
            return (string) core::output()->body();
        }
    }