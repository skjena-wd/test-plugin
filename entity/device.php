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