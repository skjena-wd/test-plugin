<?php

class Device extends AvsPostType {

const POST_TYPE = 'avs_skin';
const FIELD_GROUP_ID = 'acf_avs_skin';
const FIELD_GROUP_TITLE = 'Skin Rules';

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
    add_filter("can_trash_{$this->getPostTypeName()}_or_restore", array($this, 'isTrashable'), 1, 2);

    // Add validator
    add_filter('acf/validate_value/key=' . $this->getFieldKey(self::ACF_FIELD_DIRECTIVE), array($this, 'validateDirective'), 99, 4);

    // Extends AvsSkin as Content for Rendition
    add_filter('avs_image_format_content_type_for_rendition', array($this, 'getContentType'), 10, 3);
    add_filter('avs_content_id', array($this, 'getContentId'), 10, 4);
    add_filter('avs_get_content_type_by_id', array($this, 'getContentTypeById'), 10, 3);
    add_filter('avs_get_content_by_id', array($this, 'getContent'), 10, 4);

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
        "menu_position" => 20,
        "labels" => $this->getLabels('Skin'),
        "description" => "Skin",
        "public" => false,
        "show_ui" => true,
        "has_archive" => false,
        "show_in_menu" => 'edit.php?post_type=' . AvsMenu::POST_TYPE,
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

    register_post_type($this -> getPostTypeName(), $args);
}

protected function __installFields() {

    if(function_exists("register_field_group")) {

        // Prepare filter lists
        $repeater_subfields = $this->getDirectivesAsAcf();

        // Prepare field for dynamic collection
        $baseFields = array(

            array (
                'key' => $this->getFieldKey(self::ACF_FIELD_LOGO),
                'label' => __('Logo', UIBuilder_Naming::getSlug()),
                'name' => self::ACF_FIELD_LOGO,
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                'mime_types' => 'jpeg, jpg, png, gif',
                'type' => 'image',
                'required' => 0,

                // Integration with Image Formats Plugin
                'scope' => self::IMAGE_SCOPE_LOGO,
            ),

            array (
                'key' => $this->getFieldKey(self::ACF_FIELD_RULES),
                'label' => __('Rules', UIBuilder_Naming::getSlug()),
                'name' => self::ACF_FIELD_RULES,
                'type' => 'repeater',
                'sub_fields' => $repeater_subfields,
                'layout' => 'row',
                'button_label' => __("Add Rule", UIBuilder_Naming::getSlug()),
            ),
        );

        $post_type_fields = array (
            'id' => self::FIELD_GROUP_ID,
            'title' => self::FIELD_GROUP_TITLE,
            'fields' => $baseFields,
            'location' => array (
                array (
                    array (
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => self::getPostTypeName(),
                        'order_no' => 0,
                        'group_no' => 0,
                    ),
                ),
            ),
            'options' => array (
                'position' => 'acf_after_title',
                'layout' => 'default',
                'hide_on_screen' => array (
                    0 => 'permalink',
                    1 => 'the_content',
                    2 => 'excerpt',
                    3 => 'custom_fields',
                    4 => 'discussion',
                    5 => 'revisions',
                    6 => 'slug',
                    7 => 'author',
                    8 => 'format',
                    9 => 'featured_image',
                    10 => 'tags',
                    11 => 'send-trackbacks',
                ),
            ),
            'menu_order' => 0,
        );

        $post_type_fields = apply_filters("avs_post_type_fields", $post_type_fields, $this->getPostTypeName());
        register_field_group($post_type_fields);

    }
}

/**
 * check if post is trashable. @see can_trash_{$post->post_type}_or_restore Hook.
 * A Collection is trashable if
 * - no Page link the Collection
 *
 * @param $post WP_Post
 * @return array
 */
public function isTrashable($trashable, $post) {

    $menuWithSkin = array();

    $menu_args = array(
        'post_status' => 'publish',
        'post_type' => AvsMenu::POST_TYPE,
        'numberposts' => -1,
        'suppress_filters' => false
    );

    // Load all published pages
    $helper = $this -> __getHelper();
    $menus = get_posts($menu_args);

    // cycle in every menu
    foreach ($menus as $menu) {

        // get Skin form menu
        $skin = $helper -> getSkinFromMenu($menu);

        // check if the deleting collection is used
        if ($skin && $skin -> getPost() -> ID == $post -> ID) {

            // add the title of the menu using the collection to an array of users
            $menuWithSkin[] = avs_xss_sanitize($menu->post_title);
        }

    }

    // if the collection is used stop the trashing and show where it's used
    if ($menuWithSkin) {

        // log the untrashing
        do_action('untrash_post', $post);

        $menuWithSkin = implode(', ', $menuWithSkin);
        return array(
            'can_trash' => false,
            'reason' => __('Skin used in Menu: '. $menuWithSkin , UIBuilder_Naming::getSlug())
        );
    }

    // nope. can trash.
    return $trashable;

}

/**
 * @return array
 */
public function getDirectivesAsAcf(): array {

    // Init
    $fields = array();
    $directive_key = 'field_' . $this->getPostTypeName() . '_' . self::ACF_FIELD_DIRECTIVE;
    $directive_choices = array();

    // Check configuration
    $settings = $this->__getSettings()->getDirectives();
    if($settings) {

        // Create directive list
        foreach ($settings as $s) {

            // Add directive
            $directive_choices[$s[AvsSkinSettings::getOptionName(AvsSkinSettings::OPTION_DIRECTIVE_FIELD_RULE)]] = avs_xss_sanitize(
                __($s[AvsSkinSettings::getOptionName(AvsSkinSettings::OPTION_DIRECTIVE_FIELD_LABEL)], UIBuilder_Naming::getSlug())
            );

        }

        // Sort on label
        asort($directive_choices);

        // Select directive field
        $directiveField = array(
            'key' => $directive_key,
            'label' => __('Directive', UIBuilder_Naming::getSlug()),
            'name' => self::ACF_FIELD_DIRECTIVE,
            'type' => 'select',
            'ui' => 1,
            'choices' => $directive_choices,
            'default_value' => false,
            'allow_null' => false,
            'required' => 1
        );
        $fields[] = $directiveField;

        // Add all css property fields
        $cssFields = apply_filters(
            "avs_skin_css_property_acf_fields",
            array(),
            $directiveField,
            $this
        );
        $fields = array_merge($fields, $cssFields);

    }

    // Add filter on all skin fields
    $fields = apply_filters(
        "avs_skin_directive_acf_fields",
        $fields
    );

    return $fields;
}

/**
 * @return AvsSkinSettings
 */
protected function __getSettings() {
    return new AvsSkinSettings();
}

/**
 * Create ACF field key
 * @param $dcfield array
 * @return string
 */
public function getAcfFieldKeyParamName($skinfield) {
    $rule = sanitize_html_class($skinfield[AvsSkinSettings::getOptionName(AvsSkinSettings::OPTION_DIRECTIVE_FIELD_RULE)]);
    return 'field_' . $this->getPostTypeName() . '_' . $rule;
}

/**
 * Validate Directive - must be unique
 * @param $valid mixed
 * @param $value mixed
 * @param $field array
 * @param $input string
 * @return mixed
 */
public function validateDirective($valid, $value, $field, $input) {

    // fail early if value is already invalid
    if(!$valid) {
        return $valid;
    }

    // Check unique value for rule
    // only if rule has value
    if(!$value) {
        return $valid;
    }

    // Get post data
    $directive_times = 0;
    $acf = $_POST['acf'];

    // Get directives sent
    $rules = $acf['field_' . $this->getPostTypeName() . '_' . self::ACF_FIELD_RULES];
    if(!$rules || count($rules) <= 1) {

        // No directive or just 1 -> I cannot check if unique value
        return $valid;
    }

    // Let's count how many time this rule is declared
    foreach ($rules as $rule) {

        $directive = $rule['field_' . $this->getPostTypeName() . '_' . self::ACF_FIELD_DIRECTIVE];
        if(strcmp($directive, $value) == 0) {

            // Find rule -> count it
            $directive_times++;

        }

    }

    // If the rule is declared more then 1 time
    if($directive_times > 1) {

        // Not unique
        return __("Directive must be unique", UIBuilder_Naming::getSlug());

    }

    return $valid;
}

/**
 * Return AvsSkin as CSS String
 * @return string
 */
public function getAbsoluteLogo() {

    // Init
    $logoUrl = '';
    $logo = $this -> getLogo();
    if($logo) {

        // Init helper
        $contentUtil = Avs_Content_Util::getInstance();
        $posterUtil = Avs_Poster_Util::getInstance();

        // Prepare data for Naming Convention
        $post_id = $this -> getPost() -> ID;
        $contentId = $this -> getContentId(
            $post_id,
            $post_id,
            self::CONTENT_TYPE_SKIN,
            $contentUtil
        );

        // Use the default naming convention
        $logoUrl = $posterUtil -> getPosterUrl(
            $contentUtil -> getContent($contentId),
            self::IMAGE_SCOPE_LOGO,
            get_attached_file($logo['ID'])
        );

    }

    return $logoUrl;
}

/**
 * Return AvsSkin as CSS String
 * @return string
 */
public function toCSS() {

    $css = "";
    $helper = $this->__getHelper();

    // If AvsSkin has valid post
    if($this -> getPost()) {

        // Get all directives
        $rules = $this -> getRules();

        // For each rule
        if($rules) {
            foreach ($rules as $rule) {

                // Init directive
                $directive = $rule[self::ACF_FIELD_DIRECTIVE];
                unset($rule[self::ACF_FIELD_DIRECTIVE]);

                // If valid directive
                if($directive) {

                    // Get properties enabled for directive
                    $enabled_properties = $this->__getHelper() -> getEnabledPropertiesForDirective($directive);

                    // If some properties enabled
                    if($enabled_properties) {

                        // Init directive css string
                        $css_directive = '';

                        // For each CSS property
                        if($rule) {
                            foreach ($rule as $property => $value) {

                                // If valid property
                                if(in_array($property, $enabled_properties)) {

                                    // Convert into CSS String
                                    $css_directive .= apply_filters(
                                        "avs_skin_css_property_" . $property,
                                        '',
                                        $value,
                                        $property,
                                        $this,
                                        get_field_object($this -> getFieldKey($property))
                                    );

                                }
                            }
                        }

                        // If css created
                        if($css_directive) {

                            // Store new directive
                            $css .= sprintf(
                                self::CSS_DIRECTIVE_FORMAT,
                                $directive,
                                $css_directive
                            );

                        }

                    }

                }
            }

        }
    }

    // Remove whitespace
    $css = trim($css);

    return $css;
}


/**
 * Return AvsSkin as CSS String
 * @return array
 */
public function getLogo() {
    return $this -> getField(self::ACF_FIELD_LOGO);
}

/**
 * Returns rules
 * @return (mixed)
 */
public function getRules() {
    return $this -> getField(self::ACF_FIELD_RULES);
}

/**
 * SKIN AS CONTENT
 */

/**
 * @param $content
 * @param $contentId
 * @param $type
 * @param $content_util Avs_Content_Util
 */
public function getContent($content, $contentId, $type, $content_util) {

    $parsedId = explode(self::CONTENT_ID_SEP, $contentId);
    if($type == self::CONTENT_TYPE_SKIN) {
        $post_id = $parsedId[1];
        $content = $content_util -> getBaseContentForPost($contentId, get_post($post_id));
        $content->title .= ' ' . __('Logo', UIBuilder_Naming::getSlug());
        $content->contentTitle .= ' ' . __('Logo', UIBuilder_Naming::getSlug());

    } else if(($directive = $this->__getDirectiveContentType($type))) {

        $post_id = $parsedId[1];
        $content = $content_util -> getBaseContentForPost($contentId, get_post($post_id));

        // Parse directive to find the right one
        $skin = new AvsSkin($post_id);
        $rules = $skin -> getRules();
        list($rule, $rule_id, $property) = explode('_', $directive);
        $rule = $rules[$rule_id];

        // Update content data
        $typeLabel = $rule[self::ACF_FIELD_DIRECTIVE] . ' ' . $property;
        $content -> objectSubtype .= ' ' .  $property;
        $content -> contentType .= ' ' . $property;
        $content -> objectType .= ' ' . $property;
        $content->title .= ' ' . $typeLabel;
        $content->contentTitle .= ' ' . $typeLabel;

    }

    return $content;
}

/**
 * Returns content type = 'skin' if post_id is a AvsSkin AND $field is the logo
 * Returns content type = 'skin|[directive]' if post_id is a AvsSkin AND $field is an image for a directive
 *
 * @param $result
 * @param $post_id
 * @param $field
 */
public function getContentType($result, $post_id, $field) {

    if(get_post_type($post_id) == self::POST_TYPE) {

        // Content Type: Skin
        $type = self::CONTENT_TYPE_SKIN;

        // If image for a directive
        if($field && $field['name'] != self::ACF_FIELD_LOGO) {

            // Content Type: Skin - Directive
            $type .= self::CONTENT_ID_DEL . $field['name'];
        }

        $result = array($type, $post_id);

    }

    return $result;
}

/**
 * Returns $contentId for Skin if $type = 'skin'
 * @param $contentId
 * @param $entity_id
 * @param $type
 * @param $content_util Avs_Content_Util
 */
public function getContentId($contentId, $entity_id, $type, $content_util) {

    // If type skin (case LOGO)
    if($type == self::CONTENT_TYPE_SKIN) {
        return self::CONTENT_TYPE_SKIN . self::CONTENT_ID_SEP . $entity_id;
    }

    // If type skin-directive (case IMAGE for Directive)
    if($this -> __getDirectiveContentType($type)) {
        return $type . self::CONTENT_ID_SEP . $entity_id;
    }

    return $contentId;
}

/**
 * Returns type if $contentId identify a AvsSkin or a directive in AvsSkin
 * @param $contentType
 * @param $contentId
 * @param $content_util Avs_Content_Util
 */
public function getContentTypeById($contentType, $contentId, $content_util) {

    // Check contentId starts with 'skin' word
    $pos = strpos($contentId, self::CONTENT_TYPE_SKIN);
    if($pos !== false && $pos == 0) {

        // Split by :
        $parsedId = explode(self::CONTENT_ID_SEP, $contentId);

        // We must find 2 element
        if(count($parsedId) == 2) {

            // Found SKIN or SKIN directive
            $contentType = $parsedId[0];

        }
    }

    return $contentType;
}

/**
 * @return Avs_Skin_Helper|null
 */
protected function __getHelper() {
    return Avs_Skin_Helper::getInstance();
}

/**
 * @param $type
 * @return bool
 */
protected function __getDirectiveContentType($type) {
    $pos = strpos($type, self::CONTENT_TYPE_SKIN);
    if($pos !== false && $pos === 0) {
        return str_replace(self::CONTENT_TYPE_SKIN . self::CONTENT_ID_DEL, '', $type);
    }

    return false;
}
}

?>