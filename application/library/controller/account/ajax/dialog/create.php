<?php

    class controller_account_ajax_dialog_create extends controller_account_ajax {

        public function default_action() {

            if (core::auth()->logged_in()) {
                $json = new render_json();
                $json->status = 'close';

                if ($bounce = core::flash()->get('login_bounce')) {
                    $json->bounce = current($bounce);
                    core::flash()->del('login_bounce');
                }

                $json->render();
            } else {
                (new render_dialog('account/create'))
                    ->title('Create Account')
                    ->css([
                        'width' => '600px',
                    ])
                    ->content('body')
                    ->content('foot')
                    ->callback('afterLoad')
                    ->callback('afterShow')
                    ->render();
            }
        }
    }