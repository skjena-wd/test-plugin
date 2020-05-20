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
