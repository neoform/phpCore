<?php

    class controller_admin_locale_namespaces extends controller_admin {

        public function default_action() {

            $view = new render_view;

            $view->meta_title = 'Locale Namespaces';
            $view->namespaces = new locale_namespace_collection(null, locale_namespace_dao::all());

            $view->render('admin/locale/namespaces');
        }
    }