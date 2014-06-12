<?php

    namespace neoform;

    class controller_admin_acl_role extends controller_admin {

        public function default_action() {

            $page  = (int) http::instance()->parameter('page');
            $sort  = (string) http::instance()->parameter('sort') ?: 'id';
            $order = (string) http::instance()->parameter('order') ?: 'asc';

            $valid_sorts = ['id', 'name', ];

            if ($sort && ! in_array($sort, $valid_sorts)) {
                self::error(500, 'Invalid sort', "You cannot sort by \"{$sort}\"");
                return;
            }

            $per_page = 20;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render\view;

            $view->meta_title = 'ACL Roles';

            $roles = new acl\role\collection(
                entity::dao('acl\role')->limit(
                    [
                        $sort => $order === 'asc' ? entity\record\dao::SORT_ASC : entity\record\dao::SORT_DESC
                    ],
                    ($page - 1) * $per_page,
                    $per_page
                )
            );
            $roles->acl_resource_collection();
            $roles->acl_group_collection();

            $view->roles    = $roles;

            $view->page     = $page;
            $view->sorter   = new render\sort($sort, $order, $valid_sorts);
            $view->total    = entity::dao('acl\role')->count();
            $view->per_page = $per_page;

            $view->render('admin/acl/role');
        }
    }