# test-plugin
This is a plugin for testing purposes


    /**
     * @param $menuitem array
     * @param $parent array|null
     * @param $depth int
     * @return boolean
     */
    protected function __isActive($menuitem, $parent, $depth) {
        return (bool)$menuitem[AvsMenu::ACF_FIELD_ACTIVE];
    }

    /**
     * Create recursively menu items as json.
     * @param $items array
     * @param $parent array
     * @param int $depth
     * @return array
     */
    protected function __getItems($items, $parent, $depth = 1, $parent_suffix_id = "") {
        
        $json_menu = array();
        $menu = $this -> getMenu();        

        if($items) {

            foreach ($items as $index => $item) {
                
                
                $suffix_id = $parent_suffix_id . "_" . ($index+1);
                $subitems = null;
                if($item[AvsMenu::ACF_FIELD_ITEMS]) {
                    $subitems = $this -> __getItems($item[AvsMenu::ACF_FIELD_ITEMS], $item, $depth + 1, $suffix_id);
                }                                                
                
                $json_menu_item = array(                       
                    "id" => $menu->ID . $suffix_id,
                    "totalDepth" => $depth,
                    "layout" => $this->__getLayout($item, $parent, $depth),
                    "actions" => $this->__getActions($item, $parent, $depth),
                    "metadata" => $this->__getMetadata($item, $parent, $depth),
                    "items" => $subitems,
                );                
                $isActive = $this->__isActive($item, $parent, $depth);
                
                $json_menu_item = apply_filters("avs_export_menu_item_json", $json_menu_item, $item);
                
                /*
                * Pass $json_menu_item into $json_menu with active parameter
                */
                if($isActive){
                    $json_menu[] = array_filter($json_menu_item);
                }

            }

        }

        return $json_menu;
    }
    
    
    
    
    
    
   class Avs_Menu_Exporter
{
	/**
	* @param $menuitem array
	* @param $parent array|null
	* @param $depth int
	* @return boolean 
	*/

	protected function __isActive($menuitem, $parent, $depth){
		//fallback for sitemaps created before adding AvsMenu::ACF_FIELD_ACTIVE field
		if(!isset($menuitem[AvsMenu::ACF_FIELD_ACTIVE])){
			return true;
		}
		return (bool)$menuitem[AvsMenu::ACF_FIELD_ACTIVE];
	}

    /**
     * Create recursively menu items as json.
     * @param $items array
     * @param $parent array
     * @param int $depth
     * @return array
     */
    protected function __getItems($items, $parent, $depth = 1, $parent_suffix_id = "") {

        $json_menu = array();
        $menu = $this -> getMenu();

        if($items) {

            foreach ($items as $index => $item) {

                $isActive = $this->__isActive($item, $parent, $depth);

                /**
                 * Check if a Sitemap menu item is active
                 *
                 * @since 6.7.4
                 *
                 * @param array $json_menu_item array.
                 * @param array $item array
                 */
                $isActive = apply_filters("avs_export_menu_item_active", $isActive, $item, $parent, $depth);

                // Only for activated Menu Items
                if($isActive){

                    $suffix_id = $parent_suffix_id . "_" . ($index+1);
                    $subItems = null;
                    if($item[AvsMenu::ACF_FIELD_ITEMS]) {
                        $subItems = $this -> __getItems($item[AvsMenu::ACF_FIELD_ITEMS], $item, $depth + 1, $suffix_id);
                    }

                    $json_menu_item = array(
                        "id" => $menu->ID . $suffix_id,
                        "totalDepth" => $depth,
                        "layout" => $this->__getLayout($item, $parent, $depth),
                        "actions" => $this->__getActions($item, $parent, $depth),
                        "metadata" => $this->__getMetadata($item, $parent, $depth),
                        "items" => $subItems,
                    );

                    /**
                     * Converts a Sitemap menu item into a json
                     *
                     * @since 6.7.4
                     *
                     * @param array $json_menu_item array.
                     * @param array $item array
                     */
                    $json_menu_item = apply_filters("avs_export_menu_item_json", $json_menu_item, $item, $parent, $depth);

                    $json_menu[] = array_filter($json_menu_item);
                }

            }

        }

        return $json_menu;
    }



}






<script>
// Add clone button near each row in repeater "Items"
jQuery(document).find(".acf-icon.-plus").after('<a class="acf-icon -clone small acf-js-tooltip avs-tooltip" href="#" data-event="clone-row" title="Clone row"></a>');
//or
let cloneButton = jQuery('<a></a>')
    .addClass('acf-icon')
    .addClass('-clone')
    .addClass('small')
    .addClass('acf-js-tooltip')
    .addClass('avs-tooltip')
    .attr('href', '#')
    .attr('title', 'Clone row')
    .attr('data-event', 'clone-row');
jQuery(document).find(".acf-icon.-plus").after(cloneButton);


// Clone Menu Item and add to the parent div
jQuery(document).on('click', '.acf-icon.-clone', function(){
    let $this = jQuery(this);
    let $parentMenu = jQuery(this).closest(".acf-row");
    acf.duplicate({
        target: $parentMenu
    });
});



// Clone Menu Item and add to the parent div
jQuery(document).on('click', '.acf-icon.-clone', function(){
    let $this = jQuery(this);
    let $parentMenu = jQuery(this).closest(".acf-row");

    // Prepare unique id
    let uniqId = acf.uniqid();

    // Get title field in item menu
    let $title = $parentMenu.find('.acf-field-avs-menu-title input').first();

    // Prepare string to replace
    let search = $title.attr('name').replace('acf', ''); // remove "acf" prefix
    search = search.replace('[field_avs_menu_title]', ''); // remove "[field_avs_menu_title]"

    // Prepare replace
    let replace = search.substring(0, search.lastIndexOf("[")); // remove last [\w+] occurence at the end of the string
    replace = replace + '[' + uniqId + ']'; // append the uniqId

    // Clone menu item
    acf.duplicate({

        // Search to replace
        search: search,

        // Use uniqid for clone
        replace: replace,

        // Clone target
        target: $parentMenu,

        // Append clone
        append: function( $el, $el2 ){

            // Fix uniqId cause acf.rename set "replace" string as new data-id
            $el2.attr('data-id', uniqId);

            // Append the prefix "Clone of" to all menu title values
            // @TODO

            // append
            $el.after( $el2 );
        },

        // Before clone
        before: function($el) {

            // destroy all select2 in target to clone
            var $select = $el.find('.select2-hidden-accessible').select2();
            $select.each(function(i,item){
                jQuery(item).select2("destroy");
            });
        },

        // After clone
        after: function($el1, $el2) {

            // restore all select2 destroyed before
            $el1.find('select[data-ui="1"').select2();
        },
    });
});

</script>
