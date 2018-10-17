<?php

namespace ILab\Stem\Models;

/**
 * Class Attachment.
 *
 * Represents a media attachment
 */
class Attachment extends Post {
    protected $postType = 'attachment';
    protected $attachmentInfo = null;

    /**
     * Returns an img tag using the requested size template.
     *
     * @param string $size The size template to use, specify 'original' for original size.
     * @param bool $attr Any additional attributes to add to the tag
     * @param bool $stripDimensions Strip dimensions from the tag
     *
     * @return string|null
     */
    public function img($size = 'original', $attr = false, $stripDimensions = false) {
        if (empty($this->id)) {
            return null;
        }

        if (! $attr) {
            $img = wp_get_attachment_image($this->id, $size);
        } else {
            $img = wp_get_attachment_image($this->id, $size, false, $attr);
        }

        if ($stripDimensions) {
            $img = preg_replace('#width="[0-9]+"\s*#', '', $img);
            $img = preg_replace('#height="[0-9]+"\s*#', '', $img);
        }

        return $img;
    }

    /**
     * Generates an amp-img tag.
     * @param string $size
     * @param bool $responsive
     * @param array|null $attr Any additional attributes to add to the tag
     * @return string
     */
    public function ampImg($size = 'thumbnail', $responsive = true, $attr = null) {
        if (empty($this->id)) {
            return null;
        }

        if (empty($attr)) {
            $attr = [];
        }

        if ($responsive) {
            $attr['layout'] = 'responsive';
        }

        $img = wp_get_attachment_image($this->id, $size, false, $attr);

        $img = str_replace('<img', '<amp-img', $img);

        return $img;
    }

    /**
     * Returns the attachment url
     * @return string The attachment's URL
     */
    public function url() {
        if (empty($this->id)) {
            return null;
        }

       return wp_get_attachment_url($this->id);
    }

    /**
     * Returns the url for an image using the requested size template.
     *
     * @param string $size The size template to use.
     *
     * @return string|null
     */
    public function src($size = 'original') {
        if (empty($this->id)) {
            return null;
        }

        $result = wp_get_attachment_image_src($this->id, $size);
        if ($result && is_array($result) && (count($result) > 0)) {
            return $result[0];
        }

        return null;
    }

    /**
     * Gets the attachment URL for this attachment.
     * @return null|string
     */
    public function attachmentUrl() {
        if (empty($this->id)) {
            return null;
        }

        return wp_get_attachment_url($this->id);
    }

    /**
     * Loads attachment info.
     * @return bool
     */
    protected function loadAttachmentInfo() {
        if (empty($this->id)) {
            return false;
        }

        if ($this->attachmentInfo == null) {
            $this->attachmentInfo = wp_prepare_attachment_for_js($this->post);
        }

        return true;
    }

    /**
     * Returns the caption for the attachment, if any.
     * @return string|null
     */
    public function caption() {
        $this->loadAttachmentInfo();

        if ($this->attachmentInfo == null) {
            return null;
        }

        return (isset($this->attachmentInfo['caption'])) ? $this->attachmentInfo['caption'] : null;
    }

    /**
     * Returns the description of the attachment, if any.
     * @return string|null
     */
    public function description()
    {
        $this->loadAttachmentInfo();

        if ($this->attachmentInfo == null) {
            return null;
        }

        return (isset($this->attachmentInfo['description'])) ? $this->attachmentInfo['description'] : null;
    }

    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        unset($json['author']);
        unset($json['content']);
        unset($json['excerpt']);
        unset($json['categories']);
        unset($json['thumbnail']);

        if ($this->loadAttachmentInfo()) {
            $json['sizes'] = $this->attachmentInfo['sizes'];
            $json['width'] = $this->attachmentInfo['width'];
            $json['height'] = $this->attachmentInfo['height'];
            $json['orientation'] = $this->attachmentInfo['orientation'];
            $json['caption'] = $this->caption();
            $json['description'] = $this->description();
        }

        return $json;
    }
}
