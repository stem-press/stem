<?php

namespace Stem\Models;
use Stem\Core\Context;

/**
 * Class Attachment.
 *
 * Represents a media attachment
 */
class Attachment extends Post {
    protected static $postType = 'attachment';

    /** @var null|string The attachment's filename */
    protected $filename = null;

    /** @var null|string The attachment's url */
    protected $url = null;

    /** @var null|string The link to the attachment's page */
    protected $link = null;

    /** @var null|string The attachment's alt text */
    protected $alt = null;

    /** @var null|string The attachment's description */
    protected $description = null;

    /** @var null|string The attachment's caption */
    protected $caption = null;

    /** @var null|string The attachment's mime type */
    protected $mime = null;

    /** @var null|string The attachment's type */
    protected $type = null;

    /** @var null|string The attachment's subtype */
    protected $subtype = null;

    /** @var null|string The URL for the icon representing the attachment's mime type */
    protected $icon = null;

    /** @var null|array Extra information about the attachment */
    protected $attachmentInfo = null;

    /** @var null|array Attachment metadata */
    protected $attachmentMeta = null;

    public function __construct(Context $context, \WP_Post $post = null) {
        parent::__construct($context, $post);

        $this->parseType();
    }

    //region Properties

    /**
     * The filename of the attachment
     * @return null|string
     */
    public function filename() {
        if ($this->filename != null) {
            return $this->filename;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->filename = wp_basename(get_attached_file($this->id));
        return $this->filename;
    }

    /**
     * The URL for the attachment's original image
     * @return null|string
     */
    public function url() {
        if ($this->url != null) {
            return $this->url;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->url = wp_get_attachment_url($this->id);
        return $this->url;
    }

    /**
     * Link to the attachment's page
     * @return null|string
     */
    public function link() {
        if ($this->link != null) {
            return $this->link;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->link = get_attachment_link($this->id);
        return $this->link;
    }

    /**
     * Attachment's alt text
     * @return null|string
     */
    public function alt() {
        if ($this->alt != null) {
            return $this->alt;
        }

        $this->alt = $this->meta('_wp_attachment_image_alt', null);
        return $this->alt;
    }

    /**
     * Sets the alt text for the attachment
     * @param string $alt
     */
    public function setAlt($alt) {
        $this->alt = $alt;
        $this->updateMeta('_wp_attachment_image_alt', $alt);
    }

    /**
     * Description of the attachment
     *
     * @return null|string
     */
    public function description() {
        if ($this->description != null) {
            return $this->description;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->description = $this->post->post_content;
        return $this->description;
    }

    /**
     * Sets the description for the attachment
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
        $this->changes->addChange('post_content', $description);
    }

    /**
     * The caption for the attachment
     * @return null|string
     */
    public function caption() {
        if ($this->caption != null) {
            return $this->caption;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->caption = $this->post->post_excerpt;
        return $this->caption;
    }

    /**
     * Sets the caption for the attachment
     * @param string $caption
     */
    public function setCaption($caption) {
        $this->caption = $caption;
        $this->changes->addChange('post_excerpt', $caption);
    }

    /**
     * The attachment's primary type
     * @return null|string
     */
    public function type() {
        return $this->type;
    }

    /**
     * The attachment's sub type
     * @return null|string
     */
    public function subType() {
        return $this->subtype;
    }

    /**
     * The attachment's mime type
     * @return null|string
     */
    public function mime() {
        if ($this->mime != null) {
            return $this->mime;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->mime = $this->post->post_mime_type;
        return $this->mime;
    }

    /**
     * Sets the attachment's mime type
     * @param string $mime
     */
    public function setMime($mime) {
        $this->icon = null;
        $this->mime = $mime;
        $this->changes->addChange('post_mime_type', $mime);

        $this->parseType();
    }

    /**
     * URL for the icon representing the attachment's mime type
     * @return null|string
     */
    public function icon() {
        if ($this->icon != null) {
            return $this->icon;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->icon = wp_mime_type_icon($this->mime ?: $this->id);
        return $this->icon;
    }

    /**
     * Parses the type/subtype from the mime type
     */
    private function parseType() {
        $this->type = null;
        $this->subtype = null;

        $mime = $this->mime();
        if (empty($mime)) {
            return;
        }

        if ( false !== strpos($mime, '/' ) )
            list($this->type, $this->subtype) = explode('/', $mime);
        else
            list($this->type, $this->subtype) = array($mime, '');
    }

    //endregion

    //region Image Tags

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

    //endregion

    //region Attachment Metadata

    /**
     * Returns the post's attachment metadata
     *
     * @param null|string $keyPath The key path into the attachment metadata
     * @param null|mixed $defaultValue The default value to return if the key doesn't exist
     * @return array|mixed|null
     */
    public function attachmentMeta($keyPath=null, $defaultValue=null) {
        $this->insureAttachmentMeta();

        if (empty($keyPath)) {
            return $this->attachmentMeta;
        } else {
            return arrayPath($this->attachmentMeta, $keyPath, $defaultValue);
        }
    }

    /**
     * Updates metadata value
     * @param $keyPath
     * @param $value
     */
    public function updateAttachmentMeta($keyPath, $value) {
        $this->insureAttachmentMeta();

        updateArrayPath($this->attachmentMeta, $keyPath, $value);

        $this->changes->updateAttachmentMeta($this->attachmentMeta);
    }

    /**
     * Deletes a metadata item
     * @param $keyPath
     */
    public function deleteAttachmentMeta($keyPath) {
        $this->insureAttachmentMeta();

        unsetArrayPath($this->attachmentMeta, $keyPath);

        $this->changes->updateAttachmentMeta($this->attachmentMeta);
    }

    /**
     * Insures metadata is loaded for this post
     */
    private function insureAttachmentMeta() {
        if ($this->attachmentMeta == null) {
            if (empty($this->id)) {
                $this->attachmentMeta = [];
            } else {
                $this->attachmentMeta = wp_get_attachment_metadata($this->id);
            }
        }
    }

    //endregion

    //region Attachment Info

    /**
     * Loads the attachment's extra info
     * @param bool $forceReload
     * @return array|null
     */
    public function attachmentInfo($forceReload = false) {
        if (!$forceReload && ($this->attachmentInfo != null)) {
            return $this->attachmentInfo;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->attachmentInfo = wp_prepare_attachment_for_js($this->id);
        return $this->attachmentInfo;
    }

    //endregion

    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();

        unset($json['author']);
        unset($json['content']);
        unset($json['excerpt']);
        unset($json['categories']);
        unset($json['thumbnail']);

        $json['caption'] = $this->caption();
        $json['description'] = $this->description();

        if ($this->attachmentInfo()) {
            $json['sizes'] = $this->attachmentInfo['sizes'];
            $json['width'] = $this->attachmentInfo['width'];
            $json['height'] = $this->attachmentInfo['height'];
            $json['orientation'] = $this->attachmentInfo['orientation'];
        }

        return $json;
    }
}
