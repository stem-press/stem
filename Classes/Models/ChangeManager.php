<?php

namespace ILab\Stem\Models;

/**
 * Manages changes to a post
 *
 * @package ILab\Stem\Models
 */
class ChangeManager {
    protected $changes = [];

    public function __construct() {
    }

    //region Change Management

    /**
     * Returns if changes are pending
     * @return bool
     */
    public function hasChanges() {
        return (count($this->changes) > 0);
    }

    /**
     * Adds a change for the post
     * @param $field
     * @param $value
     */
    public function addChange($field, $value) {
        $this->changes[] = [
            'type' => 'post',
            'field' => $field,
            'value' => $value
        ];
    }

    /**
     * Sets the post's thumbnail
     * @param $attachmentId
     */
    public function setThumbnail($attachmentId) {
        $this->changes[] = [
            'type' => 'thumbnail',
            'set' => $attachmentId
        ];
    }

    /**
     * Clears the post's thumbnail
     */
    public function clearThumbnail() {
        $this->changes[] = [
            'type' => 'thumbnail',
            'clear' => true
        ];
    }

    /**
     * Adds a category addition
     * @param $category
     */
    public function addCategory($category) {
        $this->changes[] = [
            'type' => 'category',
            'add' => $category
        ];
    }

    /**
     * Adds a category removal
     * @param $category
     */
    public function removeCategory($category) {
        $this->changes[] = [
            'type' => 'category',
            'remove' => $category
        ];
    }

    /**
     * Adds a tag addition
     * @param $tag
     */
    public function addTag($tag) {
        $this->changes[] = [
            'type' => 'tag',
            'add' => $tag
        ];
    }

    /**
     * Adds a tag removal
     * @param $tag
     */
    public function removeTag($tag) {
        $this->changes[] = [
            'type' => 'tag',
            'remove' => $tag
        ];
    }

    /**
     * Updates ACF fields
     * @param $field
     * @param $value
     */
    public function updateField($field, $value) {
        $this->changes[] = [
            'type' => 'acf',
            'action' => 'update',
            'field' => $field,
            'value' => $value
        ];
    }

    /**
     * Deletes an ACF field value
     * @param $field
     */
    public function deleteField($field) {
        $this->changes[] = [
            'type' => 'acf',
            'action' => 'delete',
            'field' => $field
        ];
    }

    /**
     * Updates metadata for a post
     * @param $key
     * @param $value
     */
    public function updateMeta($key, $value) {
        $this->changes[] = [
            'type' => 'meta',
            'action' => 'update',
            'key' => $key,
            'value' => $value
        ];
    }

    /**
     * Deletes metadata for a post
     * @param $key
     */
    public function deleteMeta($key) {
        $this->changes[] = [
            'type' => 'meta',
            'action' => 'delete',
            'key' => $key
        ];
    }

    //endregion

    //region Applying Changes

    /**
     * Creates a new post with the changes in the list
     *
     * @param $postType
     * @return bool|int|\WP_Error  Returns false if no changes present, \WP_Error if there is an error, otherwise the post's ID
     * @throws \Exception
     */
    public function create($postType) {
        if (!$this->hasChanges()) {
            return false;
        }

        $postData = $this->postChanges();
        $postData['post_type'] = $postType;

        $result = wp_insert_post($postData, true);
        if ($result instanceof \WP_Error) {
            $messages = $result->get_error_messages();
            $message = implode("\n", $messages);
            throw new \Exception($message);
        }

        $this->applyOtherChanges($result);
        $this->changes=[];

        return $result;
    }

    /**
     * Updates an existing post with the changes in the list
     *
     * @param $post_id
     * @return bool|int|\WP_Error  Returns false if no changes present, \WP_Error if there is an error, otherwise the post's ID
     * @throws \Exception
     */
    public function update($post_id) {
        if (!$this->hasChanges()) {
            return false;
        }

        $postData = $this->postChanges($post_id);
        $result = wp_update_post($postData, true);
        if ($result instanceof \WP_Error) {
            $messages = $result->get_error_messages();
            $message = implode("\n", $messages);
            throw new \Exception($message);
        }

        $this->applyOtherChanges($post_id);

        $this->changes=[];

        return $result;
    }

    //endregion

    //region Change Calculations

    /**
     * Generates an array of post changes to be supplied to wp_update_post() or wp_insert_post()
     * @param null|int $post_id
     * @return array
     */
    private function postChanges($post_id=null) {
        $result = [];
        if (!empty($post_id)) {
            $result['ID'] = $post_id;
        }

        foreach($this->changes as $change) {
            if ($change['type'] != 'post') {
                continue;
            }

            $result[$change['field']] = $change['value'];
        }

        return $result;
    }

    /**
     * Applies other types of changes like categories, tags, thumbnail, etc.
     * @param $post_id
     */
    private function applyOtherChanges($post_id) {
        $this->applyACFChanges($post_id);
        $this->applyMetadataChanges($post_id);
        $this->applyThumbnailChanges($post_id);
        $this->applyCategoryChanges($post_id);
        $this->applyTagChanges($post_id);
    }

    /**
     * Applies any thumbnail changes
     * @param $post_id
     */
    private function applyThumbnailChanges($post_id) {
        foreach($this->changes as $change) {
            if ($change['type'] != 'thumbnail') {
                continue;
            }

            if (isset($change['set'])) {
                set_post_thumbnail($post_id, $change['set']);
            } else if (isset($change['clear'])) {
                delete_post_thumbnail($post_id);
            }
        }
    }

    /**
     * Applies any ACF field updates
     * @param $post_id
     */
    private function applyACFChanges($post_id) {
        foreach($this->changes as $change) {
            if ($change['type'] != 'acf') {
                continue;
            }

            if ($change['action'] == 'update') {
                update_field($change['field'], $change['value'], $post_id);
            } else if ($change['action'] == 'delete') {
                delete_field($change['field'], $post_id);
            }
        }
    }

    /**
     * Applies any metaadata changes
     * @param $post_id
     */
    private function applyMetadataChanges($post_id) {
        foreach($this->changes as $change) {
            if ($change['type'] != 'meta') {
                continue;
            }

            if ($change['action'] == 'update') {
                update_post_meta($post_id, $change['key'], $change['value']);
            } else if ($change['action'] == 'delete') {
                delete_post_meta($post_id, $change['key']);
            }
        }
    }

    /**
     * Applies the category changes to a given post
     * @param $post_id
     */
    private function applyCategoryChanges($post_id) {
        $cats = wp_get_post_categories($post_id);

        $hasChange = false;
        foreach($this->changes as $change) {
            if ($change['type'] != 'category') {
                continue;
            }

            if (isset($change['add'])) {
                $hasChange = true;
                $cats[] = $change['add'];
            } else if (isset($change['remove'])) {
                $hasChange = true;
                $cats = array_diff($cats, [$change['remove']]);
            }
        }

        if ($hasChange) {
            wp_set_post_categories($post_id, $cats);
        }
    }

    /**
     * Applies the given tag changes to a given post
     * @param $post_id
     */
    private function applyTagChanges($post_id) {
        $tags = wp_get_post_tags($post_id);

        $hasChange = false;
        foreach($this->changes as $change) {
            if ($change['type'] != 'tag') {
                continue;
            }

            if (isset($change['add'])) {
                $hasChange = true;
                $tags[] = $change['add'];
            } else if (isset($change['remove'])) {
                $hasChange = true;
                $tags = array_diff($tags, [$change['remove']]);
            }
        }

        if ($hasChange) {
            wp_set_post_tags($post_id, $tags);
        }

    }

    //endregion
}