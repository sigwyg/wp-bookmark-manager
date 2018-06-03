<?php

/**
 * A helper class for insert or update link data.
 */
class WBM_Import_Link_Helper {
    const CFS_PREFIX = 'cfs_';
    const SCF_PREFIX = 'scf_';

    /**
     * @var $link WP_Link object
     */
    private $link;

    /**
     * @var $error WP_Error object
     */
    private $error;

    /**
     * Add an error or append additional message to this object.
     *
     * @param string|int $code Error code.
     * @param string $message Error message.
     * @param mixed $data Optional. Error data.
     */
    public function addError($code, $message, $data = '') {
        if (!$this->isError()) {
            $e = new WP_Error();
            $this->error = $e;
        }
        $this->error->add($code, $message, $data);
    }

    /**
     * Get the error of this object
     *
     * @return (WP_Error)
     */
    public function getError() {
        if (!$this->isError()) {
            $e = new WP_Error();
            return $e;
        }
        return $this->error;
    }

    /**
     * Check the object has some Errors.
     *
     * @return (bool)
     */
    public function isError() {
        return is_wp_error($this->error);
    }

    /**
     * Set WP_Link object
     *
     * @param (int) $link_id Link ID
     */
    protected function setLink($link_id) {
        $link = get_bookmark($link_id);
        if (is_object($link)) {
            $this->link = $link;
        } else {
            $this->addError('link_id_not_found', __('Provided Link ID not found.', 'wp-bookmark-manager'));
        }
    }

    /**
     * Get WP_Link object
     *
     * @return (WP_Link|null)
     */
    public function getLink() {
        return $this->link;
    }

    /**
     * Get object by link id.
     *
     * @param (int) $link_id Link ID
     * @return (WBM_Import_Link_Helper)
     */
    public static function getByID($link_id) {
        $object = new WBM_Import_Link_Helper();
        $object->setLink($link_id);
        return $object;
    }

    /**
     * Add a link
     *
     * @param (array) $data An associative array of the link data
     * @return (WBM_Import_Link_Helper)
     */
    public static function add($data) {
        $object = new WBM_Import_Link_Helper();
        $link_id = wp_insert_link($data, true);
        if (is_wp_error($link_id)) {
            $object->addError($link_id->get_error_code(), $link_id->get_error_message());
        } else {
            $object->setLink($link_id);
        }
        return $object;
    }

    /**
     * Update link
     *
     * @param (array) $data An associative array of the link data
     */
    public function update($data) {
        $link = $this->getLink();
        if ($link instanceof WP_Link) {
            $data['link_id'] = $link->link_id;
        }
        print_r($data->link_category);
        $link_id = wp_update_link($data, true);
        if (is_wp_error($link_id)) {
            $this->addError($link_id->get_error_code(), $link_id->get_error_message());
        } else {
            $this->setLink($link_id);
        }
    }

    /**
     * A wrapper of wp_safe_remote_get
     *
     * @param (string) $url
     * @param (array) $args
     * @return (string) file path
     */
    public function remoteGet($url, $args = array()) {
        global $wp_filesystem;
        if (!is_object($wp_filesystem)) {
            WP_Filesystem();
        }

        if ($url && is_object($wp_filesystem)) {
            $response = wp_safe_remote_get($url, $args);
            if (!is_wp_error($response) && $response['response']['code'] === 200) {
                $destination = wp_upload_dir();
                $filename = basename($url);
                $filepath = $destination['path'] . '/' . wp_unique_filename($destination['path'], $filename);

                $body = wp_remote_retrieve_body($response);

                if ( $body && $wp_filesystem->put_contents($filepath , $body, FS_CHMOD_FILE) ) {
                    return $filepath;
                } else {
                    $this->addError('remote_get_failed', __('Could not get remote file.', 'wp-bookmark-manager'));
                }
            } elseif (is_wp_error($response)) {
                $this->addError($response->get_error_code(), $response->get_error_message());
            }
        }

        return '';
    }

    /**
     * Unset WP_Link object
     */
    public function __destruct()
    {
        unset($this->link);
    }
}
