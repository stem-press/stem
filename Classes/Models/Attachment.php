<?php
namespace ILab\Stem\Models;

/**
 * Class Attachment
 *
 * Represents a media attachment
 *
 * @package ILab\Stem\Models
 */
class Attachment extends Post {
    protected $parentPost=null;
    protected $attachmentInfo=null;

    /**
     * Returns an img tag using the requested size template.
     *
     * @param string $size The size template to use, specify 'original' for original size.
     * @param bool $attr Any additional attributes to add to the tag
     * @param bool $stripDimensions Strip dimensions from the tag
     *
     * @return string
     */
    public function img($size='original',$attr=false, $stripDimensions=false) {
        if (!$attr)
            $img=wp_get_attachment_image($this->post->ID,$size);
        else
            $img=wp_get_attachment_image($this->post->ID,$size,false,$attr);

        if ($stripDimensions){
            $img=preg_replace('#width="[0-9]+"\s*#','',$img);
            $img=preg_replace('#height="[0-9]+"\s*#','',$img);
        }

        return $img;
    }

    /**
     * Generates an amp-img tag.
     * @param string $size
     * @param array|null $attr Any additional attributes to add to the tag
     */
    public function ampImg($size='thumbnail', $responsive = true, $attr=false) {
        if (!$attr)
            $attr=[];

        if ($responsive)
            $attr['layout']='responsive';

        $img=wp_get_attachment_image($this->post->ID,$size,false,$attr);

        $img=str_replace('<img','<amp-img', $img);

        return $img;
    }

    /**
     * Returns the url for an image using the requested size template.
     *
     * @param string $size The size template to use.
     *
     * @return string|null
     */
    public function src($size='thumbnail') {
        $result=wp_get_attachment_image_src($this->post->ID,$size);
        if ($result && is_array($result) && (count($result)>0))
            return $result[0];

        return null;
    }

    /**
     * Gets the attachment URL for this attachment
     * @return false|string
     */
    public function attachmentUrl() {
        return wp_get_attachment_url($this->id);
    }

    /**
     * Returns the parent post this attachment is attached to, if any.
     * @return Attachment|Page|Post|null
     */
    public function parentPost() {
        if ($this->parentPost)
            return $this->parentPost;

        $parent_id=get_post_field('post_parent',$this->id);
        if ($parent_id && !empty($parent_id))
            $this->parentPost=$this->context->modelForPost(\WP_Post::get_instance($parent_id));

        return $this->parentPost;
    }

    /**
     * Loads attachment info.
     */
    protected function loadAttachmentInfo() {
        if (!$this->attachmentInfo)
            $this->attachmentInfo=wp_prepare_attachment_for_js($this->post);
    }

    /**
     * Returns the caption for the attachment, if any.
     * @return mixed
     */
    public function caption(){
        $this->loadAttachmentInfo();

        return (isset($this->attachmentInfo['caption'])) ? $this->attachmentInfo['caption'] : null;
    }

    /**
     * Returns the description of the attachment, if any.
     * @return mixed
     */
    public function description(){
        $this->loadAttachmentInfo();

        return (isset($this->attachmentInfo['description'])) ? $this->attachmentInfo['description'] : null;
    }

    public function jsonSerialize() {
        $json=parent::jsonSerialize();
        unset($json['author']);
        unset($json['content']);
        unset($json['excerpt']);
        unset($json['categories']);
        unset($json['thumbnail']);
        $this->loadAttachmentInfo();
        $json['sizes']=$this->attachmentInfo['sizes'];
        $json['width']=$this->attachmentInfo['width'];
        $json['height']=$this->attachmentInfo['height'];
        $json['orientation']=$this->attachmentInfo['orientation'];
        $json['caption']=$this->caption();
        $json['description']=$this->description();
        return $json;
    }

}