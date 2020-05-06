<?php

class Device extends AvsPostType {

const POST_TYPE = 'avs_device';
const FIELD_GROUP_ID = 'acf_avs_device';
const FIELD_GROUP_TITLE = 'device Rules';

const IMAGE_SCOPE_LOGO = 'skin-logo';

const ACF_FIELD_LOGO = 'logo';
const ACF_FIELD_RULES = 'rules';
const ACF_FIELD_DIRECTIVE = 'directive';

const CSS_DIRECTIVE_FORMAT = "%s{%s} ";

const CONTENT_TYPE_SKIN = 'skin';
const CONTENT_ID_DEL = "|";
const CONTENT_ID_SEP = ":";

public function init() {

    parent::init();

    // add constraint on this post type
    add_filter('avs_title_unique_constraint', function ($types) { $types[] = self::POST_TYPE; return $types; } );

    // add trash contraint on this post type
    // add_filter("can_trash_{$this->getPostTypeName()}_or_restore", array($this, 'isTrashable'), 1, 2);

    // Add validator
    // add_filter('acf/validate_value/key=' . $this->getFieldKey(self::ACF_FIELD_DIRECTIVE), array($this, 'validateDirective'), 99, 4);

    // Extends AvsSkin as Content for Rendition
    // add_filter('avs_image_format_content_type_for_rendition', array($this, 'getContentType'), 10, 3);
    // add_filter('avs_content_id', array($this, 'getContentId'), 10, 4);
    // add_filter('avs_get_content_type_by_id', array($this, 'getContentTypeById'), 10, 3);
    // add_filter('avs_get_content_by_id', array($this, 'getContent'), 10, 4);

}

/**
 * @param array $messages
 * @return array
 */
public function updatedMessages($messages) {
    $messages[self::POST_TYPE] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __( 'Test updated.' ),
        2 => __( 'Test Custom field updated.' ),
        3 => __( 'Test Custom field deleted.' ),
        4 => __( 'Test Skin updated.' ),
        /* translators: %s: date and time of the revision */
        5 => isset($_GET['revision']) ? sprintf( __( 'Skin restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
        6 => __( 'Test Skin published.' ),
        7 => __( 'Test Skin saved.' ),
        8 => __( 'Test Skin submitted.' ),
        9 => sprintf( __( 'Test Skin scheduled for: %s.'), '???' ),
        10 => __( 'Test Skin draft updated.' )
    );

    return $messages;
}

protected function getAllowedRowAction(){
    return array('edit', 'edit_as_new_draft');
}

public function getPostTypeName() {
    return self::POST_TYPE;
}

public function getCapability() {
    return self::POST_TYPE;
}

public function getSlug() {
    return self::POST_TYPE;
}

protected function __initPostType() {

    $args = array(
        "menu_position" => 30,
        "labels" => $this->getLabels('Skin'),
        "description" => "Skin",
        "public" => false,
        "show_ui" => true,
        "has_archive" => false,
        // "show_in_menu" => 'edit.php?post_type=' . DeviceMenu::POST_TYPE,
        "show_in_menu" => true,
        "exclude_from_search" => false,
        "capability_type" => $this -> getCapability(),
        "capabilities" => array(
            "create_posts" => $this->getCreateCapability(),
        ),
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array(
            "slug" => $this -> getSlug(),
            "with_front" => true
        ),
        "query_var" => true,
        "supports" => array("title"),
        "taxonomies" => array()
    );

    register_post_type($this -> getPostTypeName());
}

protected function __installFields() {

}


}

?>