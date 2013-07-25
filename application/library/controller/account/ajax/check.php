<?php

    class controller_account_ajax_check extends controller_account_ajax {

        public function default_action() {
            $json = new render_json;

            switch (core::http()->segment('action')) {
                //check if an email address is valid and available
                case 'email':
                    try {
                        if (user_api::email_available(core::http()->posts())) {
                            $json->status  = 'good';
                            $json->message = "Good";
                        } else {
                            $json->status  = 'error';
                            $json->message = 'Unavailable';
                        }
                    } catch (input_exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->email;
                    }

                    break;
            }

            $json->render();
        }
    }