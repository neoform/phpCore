<?php

    namespace neoform;

    class controller_account_ajax_insert extends controller_account_ajax {

        public function default_action() {

            $json = new render\json;

            //if already logged in
            if (core::auth()->logged_in()) {
                $json->status = 'good';
                if ($bounce = core::http_flash()->get('login_bounce')) {
                    $json->bounce = current($bounce);
                    core::http_flash()->del('login_bounce');
                }
            } else {
                try {
                    // Create user
                    $user = user\api::insert(core::http()->posts());
                    $site = new site\model(core::config()['core']['site_id']);

                    // Create user-site link
                    entity::dao('user\site')->insert([
                        'user_id' => $user->id,
                        'site_id' => $site->id,
                    ]);

                    // Activate session
                    auth\lib::activate_session($user, (bool) core::http()->post('remember'));

                    if ($bounce = core::http_flash()->get('login_bounce')) {
                        $json->bounce = current($bounce);
                        core::http_flash()->del('login_bounce');
                    }

                    $json->status = 'good';
                } catch (input\exception $e) {
                    $json->errors = $e->errors();
                    $json->message = $e->message();
                }
            }

            $json->render();
        }
    }