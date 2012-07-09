<?php
if (!class_exists('wpttuts_roles')) {
    class wpttuts_roles
    {
        /**
         * Determines if all users will have the required permissions
         *
         * @var boolean
         */
        private $all;

        /**
         * An array with the roles which have the required permissions
         *
         * @var array
         */
        private $roles;

        /**
         * An array with the user names which have the required permissions
         *
         * @var array
         */
        private $users;

        /**
         * Creates a new instance of the Roles Class 
         *
         * @param boolean $all
         * @param array $roles
         * @param array $users
         */
        function __construct($all = false, $roles = array(), $users = array())
        {
            // Set the allowed entities
            $this->set_entities($all, $roles, $users);

            // Set the user access permission
            $this->set_permissions();

            // Media Library Filter
            $this->media_filter();
        }


        /**
         * Set the permission entities
         *
         * @param boolean $all
         * @param array $roles
         * @param array $users    
         */
        private function set_entities($all, $roles, $users)
        {
            $this->all = $all;
            $this->roles = $roles;
            $this->users = $users;
        }

        /**
         * Set the Menu and Pages access permissions
         */
        private function set_permissions()
        {
            $this->set_all_permissions();
            if (!$this->all) {
                $this->set_roles_permissions();
                $this->set_users_permissions();
            }
        }

        /**
         * Set the permissions for ALL users
         */
        private function set_all_permissions()
        {
            $users = get_users();
            foreach ($users as $user) {
                $user = new WP_User($user->ID);
                if ($this->all) {
                    $user->add_cap('wptuts_client');
                } else {
                    $user->remove_cap('wptuts_client');
                }
            }
        }

        /**
         * Set the permissions for Roles
         */
        private function set_roles_permissions()
        {
            global $wp_roles;
            $roles = $wp_roles->get_names();
            foreach ($roles as $role_id => $role_name) {
                $role = get_role($role_id);
                $role->remove_cap('wptuts_client');
            }
            if (!empty($this->roles)) {
                foreach ($this->roles as $role_id) {
                    $role = get_role($role_id);
                    $role->add_cap('wptuts_client');
                }
            }
        }

        /**
         * Set the permissions for specific Users
         */
        private function set_users_permissions()
        {
            $users = get_users();
            foreach ($users as $user) {
                $user = new WP_User($user->ID);
                $user->remove_cap('wptuts_client');
            }
            if (!empty($this->users)) {
                foreach ($this->users as $user_id) {
                    $user = new WP_User($user_id);
                    $user->add_cap('wptuts_client');
                }
            }
        }

        /**
         * Restrict Media Access
         */
        private function media_filter()
        {
            // Apply the media filter for currenct AdPress Clients
            $roles = self::filter_roles(array('wptuts_client'), array('upload_files'));
            $users = self::filter_users(array('wptuts_client'), array('upload_files'));
            $this->roles_add_cap($roles, 'upload_files');
            $this->roles_add_cap($roles, 'remove_upload_files');
            $this->users_add_cap($users, 'upload_files');
            $this->users_add_cap($users, 'remove_upload_files');

            // Restrict Media Library access
            add_filter('parse_query', array(&$this, 'restrict_media_library'));

            // For cleaning purposes
            $clean_roles = self::filter_roles(array('remove_upload_files'), array('wptuts_client'));
            $clean_users = self::filter_users(array('remove_upload_files'), array('wptuts_client'));
            $this->roles_remove_cap($clean_roles, 'upload_files');
            $this->roles_remove_cap($clean_roles, 'remove_upload_files');
            $this->users_remove_cap($clean_users, 'upload_files');
            $this->users_remove_cap($clean_users, 'remove_upload_files');
        }

        /**
         * Add a capability to an Array of roles
         *
         * @param $roles
         * @param $cap
         */
        private function roles_add_cap($roles, $cap)
        {
            foreach ($roles as $role) {
                $role = get_role($role);
                $role->add_cap($cap);
            }
        }

        /**
         * Add a capability to an Array of users
         *
         * @param $users
         * @param $cap
         */
        private function users_add_cap($users, $cap)
        {
            foreach ($users as $user) {
                $user = new WP_User($user);
                $user->add_cap($cap);
            }
        }

        /**
         * Remove a capability from an Array of roles
         *
         * @param $roles
         * @param $cap
         */
        private function roles_remove_cap($roles, $cap)
        {
            foreach ($roles as $role) {
                $role = get_role($role);
                $role->remove_cap($cap);
            }
        }

        /**
         * Remove a capability from an Array of users
         *
         * @param $users
         * @param $cap
         */
        private function users_remove_cap($users, $cap)
        {
            foreach ($users as $user) {
                $user = new WP_User($user);
                $user->remove_cap($cap);
            }
        }

        /**
         * Filter all roles of the blog based on capabilities
         *
         * @static
         * @param array $include Array of capabilities to include
         * @param array $exclude Array of capabilities to exclude
         * @return array
         */
        static function filter_roles($include, $exclude)
        {
            $filtered_roles = array();
            global $wp_roles;
            $roles = $wp_roles->get_names();
            foreach ($roles as $role_id => $role_name) {
                $role = get_role($role_id);
                if (self::role_has_caps($role, $include) && !self::role_has_caps($role, $exclude)) {
                    $filtered_roles[] = $role_id;
                }
            }
            return $filtered_roles;
        }

        /**
         * Returns true if a role has the capabilities in the passed array
         *
         * @static
         * @param $role
         * @param $caps
         * @return bool
         */
        static function role_has_caps($role, $caps)
        {
            foreach ($caps as $cap) {
                if (!$role->has_cap($cap)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Filter all users of the blog based on capabilities
         *
         * @static
         * @param array $include Array of capabilities to include
         * @param array $exclude Array of capabilities to exclude
         * @return array
         */
        static function filter_users($include, $exclude)
        {
            $filtered_users = array();
            $users = get_users();
            foreach ($users as $user) {
                $user = new WP_User($user->ID);
                if (self::user_has_caps($user, $include) && !self::user_has_caps($user, $exclude)) {
                    $filtered_users[] = $user->ID;
                }
            }
            return $filtered_users;
        }


        /**
         * Returns true if a user has the capabilities in the passed array
         *
         * @static
         * @param $user
         * @param $caps
         * @return bool
         */
        static function user_has_caps($user, $caps)
        {
            foreach ($caps as $cap) {
                if (!$user->has_cap($cap)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Restrict Media Library access
         *
         * @param $wp_query
         */
        public function restrict_media_library($wp_query)
        {
            if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/upload.php')) {
                if (current_user_can('remove_upload_files')) {
                    global $current_user;
                    $wp_query->set('author', $current_user->ID);
                }
            }
            else if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/media-upload.php')) {
                if (current_user_can('remove_upload_files')) {
                    global $current_user;
                    $wp_query->set('author', $current_user->ID);
                }
            }
        }

    }
}